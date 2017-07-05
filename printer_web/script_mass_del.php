<?php
/**
 * User: jrizzo
 * Date: 27/02/14
 * Time: 11:17
 */
$config = simplexml_load_file("configuracion.xml");

$cant=0;
$correcto=true;
$correctos="";
$cantCorrectos=0;

$incorrecto=false;
$incorrectos="";
$cantIncorrectos=0;

// Create connection
$con=mysqli_connect($config->HOST[0],$config->USER[0],$config->PASS[0],$config->DB[0]);
mysqli_query($con,"SET NAMES 'utf8'");
// Check connection
if (mysqli_connect_errno($con))
{
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$fecha = date('Y-m-j');
$fecha_hasta = strtotime ( '-'.$config->ANTIGUEDAD_HASTA[0].' day' , strtotime ( $fecha ) ) ;
$fecha_hasta = date ( 'Y-m-j' , $fecha_hasta );

$jobs_db = mysqli_query($con,"SELECT nombre FROM trabajos WHERE status='SHOW' AND fecha<'".$fecha_hasta."'");

while($job = mysqli_fetch_array($jobs_db))
{
	mysqli_query($con,"UPDATE trabajos SET status=\"DEL\" WHERE nombre=".$job['nombre']);
	$do = unlink($config->PATH_OUT[0].$job['nombre'].".pdf");

	if($do=="1")
	{
		$correcto=true;
		$correctos=$correctos."\n".$job['nombre'];
		$cantCorrectos++;
	}
	else
	{
		$incorrecto=true;
		$incorrectos=$incorrectos."\n".$job['nombre'];
		$cantIncorrectos++;
	}
	$cant++;
}
echo "Eliminados correctamente:".$cantCorrectos."\n";
echo $correctos;
echo "\nEliminados con error:".$cantIncorrectos."\n";
echo $incorrectos;
mysqli_close($con);
?>