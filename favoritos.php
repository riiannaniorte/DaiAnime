<?php
session_start();
// Conexión a la base de datos
$conexion = new mysqli("localhost", "Apache3", "redes", "anime");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Si no está logueado, lo mandamos al login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: iniciar_sesion.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// --- ACCIÓN: ELIMINAR DE FAVORITOS ---
if (isset($_POST['eliminar']) && isset($_POST['anime_id'])) {
    $id_anime = intval($_POST['anime_id']);

    $conexion->query("DELETE FROM favoritos 
                      WHERE id_usuario = $id_usuario 
                      AND id_anime = $id_anime");

    header("Location: favoritos.php");
    exit();
}

// --- ACCIÓN: GUARDAR EN FAVORITOS ---
if (isset($_POST['anime_id']) && !isset($_POST['eliminar'])) {
    $id_anime = intval($_POST['anime_id']);

    // Comprobamos si ya existe para no duplicar filas
    $check = $conexion->query("SELECT id_anime FROM favoritos
                               WHERE id_usuario = $id_usuario
                               AND id_anime = $id_anime");

    if ($check->num_rows == 0) {
        $conexion->query("INSERT INTO favoritos (id_usuario, id_anime, fecha_guardado)
                          VALUES ($id_usuario, $id_anime, NOW())");
    }

    header("Location: favoritos.php");
    exit();
}

// --- CONSULTA PARA MOSTRAR LOS FAVORITOS ---
$sql = "SELECT a.*, f.fecha_guardado
        FROM anime a
        INNER JOIN favoritos f ON a.id_anime = f.id_anime
        WHERE f.id_usuario = $id_usuario
        ORDER BY f.fecha_guardado DESC";

$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Favoritos</title>
    <link rel="stylesheet" href="Buscar.css">
    <link rel="icon" type="image/png" href="DaiAnime.jpeg">
    <style>
        /* Estilos para el botón de borrar */
        .delete-button {
            background-color: #e53935;
            color: white;
            border: none;
            padding: 8px 12px;
            margin-top: 10px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
            transition: 0.3s;
        }

        .delete-button:hover {
            background-color: #b71c1c;
        }
    </style>
</head>
<body>

<header class="page-header">
    <h1>
        <a href="index.php">
            <img src="DaiAnime.jpeg" alt="Logo" style="height:40px; vertical-align:middle;">
            DaiAnime
        </a>
    </h1>

    <nav class="main-menu">
        <ul>
            <li><a href="index.php">Inicio</a></li>
            <li><a href="Buscar.php">Buscar</a></li> 
            
            <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin'): ?>
                <li><a href="admin.php">Admin</a></li>
            <?php endif; ?>           

            <li class="user-menu-item">
                <?php if(isset($_SESSION['usuario'])): ?>
                    <a href="#" class="user-link">
                        <?php echo $_SESSION['usuario']; ?>
                    </a>
                    <ul class="user-dropdown">
                        <li><a href="Mi_lista.php"> Mi Lista</a></li>
                        <li><a href="favoritos.php">Animes Favoritos</a></li>
                        <li class="logout-separator">
                            <a href="logout.php">Cerrar Sesión</a>
                        </li>
                    </ul>
                <?php else: ?>
                    <a href="iniciar_sesion.php">Iniciar Sesión</a>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
</header>

<h1 style="text-align:center; margin:20px;">⭐ Mis Animes Favoritos</h1>

<div class="anime-grid">

<?php if ($resultado && $resultado->num_rows > 0): ?>
    <?php while ($anime = $resultado->fetch_assoc()): ?>
    <div class="anime-card">
        <a href="anime.php?id=<?php echo $anime['id_anime']; ?>">
            <img src="imagenes/anime/<?php echo $anime['imagen_portada']; ?>" alt="Portada">
        </a>
        <h4><?php echo $anime['titulo']; ?></h4>

        <div class="anime-details">
            <p><strong>Año:</strong> <?php echo date("Y", strtotime($anime['fecha_estreno'])); ?></p>
            <p><strong>Tipo:</strong> <?php echo $anime['tipo']; ?></p>
            <p><strong>Estado:</strong> <?php echo $anime['estado']; ?></p>
            <p><strong>Género:</strong> <?php echo $anime['genero']; ?></p>
        </div>

        <form method="POST" onsubmit="return confirm('¿Eliminar de favoritos?');">
            <input type="hidden" name="anime_id" value="<?php echo $anime['id_anime']; ?>">
            <input type="hidden" name="eliminar" value="1">
            <button type="submit" class="delete-button">
                🗑️ Eliminar
            </button>
        </form>
    </div>
    <?php endwhile; ?>
<?php else: ?>
    <p style="text-align:center; padding:50px; grid-column: 1/-1;">No tienes favoritos aún. <a href="Buscar.php" style="color: var(--highlight-color);">¡Busca algunos!</a></p>
<?php endif; ?>

</div>

<footer>
    <p style="text-align:center; padding:20px;">© 2026 DaiAnime</p>
</footer>

</body>
</html>