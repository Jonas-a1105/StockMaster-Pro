<?php
namespace App\Services;

use App\Models\UsuarioModel;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Domain\ValueObjects\Email;

class PasswordService {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new UsuarioModel();
    }

    public function initiateReset($emailStr) {
        try {
            $email = new Email($emailStr);
            $usuario = $this->usuarioModel->findByEmail($email->getAddress());
            if ($usuario) {
                $token = bin2hex(random_bytes(32));
                $this->usuarioModel->createResetToken($email->getAddress(), $token);
                $this->sendResetEmail($email->getAddress(), $token);
            }
            return true;
        } catch (\InvalidArgumentException $e) {
            // Log invalid email format if needed, but return true to hide existence
            return true;
        } catch (\Exception $e) {
            error_log("Error in PasswordService::initiateReset: " . $e->getMessage());
            return false;
        }
    }

    public function validateToken($token) {
        return $this->usuarioModel->getResetToken($token);
    }

    public function completeReset($token, $password) {
        $tokenData = $this->usuarioModel->getResetToken($token);
        if (!$tokenData) return false;

        $newPasswordHash = password_hash($password, PASSWORD_DEFAULT);
        $this->usuarioModel->updatePassword($tokenData['email'], $newPasswordHash);
        $this->usuarioModel->deleteResetToken($token);
        return true;
    }

    private function sendResetEmail($email, $token) {
        $mail = new PHPMailer(true);
        $path = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
        $baseURL = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $path . '/';
        $resetLink = $baseURL . 'index.php?controlador=password&accion=reset&token=' . $token;

        try {
            // In a real SaaS, these would be loaded from .env or DB
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'jonasmendoza672@gmail.com'; 
            $mail->Password   = 'wmuh erko ckkq jryn'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('tu_email@gmail.com', 'Soporte de Inventario');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Recuperacion de Contrasena - Sistema de Inventario';
            $mail->Body    = "Hola,<br><br>Hemos recibido una solicitud para restablecer tu contraseña. Haz clic en el siguiente enlace:<br><br>"
                           . "<a href='$resetLink' style='padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>"
                           . "Restablecer Contraseña</a>"
                           . "<br><br>Si no solicitaste esto, ignora este email."
                           . "<br>Este enlace expira en 1 hora.";
            $mail->AltBody = "Para restablecer tu contraseña, copia y pega este enlace en tu navegador: $resetLink";

            $mail->send();
            return true;
        } catch (Exception $e) {
            throw new \Exception("Mailer Error: {$mail->ErrorInfo}");
        }
    }
}
