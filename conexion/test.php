<?php
// Archivo: conexion/test.php

require 'db.php'; // Busca el archivo en la misma carpeta

if ($pdo) {
    echo "<h1>✅ ¡Conexión Exitosa!</h1>";
    echo "<p>PHP se conectó correctamente a la base de datos <strong>'sistema_comedor'</strong>.</p>";
}
?>