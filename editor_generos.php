<?php
session_start();
// 1. Conexión a la base de datos
$conexion = mysqli_connect("localhost", "Apache3", "redes", "anime");

// 2. SEGURIDAD NIVEL ADMIN (Solo permitimos la entrada si es admin)
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php"); // Si no está logueado, al login
    exit;
}

$id_logueado = $_SESSION['id_usuario'];
$consulta = mysqli_query($conexion, "SELECT rol FROM usuario WHERE id_usuario = $id_logueado");
$datos = mysqli_fetch_assoc($consulta);

if (!$datos || $datos['rol'] != 'admin') {
    die("🚫 Acceso Denegado: Esta zona es exclusiva para Administradores.");
}

// 3. LÓGICA DE ACTUALIZACIÓN
if (isset($_POST['actualizar_generos_masivo'])) {
    $id_anime = intval($_POST['id_anime']);
    $generos_array = isset($_POST['generos_lista']) ? array_map('trim', $_POST['generos_lista']) : [];
    $generos_string = implode(",", $generos_array);
    
    mysqli_query($conexion, "UPDATE anime SET genero = '$generos_string' WHERE id_anime = $id_anime");
    $mensaje_exito = "✅ Cambios guardados para el anime ID: $id_anime";
}

// 4. DATOS PARA LA VISTA
$lista_maestra = ["Acción", "Artes Marciales", "Aventuras", "Carreras", "Ciencia Ficción", "Comedia", "Demencia", "Demonios", "Deportes", "Drama", "Ecchi", "Escolares", "Espacial", "Fantasía", "Harem", "Historico", "Infantil", "Josei", "Juegos", "Magia", "Mecha", "Militar", "Misterio", "Música", "Parodia", "Policía", "Psicológico", "Recuentos de la vida", "Romance", "Samurai", "Seinen", "Shoujo", "Shounen", "Sobrenatural", "Superpoderes", "Suspenso", "Terror", "Vampiros", "Yaoi", "Yuri"];

$query_animes = mysqli_query($conexion, "SELECT id_anime, titulo, genero, imagen_portada FROM anime ORDER BY titulo ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editor de Géneros - DaiAnime</title>
    <style>
        body { background: #0a0a0a; color: #eee; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        
        .header-bar { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e67e22; padding-bottom: 10px; margin-bottom: 20px; }
        .btn-back { background: #333; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-size: 14px; }
        .btn-back:hover { background: #444; }

        /* Estilo del Acordeón (Nativo sin Scripts) */
        details { background: #1a1a1a; margin-bottom: 8px; border-radius: 4px; border: 1px solid #333; }
        summary { padding: 12px; cursor: pointer; list-style: none; display: flex; align-items: center; justify-content: space-between; font-weight: bold; outline: none; }
        summary:hover { background: #222; }
        
        .anime-info { display: flex; align-items: center; gap: 15px; }
        .mini-img { width: 35px; height: 50px; object-fit: cover; border-radius: 2px; }
        .generos-actuales { font-size: 12px; color: #777; font-weight: normal; }

        /* Contenido del Panel Abierto */
        .panel-content { padding: 20px; background: #000; border-top: 1px solid #e67e22; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 15px; }
        
        .label-check { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #bbb; cursor: pointer; padding: 4px; }
        .label-check:hover { background: #222; color: #fff; }
        .active-gen { color: #2ecc71; font-weight: bold; }
        
        input[type="checkbox"] { accent-color: #2ecc71; transform: scale(1.1); }

        .btn-save { background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-save:hover { background: #2ecc71; }
        
        .alert-ok { background: #2ecc71; color: white; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-bar">
        <h1>🛠️ Editor Masivo de Géneros</h1>
        <a href="admin.php" class="btn-back">⬅ Volver al Panel</a>
    </div>

    <?php if(isset($mensaje_exito)) echo "<div class='alert-ok'>$mensaje_exito</div>"; ?>

    <div class="lista-animes">
        <?php while ($anime = mysqli_fetch_assoc($query_animes)): 
            $actuales = array_map('trim', explode(",", $anime['genero']));
        ?>
        
        <details>
            <summary>
                <div class="anime-info">
                    <img src="imagenes/anime/<?php echo $anime['imagen_portada']; ?>" class="mini-img">
                    <span><?php echo htmlspecialchars($anime['titulo']); ?></span>
                </div>
                <span class="generos-actuales"><?php echo htmlspecialchars($anime['genero']); ?></span>
            </summary>

            <div class="panel-content">
                <form method="POST">
                    <input type="hidden" name="id_anime" value="<?php echo $anime['id_anime']; ?>">
                    
                    <button type="submit" name="actualizar_generos_masivo" class="btn-save">GUARDAR CAMBIOS</button>

                    <div class="grid-4">
                        <?php foreach($lista_maestra as $g): 
                            $marcado = in_array($g, $actuales);
                        ?>
                        <label class="label-check <?php echo $marcado ? 'active-gen' : ''; ?>">
                            <input type="checkbox" name="generos_lista[]" value="<?php echo $g; ?>" <?php echo $marcado ? 'checked' : ''; ?>>
                            <?php echo $g; ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </form>
            </div>
        </details>

        <?php endwhile; ?>
    </div>
</div>

</body>
</html>