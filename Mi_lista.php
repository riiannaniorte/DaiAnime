<?php
session_start();
// Conexión a la base de datos
$conexion = new mysqli("localhost", "Apache3", "redes", "anime");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Si el usuario no ha entrado, fuera de aquí
if (!isset($_SESSION['id_usuario'])) {
    header("Location: iniciar_sesion.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// --- ACCIÓN: GUARDAR EN LA LISTA ---
// Si venimos de Buscar.php y se ha pulsado el botón de guardar
if (isset($_POST['anime_id']) && !isset($_POST['eliminar'])) {
    $id_anime = intval($_POST['anime_id']);
    
    // Miramos si ya existe para no repetir
    $check = $conexion->query("SELECT id_anime FROM mi_lista WHERE id_usuario = $id_usuario AND id_anime = $id_anime");
    
    if ($check->num_rows == 0) {
        $conexion->query("INSERT INTO mi_lista (id_usuario, id_anime) VALUES ($id_usuario, $id_anime)");
    }
    header("Location: Mi_lista.php");
    exit();
}

// --- ACCIÓN: ELIMINAR DE LA LISTA ---
// Si se ha pulsado el botón de eliminar dentro de esta página
if (isset($_POST['eliminar']) && isset($_POST['anime_id'])) {
    $id_anime = intval($_POST['anime_id']);
    
    $conexion->query("DELETE FROM mi_lista WHERE id_usuario = $id_usuario AND id_anime = $id_anime");
    
    header("Location: Mi_lista.php?mensaje=Anime quitado correctamente");
    exit();
}

// --- CONSULTA PARA EL GRID ---
$sql = "SELECT a.* FROM anime a 
        INNER JOIN mi_lista m ON a.id_anime = m.id_anime 
        WHERE m.id_usuario = $id_usuario";

$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Lista - DaiAnime</title>
    <link rel="stylesheet" href="Buscar.css">
    <link rel="icon" type="image/png" href="DaiAnime.jpeg">
    <style>
        /* Estilos rápidos para el botón de borrar */
        .delete-button {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 8px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }
        .mensaje-exito {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<header class="page-header">
    <h1>
        <a href="index.php">
            <img src="DaiAnime.jpeg" style="height:40px; vertical-align:middle;">
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
                <a href="#" class="user-link"><?php echo $_SESSION['usuario']; ?></a>
                <ul class="user-dropdown">
                    <li><a href="Mi_lista.php">Mi Lista</a></li>
                    <li><a href="favoritos.php">Animes Favoritos</a></li>
                    <li><a href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </li>
        </ul>
    </nav>
</header>

<main style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <h1 style="text-align: center;">Mi Lista</h1>
    
    <?php if (isset($_GET['mensaje'])): ?>
        <div class="mensaje-exito"><?php echo $_GET['mensaje']; ?></div>
    <?php endif; ?>

    <div class="anime-grid">
        <?php if ($resultado && $resultado->num_rows > 0): ?>
            <?php while ($anime = $resultado->fetch_assoc()): ?>
                <div class="anime-card">
                    <a href="anime.php?id=<?php echo $anime['id_anime']; ?>">
                        <img src="imagenes/anime/<?php echo $anime['imagen_portada']; ?>" alt="Portada">
                    </a>
                    <div class="anime-details">
                        <p><strong><?php echo $anime['titulo']; ?></strong></p>
                        <p>Tipo: <?php echo $anime['tipo']; ?></p>
                        
                        <form method="POST">
                            <input type="hidden" name="anime_id" value="<?php echo $anime['id_anime']; ?>">
                            <input type="hidden" name="eliminar" value="1">
                            <button type="submit" class="delete-button">Eliminar</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center; grid-column: 1/-1;">No tienes nada guardado aún. <a href="Buscar.php">¡Busca algo!</a></p>
        <?php endif; ?>
    </div>
</main>

<footer>
    <p>© 2026 DaiAnime</p>
</footer>
</body>
</html>