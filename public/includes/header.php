<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NominaApp - Sistema de Gestion de Nominas Integral</title>
    <link rel="stylesheet" href="../assets/css/estilos.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/estilos.css'); ?>">
    <?php if (in_array(basename($_SERVER['PHP_SELF']), ['ver_bauche.php', 'ver_bauche_admin.php'])): ?>
    <link rel="stylesheet" href="../assets/css/print.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/print.css'); ?>" media="print">
    <?php endif; ?>
</head>
<body>
    <div class="contenedor-app">
