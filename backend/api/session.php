<?php
    require_once(__DIR__.'/../config/config.php');
    require_once(__DIR__.'/../config/generals.php');

    function iniciarSesion($usuario){
        session_start();
        $_SESSION['id'] = $usuario['id'];
        $_SESSION['email'] = $usuario['email'];
        $_SESSION['es_admin'] = $usuario['es_admin'];  
    }

    function cerrarSesion() {
        session_start();
        session_destroy();
        header('Location: /');
    }

    function estaAutenticado(){
        session_start();
        return isset($_SESSION['id']);
    }

    function getUsuarioAutenticado(){
        if (estaAutenticado()){
            $db = conectarBD();
            $id = mysqli_real_escape_string($db, $_SESSION['id']);
            $sql = "SELECT * FROM usuarios WHERE id = $id";
            $result = mysqli_query($db, $sql);
            if($result == false){
                return null;
            }
            return mysqli_fetch_assoc($result);
        }
    }

    function registrarUsuario($nombre, $email, $password, $contacto, $es_admin){
        $db = conectarBD();
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (nombre, email, password, contacto, es_admin) VALUES ('$nombre', '$email', '$password_hash', $contacto, $es_admin)";
        $result = mysqli_query($db, $sql);
        if ($result == false){
            outputError(403);
        }
        return mysqli_insert_id($db);
    }