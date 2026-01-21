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
<script src="<?= BASE_URL ?>js/core/utils.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>js/core/notifications.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>js/core/api.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>js/core/combobox.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>js/core/simple-select.js?v=<?= time() ?>"></script>

<!-- Módulos Funcionales -->
<script src="<?= BASE_URL ?>js/modules/exchange-rate.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>js/modules/modals.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>js/modules/theme.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>js/modules/charts.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>js/modules/reports.js?v=<?= time() ?>"></script>

<!-- Módulos de Negocio (Nuevos) -->
<script src="<?= BASE_URL ?>js/modules/turbo-nav.js?v=<?= time() ?>"></script>
<!-- NOTA: pos.js y compras.js ahora están en /pages/ y se cargan en las vistas específicas -->
<!-- <script src="<?= BASE_URL ?>js/modules/pos.js?v=<?= time() ?>"></script> -->
<!-- <script src="<?= BASE_URL ?>js/modules/compras.js?v=<?= time() ?>"></script> -->
<!-- NOTA: proveedores.js ahora está en /pages/ y se carga en la vista específica -->
<!-- <script src="<?= BASE_URL ?>js/modules/proveedores.js?v=<?= time() ?>"></script> -->
<!-- NOTA: producto-modales.js nunca existió, la lógica está en productos.js -->
<!-- <script src="<?= BASE_URL ?>js/modules/producto-modales.js?v=<?= time() ?>"></script> -->
<script src="<?= BASE_URL ?>js/modules/stock-alerts.js?v=<?= time() ?>"></script>

<!-- App Principal (Inicializador) -->
<script src="<?= BASE_URL ?>js/app.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>js/electron-bridge.js?v=<?= time() ?>"></script>
