<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, X-Auth-Token");
header('Access-Control-Allow-Methods: POST, GET, PATCH, DELETE');
header("Allow: GET, POST, PATCH, DELETE");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    return 0;
}

require_once(__DIR__.'/../config/config.php');
require_once(__DIR__.'/../config/generals.php');

$metodo = strtolower($_SERVER['REQUEST_METHOD']);
$accion = isset($_GET['accion']) ? explode('/', strtolower($_GET['accion'])) : [];
$funcionNombre = $metodo . (isset($accion[0]) ? ucfirst($accion[0]) : '');
$parametros = array_slice($accion, 1);

if (count($parametros) > 0 && $metodo == 'get') {
    $funcionNombre = $funcionNombre . 'ConParametros';
}

if (function_exists($funcionNombre)) {
    call_user_func_array($funcionNombre, $parametros);
} else {
    outputError(400);//bad rerquest
}

function postRestablecer(){
    $db = conectarBD();
    $sql = sf__restablecerSql();
    $result = mysqli_multi_query($db, $sql);
    if ($result === false){
        print mysqli_error($db);
        outputError(500);
    }
    mysqli_close($db);
    outputJson([], 201);//201 = created
}

function getUsuarios() {
    $db = conectarBD();
    $sql = "SELECT * FROM usuarios";
    $result = mysqli_query($db, $sql);
    if ($result === false){
        print mysqli_error($db);
        outputError(500);//Internal server error
    }
    $retorno = [];
    while ($fila = mysqli_fetch_assoc($result)){
        settype($fila['id'], 'integer');
        $retorno[] = $fila;
    }
    mysqli_free_result($result);
    mysqli_close($db);
    outputJson($retorno);
}

function getPublicaciones() {
    $db = conectarBD();
    $sql = "SELECT * FROM publicaciones";
    $result = mysqli_query($db, $sql);
    if ($result === false){
        print mysqli_error($db);
        outputError(500);//Internal server error
    }
    $retorno = [];
    while($fila = mysqli_fetch_assoc($result)){
        settype($fila['id'], 'integer');
        settype($fila['usuario_id'], 'integer');
        $retorno [] = $fila;
    }
    mysqli_free_result($result);
    mysqli_close($db);
    outputJson($retorno);
}

function getRazas() {
    $db = conectarBD();
    $sql = "SELECT * FROM razas";
    $result = mysqli_query($db, $sql);
    if ($result === false){
        print mysqli_error($db);
        outputError(500);///Internal server error
    }
    $retorno = [];
    while ($fila = mysqli_fetch_assoc($result)){
        settype($fila['id'], 'integer');
        settype($fila['cantidad_publicaciones'], 'integer');
        $retorno [] = $fila;
    }
    mysqli_free_result($result);
    mysqli_close($db);
    outputJson($retorno);
}

function getUsuariosConParametros($id) {
    $db = conectarBD();
    settype($id, 'integer');
    $sql = "SELECT * FROM usuarios where usuarios.id = $id";
    $result = mysqli_query($db, $sql);
    if ($result === false){
        print mysqli_error($db);
        outputError(500);//Internal server error
    }
    if (mysqli_num_rows($result) == 0){
        outputError(404);//Not found
    }
    $retorno = mysqli_fetch_assoc($result);
    settype($retorno['id'], 'integer');
    mysqli_free_result($result);
    mysqli_close($db);
    outputJson($retorno);
}

function getPublicacionesConParametros($id) {
    $db = conectarBD();
    settype($id, 'integer');
    $sql = "SELECT * FROM publicaciones where publicaciones.id = $id";
    $result = mysqli_query($db, $sql);
    if ($result === false){
        print mysqli_error($db);
        outputError(500);//Internal server error
    }
    if (mysqli_num_rows($result) == 0){
        outputError(404);//Not found
    }
    $retorno = mysqli_fetch_assoc($result);
    settype($retorno['id'], 'integer');
    settype($retorno['usuario_id'], 'integer');
    settype($retorno['id_raza'], 'integer');
    mysqli_free_result($result);
    mysqli_close($db);
    outputJson($retorno);
}

function getRazasConParametros($id) {
    $db = conectarBD();
    settype($id, 'integer');
    $sql = "SELECT * FROM razas WHERE $id = razas.id";
    $result  = mysqli_query($db, $sql);
    if ($result === false){
        print mysqli_error($db);
        outputError(500);
    }
    $retorno  = mysqli_fetch_assoc($result);
    settype($retorno['id'], 'integer');
    settype($retorno['cantidad_publicaciones'], 'integer');
    mysqli_free_result($result);
    mysqli_close($db);
    outputJson($retorno);
}

function postUsuarios() {
    $db = conectarBD();
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['nombre']) || !isset($data['email']) || !isset($data['password']) || !isset($data['contacto'])){
        outputError(400);//Bad request
    }
    $nombre = $data['nombre'];
    $email = $data['email'];
    $password = $data['password'];
    $contacto = $data['contacto'];
    //Verifico que el mail no exista en otro usuario
    $email = mysqli_real_escape_string($db, $email);
    $sql = "SELECT usuarios.email FROM usuarios WHERE '$email' = usuarios.email";
    $result = mysqli_query($db, $sql);
    if (mysqli_num_rows($result) > 0){
        print mysqli_error($db);
        outputError('El mail pertenece a otro usuario');
        die;
    }
    $password_hash = password_hash($password, PASSWORD_BCRYPT); //Creo hash para encriptar la contraseña
    $nombre = mysqli_real_escape_string($db, $nombre);
    $contacto = mysqli_real_escape_string($db, $nombre);
    $sql = "INSERT INTO usuarios (nombre, email, password, contacto) VALUES ('$nombre', '$email', '$password_hash', '$contacto')";
    $result = mysqli_query($db, $sql);
    if ($result === false){
        print mysqli_error($db);
        outputError(500);//Internal server error
    }
    mysqli_close($db);
    outputJson(["message" => "Usuario creado exitosamente"], 201);//Created
}

function postPublicacion() {
    $db = conectarBD();
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['usuario_id']) || !isset($data['raza']) || !isset($data['lugar']) || !isset($data['foto'])) {
        outputError(400);
    }
    $usuario_id = settype($data['usuario_id'], 'integer');
    $raza = ucfirst(strtolower(trim($data['raza'])));
    $lugar = $data['lugar'];
    $foto = $data['foto'];

    //Verifico si la raza ya ha sido ingresada
    $raza = mysqli_real_escape_string($db, $raza);
    $sql = "SELECT id FROM razas WHERE nombre = '$raza'";
    $result = mysqli_query($db, $sql);

    if (mysqli_num_rows($result) > 0){
        //La raza ya existe, por lo que solo actualizo su cantidad_publicaciones
        $toUpdateRaza = mysqli_fetch_assoc($result);
        $id = settype($toUpdateRaza['id'], 'integer');
        $sql = "UPDATE razas SET cantidad_publicaciones = cantidad_publicaciones + 1 WHERE id = $id";
        $result = mysqli_query($db, $sql);
        if ($result === false){
            print mysqli_error($db);
            outputError(500);
            return;
        }
    }else{
        //Como la raza nunca ingreso, debo crear una nueva
        $sql = "INSERT INTO razas (nombre, cantidad_publicaciones) VALUES ('$raza', 1)";
        $result = mysqli_query($db, $sql);
        if ($result === false) {
            print mysqli_error($db);
            outputError(500);//Internal server error
            return;
        }
        $insert_id = mysqli_insert_id($db);
    }
    //Ahora, puedo insertar la nueva publicacion
    $sql = "INSERT INTO publicaciones (usuario_id, id_raza, lugar, foto) VALUES ($usuario_id, $insert_id, '$lugar', '$foto')";
    $result = mysqli_query($db, $sql);
    if ($result === false){
        print mysqli_error($db);
        outputError(500);//Internal server error
        return;
    }
    mysqli_close($db);
    outputJson(["message" => "Nueva publicacion creada exitosamente"], 201);//Created
}