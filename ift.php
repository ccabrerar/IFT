#!/usr/bin/php -q
<?php
/*
	Cambios recientes
		2019-04-03 - Actualización a uso de MySQLi y apagado de reportes
*/


// Cambiar esta constante por el codigo de area local desde donde se originan las llamadas
define('LOCAL','55');
 
// Parametros de acceso a la base de datos.
$db['user'] = 'cron';
$db['pass'] = '1234';
$db['name'] = 'asterisk';
$db['host'] = 'localhost';
 
 
// ***************** No cambiar nada a partir de este punto

// Apagamos el reporteo de errores (los AGI no se llevan bien con errores en el stdout)
error_reporting(0);
require_once 'phpagi.php';
$agi = new AGI(); 
 
if ( (!is_numeric($argv[1])) || (strlen($argv[1]) < 10) ) {
    $agi->verbose('No se proporciono un numero de al menos 10 digitos');
    $agi->set_variable('MOVIL',0);
    $agi->set_variable('PREFIJO','');
    $agi->set_variable('COMPLETO','');
    exit;
}
 
// Nos quedamos solo con los ultimos 10 digitos para asegurar que quitamos cualquier prefijo
$numero = substr($argv[1],-10);
 
if (!$data = mysqli_connect($db['host'],$db['user'],$db['pass'])) {
    $agi->verbose('Error de conexion a la BD');
    $agi->set_variable('MOVIL',0);
    $agi->set_variable('PREFIJO','');
    $agi->set_variable('COMPLETO','');
    exit;
}
 
// Definimos codigos de area de 2 digitos para conocer cual es el codigo de area y cual es el numero local
$area = substr($numero,0,3);
$local= substr($numero,3); 
$serie= substr($local,0,3);

$rango = substr($numero,6);

$query_base = "SELECT movil FROM %s.ift AS i WHERE i.area = %s AND i.serie = %s AND %s BETWEEN i.inicial AND i.final LIMIT 1";
$query      = sprintf($query_base,$db['name'],$area,$serie,$rango);
$result     = mysqli_query($data,$query);
$row        = mysqli_fetch_assoc($result);
$agi->set_variable('MOVIL',$row['movil']);
 
if (substr($numero,0,strlen(LOCAL)) == LOCAL) {
    if ($row['movil'] == 1) {
        $agi->set_variable('PREFIJO','044');
        $agi->set_variable('COMPLETO', '044'.$numero);
    }
    else {
        $agi->set_variable('PREFIJO','');
        $agi->set_variable('COMPLETO', $local);
    }
}
else {
    if ($row['movil'] == 1) {
        $agi->set_variable('PREFIJO','045');
        $agi->set_variable('COMPLETO', '045'.$numero);
    }
    else {
        $agi->set_variable('PREFIJO','01');
        $agi->set_variable('COMPLETO', '01'.$numero);
    }
}
