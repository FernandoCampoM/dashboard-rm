<?php
/* Offline script loader — all libraries served from vendor/ (no CDN required) */
$scripts = [
    // jQuery
    'vendor/js/jquery.min.js',
    // Bootstrap 5
    'vendor/js/bootstrap.bundle.min.js',
    // DataTables 1.13 + Bootstrap 5 integration
    'vendor/js/jquery.dataTables.min.js',
    'vendor/js/dataTables.bootstrap5.min.js',
    // DataTables Buttons
    'vendor/js/dataTables.buttons.min.js',
    'vendor/js/buttons.bootstrap5.min.js',
    'vendor/js/jszip.min.js',
    'vendor/js/pdfmake.min.js',
    'vendor/js/vfs_fonts.js',
    'vendor/js/buttons.html5.min.js',
    'vendor/js/buttons.print.min.js',
    'vendor/js/buttons.colVis.min.js',
    // Chart.js
    'vendor/js/chart.min.js',
    // Utilities
    'vendor/js/moment.min.js',
    'vendor/js/numeral.min.js',
    // SweetAlert2
    'vendor/js/sweetalert2.all.min.js',
    // FullCalendar (for schedule section)
    'vendor/js/fullcalendar.global.min.js',
];
foreach ($scripts as $src) {
    echo '<script src="' . htmlspecialchars($src) . '"></script>' . PHP_EOL;
}
