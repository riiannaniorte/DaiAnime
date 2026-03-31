<?php
session_start();
// Conexión a la base de datos
$conexion = mysqli_connect("localhost", "Apache3", "redes", "anime");

// SEGURIDAD: Solo admins
if (!isset($_SESSION['id_usuario'])) {
    die("¡Epa! Primero inicia sesión.");
}

$id_logueado = $_SESSION['id_usuario'];
$consulta = mysqli_query($conexion, "SELECT rol FROM usuario WHERE id_usuario = $id_logueado");
$datos = mysqli_fetch_assoc($consulta);

if (!$datos || $datos['rol'] != 'admin') {
    die("Lo siento, necesitas nivel de Administrador para estar aquí.");
}
// --- ACCIÓN: CAMBIAR ROL ---
if(isset($_GET['cambiar_rol'])){
    $id = intval($_GET['cambiar_rol']);
    $buscar = mysqli_query($conexion,"SELECT rol FROM usuario WHERE id_usuario=$id");
    $dato = mysqli_fetch_assoc($buscar);
    if($dato){
        $nuevoRol = ($dato['rol'] == 'admin') ? 'usuario' : 'admin';
        mysqli_query($conexion,"UPDATE usuario SET rol='$nuevoRol' WHERE id_usuario=$id");
    }
    header("Location: admin.php"); exit;
}

// --- ACCIÓN: BANEO / DESBANEO ---
if(isset($_GET['banear'])){
    $id = intval($_GET['banear']);
    $buscar = mysqli_query($conexion,"SELECT activo FROM usuario WHERE id_usuario=$id");
    $u = mysqli_fetch_assoc($buscar);
    $nuevoEstado = ($u['activo'] == 1) ? 0 : 1;
    mysqli_query($conexion,"UPDATE usuario SET activo=$nuevoEstado WHERE id_usuario=$id");
    header("Location: admin.php"); exit;
}

// --- ACCIÓN: RESET DE PASS ---
if(isset($_GET['reset_pass'])){
    $id = intval($_GET['reset_pass']);
    $password_plana = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 8);
    $password_hash = password_hash($password_plana, PASSWORD_DEFAULT);
    mysqli_query($conexion, "UPDATE usuario SET password_hash = '$password_hash' WHERE id_usuario = $id");
    $_SESSION['alerta'] = "Nueva clave para ID $id: <strong style='color:yellow;'>$password_plana</strong>";
    header("Location: admin.php"); exit;
}

// --- ACCIÓN: ELIMINAR USUARIO ---
if(isset($_GET['eliminar_usuario'])){
    $id = intval($_GET['eliminar_usuario']);
    if($id != $id_logueado){
        mysqli_query($conexion,"DELETE FROM usuario WHERE id_usuario=$id");
    }
    header("Location: admin.php"); exit;
}

// --- ACCIÓN: BORRAR ANIME ---
if(isset($_GET['borrar'])){
    $id = intval($_GET['borrar']);
    mysqli_query($conexion,"DELETE FROM anime WHERE id_anime=$id");
    header("Location: admin.php"); exit;
}

// --- ACCIÓN: AÑADIR ANIME ---
$mensaje = isset($_SESSION['alerta']) ? $_SESSION['alerta'] : "";
unset($_SESSION['alerta']);

if (isset($_POST['titulo'])) {
    $titulo = $_POST['titulo'];
    $titulo_original = $_POST['titulo_original'];
    $sinopsis = $_POST['sinopsis'];
    $tipo = $_POST['tipo'];
    $estado = $_POST['estado'];
    $genero = $_POST['genero'];
    $estudio = $_POST['estudio'];
    $rating = $_POST['rating'];
    $fecha = $_POST['fecha'];

    $imagen = $_FILES['imagen']['name'];
    move_uploaded_file($_FILES['imagen']['tmp_name'], "imagenes/anime/" . $imagen);

    $sql = "INSERT INTO anime (titulo, titulo_original, sinopsis, tipo, estado, genero, estudio, rating, fecha_estreno, imagen_portada)
            VALUES ('$titulo','$titulo_original','$sinopsis','$tipo','$estado','$genero','$estudio','$rating','$fecha','$imagen')";

    if (mysqli_query($conexion, $sql)) { $mensaje = "Anime añadido correctamente"; }
    else { $mensaje = "Error al añadir anime"; }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin - DaiAnime</title>
    <link rel="stylesheet" href="admin.css">
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
            
            <li class="user-menu-item">
                <a href="admin.php" class="user-link">Admin</a>
                <ul class="submenú">
                    <li><a href="editor_generos.php">Editor de Géneros</a></li>
                </ul>
            </li>

            <li class="user-menu-item">
                <a href="#" class="user-link"><?php echo htmlspecialchars($_SESSION['usuario']); ?></a>
                <ul class="submenú">
                    <li><a href="Mi_lista.php">Mi Lista</a></li>
                    <li><a href="favoritos.php">Animes Favoritos</a></li>
                    <li class="logout-separator"><a href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </li>
        </ul>
    </nav>
</header>

<div class="container">
    <h1>Panel de Administración</h1>
    
    <?php if($mensaje): ?>
        <div class="mensaje"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <?php
    $resCount = mysqli_query($conexion, "SELECT COUNT(*) FROM anime");
    $totalAnimes = mysqli_fetch_row($resCount)[0];
    ?>
    <div style="background:#222; padding:20px; margin-bottom:30px; text-align:center; border:1px solid #333;">
        <h2>Total de Animes en Base de Datos</h2>
        <p style="font-size:28px; font-weight:bold; color:#e74c3c;"><?php echo $totalAnimes; ?></p>
    </div>

    <h2>Gestión de Usuarios</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>Rol</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
        <?php
        $usuarios = mysqli_query($conexion,"SELECT * FROM usuario ORDER BY id_usuario DESC");
        while($u = mysqli_fetch_assoc($usuarios)): ?>
        <tr>
            <td><?php echo $u['id_usuario']; ?></td>
            <td><?php echo $u['nombre_usuario']; ?></td>
            <td><strong><?php echo $u['rol']; ?></strong></td>
            <td><?php echo ($u['activo'] == 1) ? "✅ Activo" : "❌ Baneado"; ?></td>
            <td>
                <a href="?cambiar_rol=<?php echo $u['id_usuario']; ?>" class="btn btn-orange">Rol</a>
                <a href="?reset_pass=<?php echo $u['id_usuario']; ?>" class="btn btn-blue">Pass</a>
                <a href="?banear=<?php echo $u['id_usuario']; ?>" class="btn <?php echo ($u['activo']==1)?'btn-gray':'btn-green'; ?>">
                    <?php echo ($u['activo']==1)?'Ban':'Ok'; ?>
                </a>
                <?php if($u['id_usuario'] != $_SESSION['id_usuario']): ?>
                    <a href="?eliminar_usuario=<?php echo $u['id_usuario']; ?>" class="btn btn-red" onclick="return confirm('¿Borrar usuario?')">X</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <h2 style="margin-top:40px;">Añadir Nuevo Anime</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Título</label>
        <input type="text" name="titulo" required>
        
        <label>Título Original</label>
        <input type="text" name="titulo_original" required>
        
        <label>Sinopsis</label>
        <textarea name="sinopsis" rows="4" required></textarea>
        
        <label>Estudio</label>
        <input type="text" name="estudio" required>
        
        <label>Rating (0 - 10)</label>
        <input type="number" step="0.1" name="rating" required>

        <label>Tipo de contenido</label>
        <select name="tipo">
            <option value="TV">TV</option>
            <option value="Movie">Movie</option>
            <option value="OVA">OVA</option>
        </select>

        <label>Estado</label>
        <select name="estado">
            <option value="En emisión">En Emisión</option>
            <option value="Finalizado">Finalizado</option>
        </select>

        <label>Géneros</label>
        <input type="text" name="genero">
        
        <label>Fecha de Estreno</label>
        <input type="date" name="fecha" required>
        
        <label>Imagen de Portada</label>
        <input type="file" name="imagen" required>

        <button type="submit">Guardar Anime</button>
    </form>
    <h2 style="margin-top:40px;">Lista de Animes</h2>
    <table>
        <tr>
            <th>Título</th>
            <th>Año</th>
            <th>Acción</th>
        </tr>
        <?php
        $result = mysqli_query($conexion,"SELECT * FROM anime ORDER BY id_anime DESC");
        while($a = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo $a['titulo']; ?></td>
                <td><?php echo date("Y", strtotime($a['fecha_estreno'])); ?></td>
                <td>
                    <a href="?borrar=<?php echo $a['id_anime']; ?>" style="color:#e74c3c;" onclick="return confirm('¿Borrar?')">Borrar</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>