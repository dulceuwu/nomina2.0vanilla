<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

$nomina_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($nomina_id <= 0) { redirigir('ver_nominas.php'); }

$es_admin = $_SESSION['rol'] === 'admin';
$redirect = $es_admin ? "ver_bauche_admin.php?id=$nomina_id&print=1" : "../app/ver_bauche.php?id=$nomina_id&print=1";
redirigir($redirect);
