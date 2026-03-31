<?php
session_start();
// Conexión a la base de datos
$conexion = mysqli_connect("localhost", "Apache3", "redes", "anime");

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

$error = "";
$modo = "login";

// Si viene por URL el modo (form=registro o form=recuperar)
if (isset($_GET['form'])) {
    $modo = $_GET['form'];
}

// --- PROCESAR FORMULARIOS ---
if (isset($_POST['modo'])) {
    $modo = $_POST['modo'];
    $user = isset($_POST['user']) ? trim($_POST['user']) : "";
    $pass = $_POST['pass'] ? $_POST['pass'] : "";

    // --- MODO LOGIN ---
    if ($modo == "login") {
        $sql = "SELECT * FROM usuario WHERE nombre_usuario = '$user'";
        $resultado = mysqli_query($conexion, $sql);
        
        if ($fila = mysqli_fetch_assoc($resultado)) {
            if ($fila['activo'] == 0) {
                $error = "Tu cuenta ha sido suspendida. Contacta con el administrador.";
            } 
            else if (password_verify($pass, $fila['password_hash'])){
                $_SESSION['id_usuario'] = $fila['id_usuario'];
                $_SESSION['usuario'] = $fila['nombre_usuario'];
                $_SESSION['rol'] = $fila['rol']; 

                shell_exec("/usr/local/bin/notificar_login.sh '$user'");
                
                header("Location: index.php");
                exit;
            } else {
                $error = "Contraseña incorrecta";
            }
        } else {
            $error = "El usuario no existe";
        }
    }

    // --- MODO REGISTRO ---
    if ($modo == "registro") {
        $pass2 = $_POST['pass2'];
        $email = $_POST['email'];
        
        if ($pass != $pass2) {
            $error = "Las contraseñas no coinciden";
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuario (nombre_usuario, password_hash, email, activo, rol) VALUES ('$user', '$hash', '$email', 1, 'usuario')";
            
            if (mysqli_query($conexion, $sql)) {
                $error = "Cuenta creada correctamente. ¡Ya puedes entrar!";
                $modo = "login";
            } else {
                $error = "Error: El usuario o email ya están registrados";
            }
        }
    }

   if ($modo == "recuperar") {
    $email = trim($_POST['email']);
    $pass = $_POST['pass'];
    $pass2 = $_POST['pass2'];

    if ($pass != $pass2) {
        $error = "Las contraseñas nuevas no coinciden";
    } else {
        // Preparamos la consulta para evitar errores de comillas
        $email_safe = mysqli_real_escape_string($conexion, $email);
        $check = "SELECT * FROM usuario WHERE email = '$email_safe'";
        $res = mysqli_query($conexion, $check);

        if ($res && mysqli_num_rows($res) > 0) {
            $nuevo_hash = password_hash($pass, PASSWORD_DEFAULT);
            $update = "UPDATE usuario SET password_hash = '$nuevo_hash' WHERE email = '$email_safe'";
            
            if (mysqli_query($conexion, $update)) {
                shell_exec("/usr/local/bin/notificar_cambio.sh '$email_safe'");
                $error = "Contraseña actualizada con éxito.";
                $modo = "login";
            } else {
                $error = "Error al actualizar la contraseña: " . mysqli_error($conexion);
            }
        } else {
            $error = "Ese correo electrónico no está en nuestra base de datos";
        }
    }
}
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>DaiAnime - Acceso</title>
    <link rel="icon" type="image/png" href="DaiAnime.jpeg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background: url('anime2.png') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: #1c1b1b;
            width: 350px;
            padding: 30px;
            border: 1px solid #333;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
            color: white;
            font-size: 24px;
            font-weight: bold;
        }
        
        .logo img {
            max-width: 150px;
        }
        
        input {
            width: 100%;
            padding: 10px 0;
            margin-bottom: 20px;
            background: transparent;
            border: none;
            border-bottom: 1px solid #333;
            color: white;
            outline: none;
        }
        
        .forgot {
            text-align: right;
            margin-bottom: 25px;
        }
        
        .forgot a {
            color: #999;
            text-decoration: none;
            font-size: 12px;
        }
        
        .buttons {
            display: flex;
            gap: 10px;
        }
        
        button {
            flex: 1;
            padding: 10px;
            border: 1px solid red;
            background: transparent;
            color: red;
            cursor: pointer;
            text-transform: uppercase;
            font-size: 13px;
        }
        
        .btn-red {
            background: red;
            color: white;
            border: 1px solid red;
        }
        
        .terms {
            color: #666;
            font-size: 11px;
            text-align: center;
            margin: 20px 0;
            line-height: 1.4;
        }
        
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
            font-size: 13px;
            border: 1px solid red;
            padding: 8px;
        }
    </style>
</head>
<body>

<?php if($modo == "login"): ?>
<div class="container">
    <div class="logo"><img src="DaiAnime.jpeg" alt="DaiAnime"></div>
    <?php if($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST">
        <input type="hidden" name="modo" value="login">
        <div style="color: white;">Nombre de usuario</div>
        <input type="text" name="user" required>
        <div style="color: white;">Contraseña</div>
        <input type="password" name="pass" required>
        <div class="forgot">
            <a href="?form=recuperar">¿Has olvidado tu contraseña?</a>
        </div>
        <div class="buttons">
            <button type="button" onclick="window.location.href='?form=registro'" class="btn-red">Registrarme</button>
            <button type="submit">Iniciar sesión</button>
        </div>
    </form>
</div>
<?php endif; ?>

<?php if($modo == "registro"): ?>
<div class="container">
    <div class="logo"><img src="DaiAnime.jpeg" alt="DaiAnime"></div>
    <?php if($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST">
        <input type="hidden" name="modo" value="registro">
        <div style="color: white;">Correo electrónico</div>
        <input type="email" name="email" required>
        <div style="color: white;">Nombre de usuario</div>
        <input type="text" name="user" required>
        <div style="color: white;">Contraseña</div>
        <input type="password" name="pass" required>
        <div style="color: white;">Vuelve a escribir la contraseña</div>
        <input type="password" name="pass2" required>
        <div class="terms">Al registrarte aceptas los términos y políticas de DaiAnime</div>
        <div class="buttons">
            <button type="button" onclick="window.location.href='?form=login'">Volver</button>
            <button type="submit" class="btn-red">Registrarme</button>
        </div>
    </form>
</div>
<?php endif; ?>

<?php if($modo == "recuperar"): ?>
<div class="container">
    <div class="logo"><img src="DaiAnime.jpeg" alt="DaiAnime"></div>
    <div style="color: white; text-align: center; margin-bottom: 20px; font-size: 14px;">Recuperar Contraseña</div>
    <?php if($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST">
        <input type="hidden" name="modo" value="recuperar">
        <div style="color: white;">Introduce tu correo</div>
        <input type="email" name="email" required>
        <div style="color: white;"> Nueva contraseña</div>
        <input type="password" name="pass" required>
        <div style="color: white;">Repite la nueva contraseña</div>
        <input type="password" name="pass2" required>
        <div class="buttons">
            <button type="button" onclick="window.location.href='?form=login'">Cancelar</button>
            <button type="submit" class="btn-red">Cambiar</button>
        </div>
    </form>
</div>
<?php endif; ?>

</body>
</html>