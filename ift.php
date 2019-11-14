#!/usr/bin/php -q
<?php
/*
    Requisitos:
        PHP en CLI + MySQLi

    Uso:
        Invocar desde Asterisk como:
            AGI(ift.php,${EXTEN})

        La variable ${EXTEN} debe ser de al menos 10 dígitos

        Este script no hace ningún tipo de llamada. Únicamente llena algunas variables para que podamos utilizarlas en
        nuestra troncal al momento de marcar. Las variables creadas son:

            - ${MOVIL} contiene 1 o 0, indicando si el número ingresado es móvil o fijo
            - ${PREFIJO} contiene el prefijo de larga distancia, celular, o celular LDN (01, 044 o 045) que se necesite
                        según el tipo de número obtenido
            - ${COMPLETO} contiene el número completo tal como se marca en la mayoría de los carriers:
                    XXXXXXX para números fijos locales
                    01XXXXXXXXXX para números fijos de LD
                    044XXXXXXXXXX para números móviles locales
                    045XXXXXXXXXX para números móviles de LD


    Cambios recientes
        2019-04-03 - Actualización a uso de MySQLi y apagado de reporteo
        2019-07-18 - Agregamos soporte para múltiples códigos de área locales
        2019-07-19 - Corrección de la variable $local

*/


// Cambiar el contenido de este arreglo por todos los códigos de área que puedan interpretarse como locales
$area_local = array('55','56');

// Parametros de acceso a la base de datos. Debe existir la tabla `ift`. Usa el archivo ift.sql o ift_extended.sql para crearla
$db['user'] = 'cron';
$db['pass'] = '1234';
$db['name'] = 'asterisk';
$db['host'] = 'localhost';



//
// ***************** No deberías tener que cambiar nada a partir de este punto
//

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
elseif (!$data = mysqli_connect($db['host'],$db['user'],$db['pass'])) {
    $agi->verbose('Error de conexion a la BD');
    $agi->set_variable('MOVIL',0);
    $agi->set_variable('PREFIJO','');
    $agi->set_variable('COMPLETO','');
    exit;
}

// Nos quedamos solo con los ultimos 10 digitos para asegurar que quitamos cualquier prefijo
$numero = substr($argv[1],-10);

// Definimos los diferentes componentes del número
// La base de datos contiene los códigos de área a 3 dígitos para mayor facilidad (y evitar el problema con 55, 33 y 81)
$area   = substr($numero,0,3);
$local  = substr($numero,3);
$serie  = substr($numero,3,3);

$rango  = substr($numero,6);

// Por default un número no es local. Evaluaremos todo el arreglo $local para saber si el número es foráneo o local
$es_local = FALSE;
foreach ($area_local as $x) {
    if (substr($numero,0,strlen($x)) == $x) {
        $es_local = TRUE;
        $local  = substr($numero,strlen($x));
        break;
    }
}



$query_base = "SELECT movil FROM %s.ift AS i WHERE i.area = %s AND i.serie = %s AND %s BETWEEN i.inicial AND i.final LIMIT 1";
$query      = sprintf($query_base,$db['name'],$area,$serie,$rango);
$result     = mysqli_query($data,$query);
$row        = mysqli_fetch_assoc($result);
$agi->set_variable('MOVIL',$row['movil']);

// Si el número es del mismo código de área que la constante LOCAL
if ($es_local) {
    if ($row['movil'] == 1) {       // Número móvil local
        $agi->set_variable('PREFIJO','044');
        $agi->set_variable('COMPLETO', '044'.$numero);
    }
    else {                          // Número fijo local
        $agi->set_variable('PREFIJO','');
        $agi->set_variable('COMPLETO', $local);
    }
}
else {                              // Número móvil LDN
    if ($row['movil'] == 1) {
        $agi->set_variable('PREFIJO','045');
        $agi->set_variable('COMPLETO', '045'.$numero);
    }
    else {                          // Número fijo LDN
        $agi->set_variable('PREFIJO','01');
        $agi->set_variable('COMPLETO', '01'.$numero);
    }
}