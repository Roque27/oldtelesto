<?php
/**
 * User: jrizzo
 * Date: 24/10/13
 * Time: 09:54
 */
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Trabajos de impresi&oacute;n</title>
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="css/DT_bootstrap.css">
	<!--?php
		$url=$_SERVER['REQUEST_URI'];
		header("Refresh: 30; URL=$url"); 
	?-->
</head>
<body>
	<div class="container">
		<table>
			<tr>
				<td>
					<div id="titulo" class="page-header" >
						<h2>Trabajos de impresi&oacute;n</h2>
					</div>
				</td>
				<td style="text-align:right; padding-bottom:1em;padding-left:30px;width:30px;">
					<div>
						<img style="width:30;" onclick="window.location.reload()" src="./images/refresh.png" alt="Refrescar"/>			
					</div>		
				</td>
			</tr>
		</table>
		<div class="container" style="margin-top: 10px">
		<table id="trabajos" cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>T&iacute;tulo</th>
					<th>Servidor</th>
					<th>Propietario</th>
					<th>Fecha y hora</th>
					<th>P&aacute;ginas</th>
					<th>Tama&ntilde;o</th>
					<th>Descagar</th>
				</tr>
			</thead>
			<tbody>
			<?php
			// Create connection
			$con=mysqli_connect("localhost","root","","pdf_printer_db");
			mysqli_query($con,"SET NAMES 'utf8'");
			// Check connection
			if (mysqli_connect_errno($con))
			{
				echo "Failed to connect to MySQL: " . mysqli_connect_error();
			}
			
			$jobs_db = mysqli_query($con,"SELECT * FROM trabajos WHERE status=\"SHOW\"");
			
			while($job = mysqli_fetch_array($jobs_db))
			{
				echo "<tr>";
				echo "<td>".$job['title']."</td>";
				echo "<td>".$job['local_server']."</td>";
				echo "<td>".$job['owner']."</td>";
				echo "<td>".date("d/m/Y H:i:s", mktime(substr($job['nombre'],8,2), substr($job['nombre'],10,2), substr($job['nombre'],12,2), substr($job['nombre'],4,2), substr($job['nombre'],6,2), substr($job['nombre'],0,4)))."</td>";
				$pages = ($job['pages'] == "") ? '&nbsp;' : $job['pages'];
				echo "<td>".$pages."</td>";
				$size_pdf = ($job['size_pdf'] == "") ? '&nbsp;' : round($job['size_pdf']/1024,2)." KB";
				echo "<td>".$size_pdf."</td>";
				if (file_exists("./out/".$job['nombre'].".pdf")) {
					echo "<td style=\"text-align:center;\"><a href=\"./out/".$job['nombre'].".pdf\" target=\"_blank\"><img src=\"images/led-icons/doc_pdf.png\" alt=\"Descargar\"/></a></td>";
				} else {
					echo "<td style=\"text-align:center;\"><img src=\"images/led-icons/link_break.png\" alt=\"Broken Link\"/></td>";
				}
				echo "</tr>";
			}
			mysqli_close($con);
			?>
			</tbody>
		</table>
		</div>
		<footer>
			<p align="right">
				<img src="images/logo.gif" alt="Litoral Gas S.A."/>
			</p>
		</footer>
		<script type="text/javascript" src="js/jquery-1.8.0.min.js"></script>
		<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" src="js/DT_bootstrap.js"></script>
		<script type="text/javascript" src="js/dataTables.dataSourcePlugins.js"></script>
		<script type="text/javascript" language="JavaScript">
		$(document).ready(function() 
		{
			$('#trabajos').dataTable( 
			{
				"aoColumns": [
						null,
						null,
						null,
						{"sType": "date-euro"},
						null,
						null,
						{ "bSortable": false }
					],
				"aaSorting": [[ 3, "desc" ]],
				"sDom": "<'row'<'span6'l><'span6'f>r>t<'row'<'span6'i><'span6'p>>",
				"sPaginationType": "bootstrap",
				"aLengthMenu": [[15, 50, -1], [15, 50, "All"]],
				"iDisplayLength": 15,
				"oLanguage": 
				{
					"oPaginate": 
					{
						"sPrevious": "Atr&aacutes",
						"sNext": "Adelante",
						
					},
					"sSearch": "Buscar",
					"sLengthMenu": "_MENU_ trabajos por p&aacutegina",
					"sZeroRecords": "Registro no encontrado, disculpe",
					"sInfo": "Mostrando _START_ a _END_ de _TOTAL_ trabajos",
					"sInfoEmpty": "Mostrando 0 a 0 de 0 trabajos",
					"sInfoFiltered": "(Filtrados _MAX_ trabajos)"
				}
			} );
		} );
		</script>
	</div>
</body>
</html>