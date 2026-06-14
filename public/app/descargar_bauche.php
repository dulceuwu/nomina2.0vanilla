<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

$nomina_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = (int)$_SESSION['user_id'];

if ($nomina_id <= 0) { redirigir('mis_recibos.php'); }

$stmt = $mysqli->prepare("SELECT id FROM nominas WHERE id = ? AND usuario_id = ?");
$stmt->bind_param('ii', $nomina_id, $user_id);
$stmt->execute();
if (!$stmt->get_result()->fetch_assoc()) {
    $_SESSION['mensaje_flash'] = 'Bauche no encontrado.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir('mis_recibos.php');
}
$stmt->close();

redirigir("ver_bauche.php?id=$nomina_id&print=1");
