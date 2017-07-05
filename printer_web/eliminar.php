<?php
/**
 * User: jrizzo
 * Date: 24/10/13
 * Time: 11:17
 */
$config = simplexml_load_file("configuracion.xml");
if (!empty($_POST))
{
	if (isset($_POST['delete']))
	{
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
		
		foreach ($_POST['ID'] as $i => $value)
		{
			mysqli_query($con,"UPDATE trabajos SET status=\"DEL\" WHERE nombre=".$value);
			$do = unlink($config->PATH_OUT[0].$value.".pdf");
			if($do=="1")
			{
				$correcto=true;
				$correctos=$correctos."<li type=\"disc\">".$value.".pdf</li>";
				$cantCorrectos++;
			}
			else
			{
				$incorrecto=true;
				$incorrectos=$incorrectos."<li type=\"disc\">".$value.".pdf</li>";
				$cantIncorrectos++;
			}
			$cant++;
		}
		mysqli_close($con);
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
	}
	if (isset($_POST['download']))
	{
		$fp = fopen('web.log', 'a');
		fwrite($fp, "---------------eliminar.php(download)-----------------------\n");
		fwrite($fp, "POST:: ".serialize($_POST)."\n");
		fwrite($fp,"Plantilla seleccionada:: ".$_POST['select']."\n");
		$temp_dir=$config->PATH_OUT[0];
		$zipname = time().".zip";
		$zip = new ZipArchive;
		$zip->open($zipname, ZipArchive::CREATE);
		$cant=0;
		//Preguntar si se seleccionó plantilla para crear el directorio o no
		if (!$_POST['select']==0)
		{
			//Crear un directorio temporal para agregar los archivos con las plantillas aplicadas
			$temp_dir = "./temp/".$zipname."/";
			fwrite($fp,"Carpeta temporal:: ".$temp_dir."\n");
			mkdir($temp_dir);
		}
		foreach ($_POST['ID'] as $i => $value)
		{
			//Armar el Path al archivo de la carpeta OUT a agregar al Zip
			$path = $config->PATH_OUT[0].$value.".pdf";
			fwrite($fp,"Procesando archivo:: ".$path."\n");
			//Preguntar si existe el archivo
			if(file_exists($path))
			{
				//Preguntar si se seleccionó plantilla
				if (!$_POST['select']==0)
				{
					//Armar comando para aplicar la plantilla al archivo
					$cmd = 'pdftk '.$path.' background '.$config->PATH_PREPRINTED[0].$_POST['select'].' output '.$temp_dir.$value.".pdf";
					fwrite($fp,$cmd."\n");
					fwrite($fp,"Sistema Operativo:".$config->OS[0]."\n");
					//Preguntar si está corriendo en ambiente Windows, para ejecutar el comando
					if ($config->OS[0] == "Windows")
					{
						pclose(popen("start /B ". $cmd, "r"));
					}
					else
					{
						exec($cmd . " > /dev/null &");
					}
					fwrite($fp,"Finished command\n");
					sleep(1);
					//Cambiar el Path del archivo a agregar
					$path=$temp_dir.$value.".pdf";
					fwrite($fp,$cmd."\n");
				}
				//Agregar al ZIP
				fwrite($fp,"Archivo a agregar al ZIP: ".$path."\n");
				$zip->addFromString(basename($path),  file_get_contents($path));
			}
			else
			{
				fwrite($fp,"Archivo no econtrado\n");
			}
		}
		//Cerrar el archivo Zip
		fwrite($fp,"::Cerrar archivo Zip::\n");
		$zip->close();

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"".$zipname."\"");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".filesize($zipname));
		ob_end_flush();
		@readfile($zipname);
		unlink($zipname);
		
		//Eliminar los archivos temporales cuando se selecciona plantilla
		if (!$_POST['select']==0)
		{
			if(@$handle = opendir($temp_dir))
			{ 
				while (false !== ($file = readdir($handle)))
				{ 
					if ($file != '.' && $file != '..')
					{ 
						fwrite($fp,"Eliminando archivo: ".$file."\n");
						unlink($temp_dir.$file);
					}
				} 
				closedir($handle);
				@rmdir($temp_dir);
			}
			else
			{ 
				 fwrite($fp,"ERROR: No se encuentra el directorio: ".$temp_dir."\n");
			}
		}
		fclose($fp);
	}
}
?>