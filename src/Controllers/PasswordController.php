<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Models\UsuarioModel;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PasswordController {

    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new UsuarioModel();
    }

    /**
     * Muestra el formulario de "Olvidé mi contraseña"
     */
    public function request() {
        $this->render('password/request');
    }

    /**
     * Procesa la solicitud y envía el email
     */
    public function send() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controlador=login');
        }

        $email = $_POST['email'] ?? '';
        
        // 1. Verificar que el usuario exista
        $usuario = $this->usuarioModel->findByEmail($email);

        // ¡IMPORTANTE! Por seguridad, no le decimos al usuario si el email
        // existe o no. Simplemente mostramos un mensaje genérico.
        if ($usuario) {
            try {
                // 2. Generar token seguro
                $token = bin2hex(random_bytes(32));
                
                // 3. Guardar token en la BBDD
                $this->usuarioModel->createResetToken($email, $token);

                // 4. Enviar el email
                $this->sendResetEmail($email, $token);

            } catch (\Exception $e) {
                // Error al generar token o al enviar email
                error_log("Error al enviar reseteo: " . $e->getMessage());
            }
        }
        
        // 5. Mostrar mensaje de éxito (incluso si el email no existía)
        Session::flash('success', 'Si existe una cuenta con ese email, se ha enviado un enlace de recuperación.');
        redirect('index.php?controlador=login');
    }

    /**
     * Muestra el formulario para ingresar la NUEVA contraseña
     */
    public function reset() {
        $token = $_GET['token'] ?? '';
        
        // Validar que el token exista y no haya expirado
        $tokenData = $this->usuarioModel->getResetToken($token);

        if (!$tokenData) {
            Session::flash('error', 'El enlace de recuperación es inválido o ha expirado.');
            redirect('index.php?controlador=login');
        }
        
        // El token es válido, mostrar la vista de reseteo
        $this->render('password/reset', ['token' => $token]);
    }

    /**
     * Actualiza la contraseña en la BBDD
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controlador=login');
        }

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        // 1. Validar el token de nuevo
        $tokenData = $this->usuarioModel->getResetToken($token);
        if (!$tokenData) {
            Session::flash('error', 'Petición inválida o el token ha expirado.');
            redirect('index.php?controlador=login');
        }

        // 2. Validar contraseñas
        if (empty($password) || $password !== $password_confirm) {
            Session::flash('error', 'Las contraseñas no coinciden o están vacías.');
            // Devolver al formulario de reseteo CON el token
            redirect('index.php?controlador=password&accion=reset&token=' . $token);
        }

        // 3. Hashear nueva contraseña
        $newPasswordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // 4. Actualizar BBDD
        $this->usuarioModel->updatePassword($tokenData['email'], $newPasswordHash);
        
        // 5. Borrar el token (ya se usó)
        $this->usuarioModel->deleteResetToken($token);

        Session::flash('success', '¡Contraseña actualizada! Ya puedes iniciar sesión.');
        redirect('index.php?controlador=login');
    }


    /**
     * Configuración y envío del email con PHPMailer
     */
    private function sendResetEmail($email, $token) {
        $mail = new PHPMailer(true);
        $path = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
        $baseURL = 'http://' . $_SERVER['HTTP_HOST'] . $path . '/';
        $resetLink = $baseURL . 'index.php?controlador=password&accion=reset&token=' . $token;

        try {
            // --- ¡CONFIGURACIÓN CRÍTICA! (Usando Gmail como ejemplo) ---
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Descomenta esto para ver errores
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'jonasmendoza672@gmail.com'; // <-- ¡PON TU EMAIL DE GMAIL!
            $mail->Password   = 'wmuh erko ckkq jryn'; // <-- ¡PON TU CONTRASEÑA DE APLICACIÓN DE GMAIL!
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            // --- FIN DE LA CONFIGURACIÓN ---

            // Emisor y Receptor
            $mail->setFrom('tu_email@gmail.com', 'Soporte de Inventario');
            $mail->addAddress($email);

            // Contenido
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
            throw new \Exception("El mensaje no pudo ser enviado. Mailer Error: {$mail->ErrorInfo}");
        }
    }

    private function render($vista, $data = []) {
        extract($data);
        require __DIR__ . '/../../views/' . $vista . '.php';
    }
}