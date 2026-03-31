<?php
session_start();

// Conexión a la base de datos
$conexion = new mysqli("localhost", "Apache3", "redes", "anime");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Si no hay ID, volvemos atrás
if (!isset($_GET['id'])) {
    header("Location: Buscar.php");
    exit();
}

$id = $_GET['id'];

// --- DATOS DEL ANIME ---
$stmt = $conexion->prepare("SELECT * FROM anime WHERE id_anime = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    die("Anime no encontrado");
}

$anime = $resultado->fetch_assoc();

// --- VALORACIONES ---
if (isset($_POST['valorar']) && isset($_SESSION['id_usuario'])) {
    $puntuacion = intval($_POST['puntuacion']);
    $comentario = trim($_POST['comentario']);
    $id_usuario = $_SESSION['id_usuario'];

    if ($puntuacion >= 1 && $puntuacion <= 10 && !empty($comentario)) {
        // Miramos si ya existe valoración
        $check = $conexion->prepare("SELECT 1 FROM valoracion WHERE id_usuario = ? AND id_anime = ?");
        $check->bind_param("ii", $id_usuario, $id);
        $check->execute();
        $existe = $check->get_result();

        if ($existe->num_rows > 0) {
            // Actualizar
            $update = $conexion->prepare("UPDATE valoracion SET puntuacion = ?, comentario = ?, fecha = NOW() WHERE id_usuario = ? AND id_anime = ?");
            $update->bind_param("isii", $puntuacion, $comentario, $id_usuario, $id);
            $update->execute();
        } else {
            // Insertar nueva
            $insert = $conexion->prepare("INSERT INTO valoracion (id_usuario, id_anime, puntuacion, comentario, fecha) VALUES (?, ?, ?, ?, NOW())");
            $insert->bind_param("iiis", $id_usuario, $id, $puntuacion, $comentario);
            $insert->execute();
        }
        // Recargar para ver los cambios sin reenviar el POST
        header("Location: anime.php?id=" . $id);
        exit();
    }
}

// Media de notas
$stmt_media = $conexion->prepare("SELECT AVG(puntuacion) as media FROM valoracion WHERE id_anime = ?");
$stmt_media->bind_param("i", $id);
$stmt_media->execute();
$media = $stmt_media->get_result()->fetch_assoc()['media'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $anime['titulo']; ?></title>
    <link rel="stylesheet" href="anime.css">
    <link rel="icon" type="image/png" href="DaiAnime.jpeg">
</head>
<body>

<header class="page-header">
    <a href="index.php">
        <img src="DaiAnime.jpeg" style="height:40px; vertical-align:middle;">
        DaiAnime
    </a>
</header>

<div class="anime-container">
    <div class="anime-image">
        <img src="imagenes/anime/<?php echo $anime['imagen_portada']; ?>" alt="Portada">
    </div>

    <div class="anime-info">
        <h1><?php echo $anime['titulo']; ?></h1>

        <p><strong>Sinopsis:</strong><br>
        <?php echo $anime['sinopsis']; ?></p>

        <p><strong>Tipo:</strong> <?php echo $anime['tipo']; ?></p>
        <p><strong>Estado:</strong> <?php echo $anime['estado']; ?></p>
        <p><strong>Género:</strong> <?php echo $anime['genero']; ?></p>
        <p><strong>Año:</strong> <?php echo date("Y", strtotime($anime['fecha_estreno'])); ?></p>

        <div class="rating-box">
            <h3>⭐ Media: 
                <?php echo $media ? round($media, 1) : "S/V"; ?>
            </h3>
        </div>

        <a href="Buscar.php" class="back-btn">⬅ Volver</a>
    </div>
</div>

<div class="comentarios-seccion" style="max-width: 1100px; margin: 0 auto; padding: 20px;">
    <?php if(isset($_SESSION['id_usuario'])): ?>
        <div class="rating-box">
            <h3>Deja tu reseña</h3>
            <form method="POST">
                <select name="puntuacion" required>
                    <?php for($i=10; $i>=1; $i--) echo "<option value='$i'>$i</option>"; ?>
                </select>
                <br><br>
                <textarea name="comentario" required style="width:100%; height:80px;" placeholder="¿Qué te ha parecido?"></textarea>
                <br><br>
                <button type="submit" name="valorar" class="back-btn" style="border:none;">Enviar</button>
            </form>
        </div>
    <?php else: ?>
        <p style="text-align:center;">Inicia sesión para comentar.</p>
    <?php endif; ?>

    <h2 style="text-align:center; color: var(--highlight-color);">Comunidad</h2>

    <?php
    $stmt_com = $conexion->prepare("SELECT v.*, u.nombre_usuario FROM valoracion v JOIN usuario u ON v.id_usuario = u.id_usuario WHERE v.id_anime = ? ORDER BY v.fecha DESC");
    $stmt_com->bind_param("i", $id);
    $stmt_com->execute();
    $comentarios = $stmt_com->get_result();

    while($c = $comentarios->fetch_assoc()): ?>
        <div class="rating-box">
            <strong><?php echo $c['nombre_usuario']; ?></strong> 
            <span style="color: #f1c40f;">⭐ <?php echo $c['puntuacion']; ?>/10</span>
            <p><?php echo $c['comentario']; ?></p>
            <small><?php echo $c['fecha']; ?></small>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>