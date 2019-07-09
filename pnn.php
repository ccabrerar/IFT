<?php
/*
	Genera el archivo .sql a partir del CSV del Plan Nacional de Numeración.
	La versión más reciente del PNN se consigue desde https://sns.ift.org.mx:8081/sns-frontend/planes-numeracion/descarga-publica.xhtml
	
	Uso:		php pnn.php  (no recibe argumentos)
	Requiere:	La existencia del archivo pnn.csv en el mismo directorio
	
	Este script entrega el archivo .sql en el mismo directorio donde se invocó. No lo carga a la BD.
	
*/


$table_names = array('cofetel','ift');
$batch_size = 1000;
$filename 	= 'pnn.csv';
$min_filesize = 1024 * 10;


// Hacemos unos chequeos para ver que todo se vea bien
if (!file_exists($filename))
	die("Archivo $filename no existe");



// Revisa el tamaño del archivo pnn.csv. Si es demasiado pequeño, detiene el proceso para no generar un archivo SQL vacío.
//if (filesize($filename)

foreach ($table_names as $table_name) {		
	$destfile 	= "$table_name.sql";

	$query_inicial = "DROP TABLE IF EXISTS $table_name;
	 
CREATE TABLE `$table_name` (
	`area` mediumint(9) NOT NULL DEFAULT '0',
	`serie` mediumint(9) NOT NULL DEFAULT '0',
	`inicial` mediumint(9) NOT NULL DEFAULT '0',
	`final` mediumint(9) NOT NULL DEFAULT '0',
	`movil` tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (`area`,`serie`,`inicial`,`final`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
\n";
	$query_base = "INSERT IGNORE INTO $table_name (area,serie,inicial,final,movil) VALUES ";


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
		$movil = (($data[13] == 'CPP') ? '1' : '0');
		$valores[] = sprintf("(%s,%s,%s,%s,%s)",$area,$serie,$inicial*1,$final,$movil);
		
		// Caso especial para los códigos de área de 2 dígitos
		if (strlen($area) == 2) {
			$newarea = $area.$serie;
			$area = substr($newarea,0,3);
			$serie = substr($newarea,3,3);
			$valores[] = sprintf("(%s,%s,%s,%s,%s)",$area,$serie,$inicial*1,$final,$movil);
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
}
