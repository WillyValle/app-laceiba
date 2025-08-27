<?php
require_once "app/modelos/database.php";

$conn = BasedeDatos::Conectar();

if ($conn instanceof PDO) {
    echo "✅ Conexión exitosa a la base de datos";
} else {
    echo "❌ No se pudo conectar";
}
