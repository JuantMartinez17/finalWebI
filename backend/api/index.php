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
}