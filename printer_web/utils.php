<?php
$config = simplexml_load_file("configuracion.xml");
$fp = fopen('web.log', 'a');
fwrite($fp, "----------------utils.php---------------------------\n");
fwrite($fp, "Plantilla seleccionada:: ".$_POST['select']."\n");
fwrite($fp, "archivo:: ".$_POST['archivo']."\n");
fwrite($fp, "id:: ".$_POST['id']."\n");

//$temp_dir = "/opt/lampp/htdocs/printer_web/temp/";

//Armar comando para aplicar la plantilla al archivo
$cmd = $config->PATH_OUT_ORIGINAL[0].$_POST['id'].'.pdf background '.$config->PATH_PREPRINTED[0].$_POST['select'].' output '.$config->TEMP_DIR[0].$_POST['id'].".pdf";
fwrite($fp,$cmd."\n");
fwrite($fp,"Sistema Operativo:".$config->OS[0]."\n");
//Preguntar si está corriendo en ambiente Windows, para ejecutar el comando
if ($config->OS[0] == "Windows")
{
	fwrite($fp,"start /B pdftk ".$cmd."\n");
	pclose(popen("start /B pdftk ". $cmd, "r"));
}
else
{
	//fwrite($fp,$cmd . " > /dev/null &\n");
	$salida = shell_exec('sh exec.sh '.$cmd);
	//$salida = shell_exec("pdftk");
	fwrite($fp,"Salida: ".$salida."\n");

}
sleep(1);
fwrite($fp,"Finished command\n");
echo './temp/'.$_POST['id'].".pdf";
//Hacer que ser borren los archivos del temporal
//unlink($temp_dir.$_POST['id'].".pdf");
fclose($fp);
?>