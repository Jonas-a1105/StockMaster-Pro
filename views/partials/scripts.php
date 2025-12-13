<?php
/**
 * SCRIPTS - JavaScript del sistema
 */
?>
<!-- Librerías externas -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

<!-- Módulos Core -->
<!-- Módulos Core -->
<script src="<?= BASE_URL ?>js/core/utils.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>js/core/notifications.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>js/core/api.js?v=<?= time() ?>"></script>

<!-- Módulos Funcionales -->
<script src="<?= BASE_URL ?>js/modules/exchange-rate.js"></script>
<script src="<?= BASE_URL ?>js/modules/modals.js"></script>
<script src="<?= BASE_URL ?>js/modules/theme.js"></script>
<script src="<?= BASE_URL ?>js/modules/charts.js"></script>
<script src="<?= BASE_URL ?>js/modules/reports.js"></script>

<!-- App Principal -->
<script src="<?= BASE_URL ?>js/app.js?v=<?= time() ?>"></script>
