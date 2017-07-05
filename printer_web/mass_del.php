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
		$correctos=$correctos."<li type=\"disc\">".$job['nombre'].".pdf</li>";
		$cantCorrectos++;
	}
	else
	{
		$incorrecto=true;
		$incorrectos=$incorrectos."<li type=\"disc\">".$job['nombre'].".pdf</li>";
		$cantIncorrectos++;
	}
	$cant++;
}

echo "<html>";
echo "<head>";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">";
echo "<title>Resultado eliminar trabajos de impresión</title>";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"css/bootstrap.min.css\">";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"css/DT_bootstrap.css\">";
echo "</head>";
echo "<body>";
echo "<div class=\"container\">";
	echo "<div id=\"titulo\" class=\"page-header\">";
		echo "<h1>Resultado eliminar trabajos de impresión</h1>";
	echo "</div>";
	echo "<div class\"col-lg-4\">";
if ($correcto)
{
	echo "<div class=\"alert alert-dismissable alert-success\">";
		echo "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button>";
		echo "<strong>Correcto!</strong> Archivos eliminados";
		echo "<ul>";
			echo $correctos;
		echo "</ul>";
		echo "</div>";
	echo "<h4>Total de archivos eliminados correctamente: ".$cantCorrectos."</h4>";
}

if ($incorrecto)
{
	echo "<div class=\"alert alert-dismissable alert-danger\">";
		echo "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button>";
		echo "<strong>Error!</strong> Archivos que no se pudieron eliminar";
		echo "<ul>";
			echo $incorrectos;
		echo "</ul>";
	echo "</div>";							
	echo "<h4>Total de registros con problemas al intentar eliminar archivos: ".$cantIncorrectos."</h4>";
}
echo "<h4>Total de registros procesados: ".$cant."</h4>";
echo "</div>";
echo "<div style=\"text-align:right; padding-bottom:1em;\">";
echo "<input type=\"button\" class=\"btn btn-primary\" value=\"Volver\" onClick=\"window.location='/printer_web/admin.php'\">";
echo "</div>";
echo "</div>";
echo "</body>";
echo "</html>";

mysqli_close($con);
?>