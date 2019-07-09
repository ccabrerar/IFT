<?php
/*
	Genera el archivo .sql a partir del CSV del Plan Nacional de Numeración.
	La versión más reciente del PNN se consigue desde https://sns.ift.org.mx:8081/sns-frontend/planes-numeracion/descarga-publica.xhtml
	
	Uso:		php pnn.php  (no recibe argumentos)
	Requiere:	La existencia del archivo pnn.csv en el mismo directorio
	
	Este script entrega el archivo .sql en el mismo directorio donde se invocó. No lo carga a la BD.
	
*/


$table_name = 'ift';
$batch_size = 500;
$filename 	= 'pnn.csv';
$destfile 	= $table_name."_extended.sql";

$query_inicial = "DROP TABLE IF EXISTS ".$table_name."_extended;
 
CREATE TABLE `".$table_name."_extended` (
  `area` mediumint(9) unsigned NOT NULL,
  `serie` mediumint(8) unsigned NOT NULL,
  `inicial` mediumint(8) unsigned NOT NULL,
  `final` mediumint(8) unsigned NOT NULL,
  `tipo` enum('FIJO','MOVIL') NOT NULL,
  `marcacion` set('CPP','MPP','FIJO') NOT NULL,
  `carrier` varchar(100) NOT NULL,
  PRIMARY KEY (`area`,`serie`,`inicial`,`final`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
;";
$query_base = "INSERT IGNORE INTO ".$table_name."_extended (area,serie,inicial,final,tipo,marcacion,carrier) VALUES ";


if (!file_exists($filename))
	die("Archivo $filename no existe");
if (($handle1 = fopen($filename, "r")) === FALSE)
	die("No se pudo abrir $filename");
if (($handle2 = fopen($destfile, "w")) === FALSE)
	die("No se puede escribir $destfile");

fputs($handle2,$query_inicial);

$valores = array();
while (($data = fgetcsv($handle1, 1000, ",")) !== FALSE) {
	$area 	= $data[7];
	if (!is_numeric($area))
		continue;
	$serie 	= $data[8];
	$inicial = $data[9];
	$final = $data[10];
	$tipo = $data[12];
	$marcacion = $data[13];
	$carrier = $data[14];
	$valores[] = sprintf("(%s,%s,%s,%s,\"%s\",\"%s\",\"%s\")",$area,$serie,$inicial*1,$final,$tipo,$marcacion,$carrier);

	// Caso especial para los códigos de área de 2 dígitos
	if (strlen($area) == 2) {
		$newarea = $area.$serie;
		$area = substr($newarea,0,3);
		$serie = substr($newarea,3,3);
		$valores[] = sprintf("(%s,%s,%s,%s,\"%s\",\"%s\",\"%s\")",$area,$serie,$inicial*1,$final,$tipo,$marcacion,$carrier);
	}


	if (count($valores) >= $batch_size) {
		fputs($handle2,$query_base . implode(',',$valores) . ";\n");
		$valores = array();
	}
}
if (count($valores) > 0)
	fputs($handle2,$query_base . implode(',',$valores) . ";\n");

fclose($handle1);
fclose($handle2);