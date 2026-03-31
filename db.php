<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conexion = mysqli_connect(
        "localhost",
        "Apache3",          // usuario
        "redes",              // contraseña
        "anime" // base de datos
    );

    echo "✅ Conectado a la base de datos";

} catch (mysqli_sql_exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>