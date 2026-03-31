<?php
session_start();
// Conexión a la base de datos
$conexion = new mysqli("localhost", "Apache3", "redes", "anime");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Recogida de datos por GET para el filtrado
$buscar  = isset($_GET['buscar']) ? $_GET['buscar'] : '';
$tipo    = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$estado  = isset($_GET['estado']) ? $_GET['estado'] : '';
$generos = isset($_GET['genero']) ? $_GET['genero'] : []; 
$anios   = isset($_GET['anio']) ? $_GET['anio'] : [];     

// Construcción de la consulta SQL
$sql = "SELECT * FROM anime WHERE 1=1";

if (!empty($buscar)) {
    $sql .= " AND titulo LIKE '%" . $conexion->real_escape_string($buscar) . "%'";
}

if (!empty($tipo) && $tipo != 'Todos') {
    $sql .= " AND tipo = '" . $conexion->real_escape_string($tipo) . "'";
}

if (!empty($estado) && $estado != 'Todos') {
    $sql .= " AND estado = '" . $conexion->real_escape_string($estado) . "'";
}

// Filtro de géneros
if (!empty($generos)) {
    $generos_escaped = array_map(function($g) use ($conexion) {
        return "FIND_IN_SET('" . $conexion->real_escape_string($g) . "', genero)";
    }, $generos);
    $sql .= " AND (" . implode(" OR ", $generos_escaped) . ")";
}

// Filtro de años
if (!empty($anios)) {
    $anios_escaped = array_map(function($a) use ($conexion) {
        return "YEAR(fecha_estreno) = '" . $conexion->real_escape_string($a) . "'";
    }, $anios);
    $sql .= " AND (" . implode(" OR ", $anios_escaped) . ")";
}

$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar Animes - DaiAnime</title>
    <link rel="stylesheet" href="Buscar.css">
    <link rel="icon" type="image/png" href="DaiAnime.jpeg">
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
                    <a href="iniciar_sesion.php" class="user-link">Iniciar Sesión</a>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
</header>

<form method="GET" class="search-filters-form">
    <input type="text" name="buscar" id="searchInput" value="<?php echo $buscar; ?>">
    
    <select name="tipo">
        <option value="Todos">Tipo: Todos</option>
        <option value="TV" <?php if($tipo=='TV') echo 'selected'; ?>>TV</option>
        <option value="Movie" <?php if($tipo=='Movie') echo 'selected'; ?>>Movie</option>
        <option value="OVA" <?php if($tipo=='OVA') echo 'selected'; ?>>OVA</option>
        <option value="ONA" <?php if($tipo=='ONA') echo 'selected'; ?>>ONA</option>
    </select>

    <select name="estado">
        <option value="Todos">Estado: Todos</option>
        <option value="En emisión" <?php if($estado=='En emisión') echo 'selected'; ?>>En emisión</option>
        <option value="Finalizado" <?php if($estado=='Finalizado') echo 'selected'; ?>>Finalizado</option>
    </select>

    <div class="multi-select">
        <div class="select-box">Géneros</div>
        <div class="checkboxes">
            <?php
                $lista_generos = [
                "Acción", "Artes Marciales", "Aventuras", "Carreras",
                "Ciencia Ficción", "Comedia", "Demencia", "Demonios",
                "Deportes", "Drama", "Ecchi", "Escolares",
                "Espacial", "Fantasía", "Harem", "Historico",
                "Infantil", "Josei", "Juegos", "Magia",
                "Mecha", "Militar", "Misterio", "Música",
                "Parodia", "Policía", "Psicológico", "Recuentos de la vida",
                "Romance", "Samurai", "Seinen", "Shoujo",
                "Shounen", "Sobrenatural", "Superpoderes", "Suspenso",
                "Terror", "Vampiros", "Yaoi", "Yuri"
];           
 foreach($lista_generos as $g){
                $checked = in_array($g, $generos) ? "checked" : "";
                echo "<label><input type='checkbox' name='genero[]' value='$g' $checked> $g</label>";
            }
            ?>
        </div>
    </div>

    <div class="multi-select">
        <div class="select-box">Año</div>
        <div class="checkboxes">
            <?php
            for($y=2026; $y>=1985; $y--){
                $checked = in_array($y, $anios) ? "checked" : "";
                echo "<label><input type='checkbox' name='anio[]' value='$y' $checked> $y</label>";
            }
            ?>
        </div>
    </div>

    <button type="submit" id="btnFiltrar">FILTRAR</button>
</form>

<div class="anime-grid">
<?php
if ($resultado && $resultado->num_rows > 0) {
    while ($anime = $resultado->fetch_assoc()) {
        ?>
        <div class="anime-card">
            <a href="anime.php?id=<?php echo $anime['id_anime']; ?>">
                <img src="imagenes/anime/<?php echo $anime['imagen_portada']; ?>" alt="Portada">
            </a>
            <div class="anime-details">
                <h4><?php echo $anime['titulo']; ?></h4>
                <p><strong>Año:</strong> <?php echo date("Y", strtotime($anime['fecha_estreno'])); ?></p>
                <p><strong>Tipo:</strong> <?php echo $anime['tipo']; ?></p>
                <p class="sinopsis-corta"><strong>Sinopsis:</strong> <?php echo substr($anime['sinopsis'], 0, 80) . '...'; ?></p>
                
                <div class="botones">
                    <form method="POST" action="Mi_lista.php">
                        <input type="hidden" name="anime_id" value="<?php echo $anime['id_anime']; ?>">
                        <button type="submit" class="save-button">Guardar Anime</button>
                    </form>
                    <form method="POST" action="favoritos.php">
                        <input type="hidden" name="anime_id" value="<?php echo $anime['id_anime']; ?>">
                        <button type="submit" class="favorite-button">⭐</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
} else {
    echo "<p style='grid-column: 1/-1; text-align:center;'>No se encontraron resultados.</p>";
}
?>
</div>

<footer>
    <p>© 2026 DaiAnime. Todos los derechos reservados.</p>
</footer>
</body>
</html>