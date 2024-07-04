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
        return;
    }
    $password_hash = password_hash($password, PASSWORD_DEFAULT); //Creo hash para encriptar la contraseña
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

function postPublicaciones() {
    $db = conectarBD();
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['usuario_id']) || !isset($data['raza']) || !isset($data['lugar']) || !isset($data['foto'])) {
        outputError(400);
        return;
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
        $id_raza = settype($toUpdateRaza['id'], 'integer');
        $sql = "UPDATE razas SET cantidad_publicaciones = cantidad_publicaciones + 1 WHERE id = $id_raza";
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
        $id_raza = mysqli_insert_id($db);
    }
    //Ahora, puedo insertar la nueva publicacion
    $sql = "INSERT INTO publicaciones (usuario_id, id_raza, lugar, foto) VALUES ($usuario_id, $id_raza, '$lugar', '$foto')";
    $result = mysqli_query($db, $sql);
    if ($result === false){
        print mysqli_error($db);
        outputError(500);//Internal server error
        return;
    }
    mysqli_close($db);
    outputJson(["message" => "Nueva publicacion creada exitosamente"], 201);//Created
}

function postRazas() {
    $db = conectarBD();
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['nombre'])) {
        print mysqli_error($db);
        outputError([400, "El nombre es requerido"]);//Bad request
    }
    $nombre = ucfirst(strtolower(trim($data['nombre'])));
    $nombre = mysqli_real_escape_string($db, $nombre);

    //Me fijo que no se repita
    $sql = "SELECT id FROM razas WHERE nombre = '$nombre'";
    $result = mysqli_query($db, $sql);
    if ($result === false) {
        print mysqli_error($db);
        outputError([500, "Error al verificar la existencia de la raza"]);
        return;
    }
    if(mysqli_num_rows($result) > 0){
        //Entonces la raza ya existe, no la inserto
        print mysqli_error($db);
        outputError([409, "La raza que se intento ingresar ya existe"]);//Conflict
        return;
    }
    $sql = "INSERT INTO razas (nombre) VALUES ('$nombre')";
    $result = mysqli_query($db, $sql);
    if ($result === false) {
        print mysqli_error($db);
        outputError(500);//Internal server error
    }
    $insert_id = mysqli_insert_id($db);
    mysqli_close($db);
    outputJson(["message" => "Nueva raza ($insert_id) creada exitosamente"]);
}

function patchUsuarios($id) {
    settype($id, 'integer');
    $db = conectarBD();
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['nombre']) || !isset($data['email']) || !isset($data['contacto']) || !isset($data['es_admin'])){
        outputError([400, "No se completaron los campos requeridos"]);
        print mysqli_error($db);
    }
    //Busco el usuario con el id enviado
    $sql = "SELECT * FROM usuarios WHERE id = $id";
    $result = mysqli_query($db, $sql);
    if ($result === false){
        print mysqli_error($db);
        outputError([404, "No se encontro usuario con el id proporcionado"]);
        mysqli_close($db);
        return;
    }else{
        $nombre = $data['nombre'];        
        $nombre = mysqli_real_escape_string($db, $nombre);
        $email = $data['email'];        
        $email = mysqli_real_escape_string($db, $email);
        $contacto = $data['contacto'];        
        $contacto = mysqli_real_escape_string($db, $contacto);
        $es_admin = $data['es_admin'];
        if ($es_admin != true && $es_admin != false && $es_admin != 1 && $es_admin != 0){
            outputError([400, "El campo es_admin debe ser boolean"]);
        }
        $sql = "UPDATE usuarios SET nombre = '$nombre', email = '$email', contacto = '$contacto', es_admin = $es_admin WHERE id = $id";
        $result = mysqli_query($db, $sql);
        if ($result === false){
            print mysqli_error($db);
            outputError([500, "Error al actualizar el usuario"]);
        }
        mysqli_close($db);
        outputJson(["message" => "Se actualizo el usuario con id $id"]);
    }
}
function patchPublicaciones($id_publicacion, $id_usuario) {
    settype($id_publicacion, 'integer');
    settype($id_usuario, 'integer');
    $db = conectarBD();
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Verifico que se haya ingresado algún campo
    if (!isset($data['raza']) && !isset($data['lugar']) && !isset($data['foto'])){
        outputError([500, 'No se ingresaron los datos para actualizar']); // Internal server error
        return;
    }
    
    // Busco la publicación y verifico que sea del usuario
    $sql = "SELECT * FROM publicaciones WHERE id = $id_publicacion AND usuario_id = $id_usuario";
    $result = mysqli_query($db, $sql);
    if (mysqli_num_rows($result) < 1){
        outputError([404, 'Publicación no encontrada o no pertenece al usuario']);
        mysqli_close($db);
        return;
    }
    
    $publicacion = mysqli_fetch_assoc($result);
    $id_raza_antigua = $publicacion['id_raza'];
    
    // Me fijo qué campos voy a actualizar
    $updates = [];
    if (isset($data['raza'])){
        $raza = ucfirst(strtolower(trim($data['raza'])));
        $raza = mysqli_real_escape_string($db, $raza);
        $sql = "SELECT id FROM razas WHERE nombre = '$raza'";
        $result = mysqli_query($db, $sql);
        if (mysqli_num_rows($result) > 0) {
            $fila = mysqli_fetch_assoc($result);
            $id_raza = $fila['id'];
            // Actualizo la cantidad de publicaciones de la nueva raza
            $sql = "UPDATE razas SET cantidad_publicaciones = cantidad_publicaciones + 1 WHERE id = $id_raza";
            $result = mysqli_query($db, $sql);
            if ($result === false){
                outputError([500, 'Error al actualizar cantidad de publicaciones']);
                mysqli_close($db);
                return;
            }
        } else {
            // Debo insertar una nueva raza
            $sql = "INSERT INTO razas (nombre, cantidad_publicaciones) VALUES ('$raza', 1)";
            $result = mysqli_query($db, $sql);
            if ($result === false){
                outputError([500, 'Error al crear nueva raza']);
                mysqli_close($db);
                return;
            }
            $id_raza = mysqli_insert_id($db);
        }
        $updates[] = "id_raza = $id_raza";
        // La raza antigua se resta una publicación
        $sql = "UPDATE razas SET cantidad_publicaciones = cantidad_publicaciones - 1 WHERE id = $id_raza_antigua";
        $result = mysqli_query($db, $sql);
        if ($result === false){
            outputError([500, 'Error al actualizar publicaciones de la raza antigua']);
            mysqli_close($db);
            return;
        }
    }
    
    if (isset($data['lugar'])) {
        $lugar = mysqli_real_escape_string($db, $data['lugar']);
        $updates[] = "lugar = '$lugar'";
    }
    
    if (isset($data['foto'])) {
        $foto = mysqli_real_escape_string($db, $data['foto']);
        $updates[] = "foto = '$foto'";
    }
    
    // Ahora puedo actualizar la publicación
    if (count($updates) > 0){
        $update_sql = "UPDATE publicaciones SET " . implode(',', $updates) . " WHERE id = $id_publicacion";
        $result = mysqli_query($db, $update_sql);
        if ($result === false){
            outputError([500, 'Error al actualizar la publicación']);
            mysqli_close($db);
            return;
        }
    }
    mysqli_close($db);
    outputJson(["message" => "Se actualizó la publicación $id_publicacion con éxito."]);
}


function deleteUsuarios($id) {
    settype($id, 'integer');
    $db = conectarBD();
    $sql = "DELETE FROM usuarios WHERE id = $id";
    $result = mysqli_query($db, $sql);
    if ($result === false) {
        print mysqli_error($db);
        outputError(500);//Internal server error
        mysqli_close($db);
        return;
    }
    if (mysqli_affected_rows($db) == 0){
        outputError([404, 'No se encontro el usuario a eliminar']);
    }
    mysqli_close($db);
    outputJson(["message " => "Se elimino correctamente el usuario $id"]);
}

function deletePublicaciones($id) {
    settype($id, 'integer');
    $db = conectarBD();
    $sql = "DELETE FROM publicaciones WHERE id = $id";
    $result = mysqli_query($db, $sql);
    if ($result === false){
        print mysqli_error($db);
        outputError(500);//Internal server error
        mysqli_close($db);
        return;
    }
    if (mysqli_affected_rows($db) == 0) {
        outputError([404, 'No se encontro la publicacion a eliminar']);
    }
    mysqli_close($db);
    outputJson(["message " => "Se elimino correctamente la publicacion $id"]);
}

function deleteRazas($id) {
    settype($id, 'integer');
    $db = conectarBD();
    $sql = "DELETE FROM razas WHERE id = $id";
    $result = mysqli_query($db, $sql);
    if ($result === false){
        print mysqli_error($db);
        outputError(500);//Internal server error
        mysqli_close($db);
        return;
    }
    if (mysqli_affected_rows($db) == 0) {
        outputError([404, 'No se encontro la raza a eliminar']);
    }
    mysqli_close($db);
    outputJson(["message " => "Se elimino correctamente la raza $id"]);
}
