<?php
// 1. Iniciamos la sesión para poder acceder a ella
session_start();

// 2. Limpiamos todas las variables de sesión
$_SESSION = array();

// 3. Si se desea destruir la sesión completamente, borramos también la cookie de sesión.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Destruimos la sesión en el servidor
session_destroy();

// 5. Redirigimos al index.html como pediste
header("Location: index.php");
exit;
?>