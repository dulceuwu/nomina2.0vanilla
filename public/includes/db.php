<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nomina_vanilla_db');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$mysqli->set_charset('utf8mb4');

if ($mysqli->connect_errno) {
    error_log('Error de conexion MySQL: ' . $mysqli->connect_error);
    die('Error de conexion a la base de datos. Contacte al administrador.');
}
