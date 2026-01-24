<?php
// config/db.php
try {
    // __DIR__ es la carpeta 'config'. 
    // '/../' nos sube un nivel hacia la raíz (SAGAFLEX)
    $rutaBaseDatos = __DIR__ . '/../sagaflex.db';

    // Creamos la conexión usando esa ruta absoluta
    $pdo = new PDO("sqlite:" . $rutaBaseDatos);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>