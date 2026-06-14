<?php
function sanitizarInput($dato) {
    return trim(strip_tags(htmlspecialchars($dato, ENT_QUOTES, 'UTF-8')));
}

function sanitizarArray($datos) {
    $limpio = [];
    foreach ($datos as $clave => $valor) {
        if (is_array($valor)) {
            $limpio[$clave] = sanitizarArray($valor);
        } else {
            $limpio[$clave] = sanitizarInput($valor);
        }
    }
    return $limpio;
}

function redirigir($url) {
    header('Location: ' . $url);
    exit;
}

function mostrarMensaje() {
    if (isset($_SESSION['mensaje_flash'])) {
        $tipo = $_SESSION['mensaje_tipo'] ?? 'info';
        $html = '<div class="mensaje mensaje-' . $tipo . '" id="mensaje-flash">';
        $html .= htmlspecialchars($_SESSION['mensaje_flash'], ENT_QUOTES, 'UTF-8');
        $html .= '</div>';
        unset($_SESSION['mensaje_flash'], $_SESSION['mensaje_tipo']);
        return $html;
    }
    return '';
}

function obtenerFechaHora() {
    return date('Y-m-d H:i:s');
}

function formatoMoneda($monto) {
    return 'Bs. ' . number_format($monto, 2, ',', '.');
}

function generarTokenCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validarTokenCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function obtenerConfigLegal($mysqli, $parametro) {
    $stmt = $mysqli->prepare("SELECT valor FROM configuracion_legal WHERE parametro = ?");
    $stmt->bind_param('s', $parametro);
    $stmt->execute();
    $stmt->bind_result($valor);
    $stmt->fetch();
    $stmt->close();
    return $valor;
}

function obtenerTodasConfig($mysqli) {
    $result = $mysqli->query("SELECT parametro, valor, descripcion FROM configuracion_legal ORDER BY parametro");
    $datos = [];
    while ($row = $result->fetch_assoc()) {
        $datos[] = $row;
    }
    return $datos;
}

function obtenerTasaDolar($mysqli) {
    $valor = obtenerConfigLegal($mysqli, 'TASA_DOLAR');
    return $valor > 0 ? (float)$valor : 60;
}

function formatoUSD($monto_bs, $mysqli) {
    $tasa = obtenerTasaDolar($mysqli);
    $usd = $tasa > 0 ? $monto_bs / $tasa : 0;
    return '$ ' . number_format($usd, 2, ',', '.');
}

function formatoDual($monto_bs, $mysqli) {
    $bs = formatoMoneda($monto_bs);
    $usd = formatoUSD($monto_bs, $mysqli);
    return "<span class=\"moneda-bs\">$bs</span> <span class=\"moneda-usd\">($usd)</span>";
}
