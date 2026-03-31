<?php
session_start();
$conexion = mysqli_connect("localhost", "Apache3", "redes", "anime");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DaiAnime - Inicio</title>
    <link rel="stylesheet" href="index.css">
    <link rel="icon" type="image/png" href="DaiAnime.jpeg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                    <a href="#" class="user-link"><?php echo $_SESSION['usuario']; ?></a>
                    <ul class="user-dropdown">
                        <li><a href="Mi_lista.php">Mi Lista</a></li>
                        <li><a href="favoritos.php">Animes Favoritos</a></li>
                        <li class="logout-separator"><a href="logout.php">Cerrar Sesión</a></li>
                    </ul>
                <?php else: ?>
                    <a href="iniciar_sesion.php">Iniciar Sesión</a>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
</header>
   <h2 class="h3">Animes/Temporadas Nuevas 2026</h2>

    <section class="anime-list">
    <?php
    $sql = "SELECT * FROM anime WHERE YEAR(fecha_estreno) >= 2026 ORDER BY fecha_estreno DESC LIMIT 4";
    $res = mysqli_query($conexion, $sql);
    while ($nuevo = mysqli_fetch_assoc($res)) {
    ?>
        <div class="anime-card news-card">
            <img src="imagenes/anime/<?php echo $nuevo['imagen_portada']; ?>" class="anime-img">
            <h3 class="anime-title"><?php echo $nuevo['titulo']; ?></h3>
            <p class="anime-genre">Estreno: <?php echo date("d/m/Y", strtotime($nuevo['fecha_estreno'])); ?></p>
        </div>
    <?php } ?>
</section>

   <h2 class="h3">Animes 2025 </h2>
   <section class="anime-list">
    <?php
    $sql = "SELECT * FROM anime WHERE YEAR(fecha_estreno) = 2025 ORDER BY fecha_estreno DESC LIMIT 4";    
    $res = mysqli_query($conexion, $sql);
    while ($nuevo = mysqli_fetch_assoc($res)) {
    ?>
        <div class="anime-card news-card">
            <img src="imagenes/anime/<?php echo $nuevo['imagen_portada']; ?>" class="anime-img">
            <h3 class="anime-title"><?php echo $nuevo['titulo']; ?></h3>
            <p class="anime-genre">Estreno: <?php echo date("d/m/Y", strtotime($nuevo['fecha_estreno'])); ?></p>
        </div>
    <?php } ?>
</section>

<footer>
    <p>© 2026 DaiAnime. Todos los derechos reservados.</p>
    <div class="social-links">  
        <a href="#"><i class="fab fa-facebook"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-youtube"></i></a>
    </div>
</footer>
</body>
</html>