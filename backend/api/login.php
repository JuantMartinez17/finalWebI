<?php
    require_once(__DIR__.'/../config/config.php');
    require_once(__DIR__.'/../config/generals.php');

    $db = conectarBD();
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['email']) || !isset($data['password'])){
        if(empty($data['email'])){
            echo '<div class="alert alert-primary" role="alert">
                Debe ingresar un correo electrónico
            </div>';
        }else if (empty($data['password'])){
            echo '<div class="alert alert-primary" role="alert">
                Debe ingresar una contraseña válida
            </div>';
        }
    }else{
        $email = mysqli_real_escape_string($db, $data['email']);
        $password = mysqli_real_escape_string($db, $data['password']);

        $sql = "SELECT * FROM usuarios WHERE email = $email";
        $result = mysqli_query($db, $sql);

        if(mysqli_num_rows($result) === 1){
            $usuario = mysqli_fetch_assoc($result);
            if($usuario['password'] === $password){
                iniciarSesion($usuario);
            }
            outputJson($usuario);
        }else{
            outputError([403, "Usuario no encontrado o clave incorrecta"]);
        }
    }
    
