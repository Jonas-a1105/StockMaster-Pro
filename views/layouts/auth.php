<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?? 'Acceso | StockMaster Pro' ?></title>
    <link rel="shortcut icon" href="<?= BASE_URL ?>img/StockMasterPro.ico" type="image/x-icon">
    
    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="<?= BASE_URL ?>css/main.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/animations.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/components/glass.css?v=<?= time() ?>">
</head>
<body class="min-h-screen bg-[#eef2f6] flex flex-col items-center justify-center p-4">
    
    <!-- Flash Messages (Bridge to JS) -->
    <?php 
        if (\App\Core\Session::hasFlash()) {
            $flashMsg = '';
            $flashType = 'success';
            if ($msg = \App\Core\Session::getFlash('success')) { $flashMsg = $msg; $flashType = 'success'; }
            elseif ($msg = \App\Core\Session::getFlash('error')) { $flashMsg = $msg; $flashType = 'error'; }
            if ($flashMsg) {
                echo "<div id='flash-data' data-message='" . htmlspecialchars($flashMsg, ENT_QUOTES) . "' data-type='" . $flashType . "' class='hidden'></div>";
            }
        }
    ?>

    <!-- Reusable Loader -->
    <?= \App\Core\View::component('loader', [
        'titulo' => $loader_titulo ?? '¡Hola de nuevo!',
        'subtitulo' => $loader_subtitulo ?? 'Estamos preparando tu entorno...'
    ]) ?>

    <!-- View Content -->
    <?= $content ?? '' ?>

    <!-- Footer Copyright -->
    <p class="text-[10px] text-slate-400 mt-8 font-medium fixed bottom-4">© <?= date('Y') ?> StockMaster Solutions <span class="opacity-50 mx-1">|</span> v<?= APP_VERSION ?></p>

    <!-- SCRIPTS CORE -->
    <script>
        window.csrfToken = '<?= \App\Core\Session::csrfToken() ?>';
    </script>
    <script src="<?= BASE_URL ?>js/core/utils.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>js/core/notifications.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>js/app.js?v=<?= time() ?>"></script>
    
    <!-- PAGE SCRIPTS -->
    <script src="<?= BASE_URL ?>js/pages/auth.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>js/electron-bridge.js?v=<?= time() ?>"></script>
    <?php if (isset($extra_scripts)) echo $extra_scripts; ?>

</body>
</html>
