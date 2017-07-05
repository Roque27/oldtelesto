<?php
/**
 * User: jrizzo
 * Date: 24/10/13
 * Time: 11:17
 */
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Admin de Trabajos de impresión</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">

    <!-- Custom styles for this template -->
    <link rel="stylesheet" type="text/css" href="css/DT_bootstrap.css">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    <style type="text/css"></style>
</head>
<body style="">
<div class="container">
    <div class="page-header" id="banner">
        <h2>Trabajos de impresión</h2>
    </div>
    <div class="bs-docs-section">
        <div class="col-lg-12">
            <div class="col-lg-6">
                <form id="form" method="post" action="eliminar.php" class="bs-example form-horizontal">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <div class="form-group" style="text-align: right;">
                                <button class="btn btn-primary" type="submit" value="download" name="download">Descargar</button>
                                <button class="btn btn-danger" type="submit" value="delete" name="delete">Eliminar</button>
                            </div>
                            <label for="select" class="col-lg-2 control-label" title="Seleccione una plantilla para que al descargar el archivo PDF se visualice como fondo del mismo.">Utilizar la plantilla: </label>
                            <div class="col-lg-10">
                                <select class="form-control" id="select" name="select" title="Seleccione una plantilla para que al descargar el archivo PDF se visualice como fondo del mismo.">
                                    <?php
                                    $config = simplexml_load_file("configuracion.xml");
                                    if(@$handle = opendir($config->PATH_PREPRINTED[0]))
                                    {
                                        echo '<option id="0" name="0" value="0">--Seleccione una opción--</option>';
                                        while (false !== ($file = readdir($handle)))
                                        {
                                            if ($file != '.' && $file != '..')
                                            {
                                                echo '<option id="'.$file.' value="'.$file.'">'.$file.'</option>';
                                            }
                                        }
                                        closedir($handle);
                                    }
                                    else
                                    {
                                        echo 'ERROR: No se ha localizado el directorio'.$config->PATH_PREPRINTED[0];
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <br>
                    <table id="trabajos" cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th>Título</th>
                            <th>Servidor</th>
                            <th>Propietario</th>
                            <th>Fecha y hora</th>
                            <th>Páginas</th>
                            <th>Tamaño</th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        //Recuperar archivo de configuración
                        $config = simplexml_load_file("configuracion.xml");
                        // Create connection
                        $con=mysqli_connect($config->HOST[0],$config->USER[0],$config->PASS[0],$config->DB[0]);
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
                            if (file_exists($config->PATH_OUT[0].$job['nombre'].".pdf")) {
                                echo "<td style=\"text-align:center;\"><img onmouseover=\"bigImg(this)\" onmouseout=\"normalImg(this)\" id=\"".$job['nombre']."\" name=\"".$config->PATH_OUT[0].$job['nombre'].".pdf\" src=\"images/doc_pdf.png\" alt=\"Descargar\" class=\"descargar\" style=\"text-align:center;\"/></td>";
                                echo "<td style=\"text-align:center;\" class=\"center\"><input type=\"checkbox\" value=\"".$job['nombre']."\" name=\"ID[".$job['nombre']."]\" /></td>";
                            } else {
                                echo "<td style=\"text-align:center;\"><img src=\"images/link_break.png\" alt=\"Broken Link\"/></td>";
                                echo "<td style=\"text-align:center;\" class=\"center\"><input disabled=\"disabled\" type=\"checkbox\" value=\"".$job['nombre']."\" name=\"ID[".$job['nombre']."]\" /></td>";
                            }

                            echo "</tr>";
                        }
                        mysqli_close($con);
                        ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="js/jquery-1.8.0.min.js"></script>
<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="js/DT_bootstrap.js"></script>
<script type="text/javascript" src="js/dataTables.dataSourcePlugins.js"></script>
<script type="text/javascript" language="JavaScript">
    function bigImg(x)
    {
        x.style.height="18px";
        x.style.width="18px";
    }

    function normalImg(x)
    {
        x.style.height="";
        x.style.width="";
    }

    $(document).ready(function() {
        $('img.descargar').click(function() {

            var x=document.getElementById("select").selectedIndex;
            var y=document.getElementById("select").options;
            if(y[x].index==0)
                window.open(this.name);
            else
            {
                $.post( "utils.php", { select:y[x].text, archivo:this.name, id:this.id } )
                    .done(function( data ) {
                        window.open(data);
                    });
            }
        });
    });

    $(document).ready(function() {
        $('#trabajos').dataTable( {
            "aoColumns": [
                null,
                null,
                null,
                {"sType": "date-euro"},
                null,
                null,
                { "bSortable": false },
                { "bSortable": false }
            ],
            "aaSorting": [[ 3, "desc" ]],
            "sDom": "<'row'<'span6'l><'span6'f>r>t<'row'<'span6'i><'span6'p>>",
            "sPaginationType": "bootstrap",
            "aLengthMenu": [[15, 50, -1], [15, 50, "All"]],
            "iDisplayLength": 15,
            "oLanguage": {
                "oPaginate": {
                    "sPrevious": "Atrás",
                    "sNext": "Adelante"
                },
                "sSearch": "Buscar",
                "sLengthMenu": "_MENU_ trabajos por página",
                "sZeroRecords": "Registro no encontrado, disculpe",
                "sInfo": "Mostrando _START_ a _END_ de _TOTAL_ trabajos",
                "sInfoEmpty": "Mostrando 0 a 0 de 0 trabajos",
                "sInfoFiltered": "(Filtrados _MAX_ trabajos)"
            }
        });
    });

    $( "form" ).submit(function( event )
    {
        checkboxes=document.getElementsByTagName('input'); //obtenemos todos los controles del tipo Input
        for(i=0;i<checkboxes.length;i++) //recoremos todos los controles
        {
            if(checkboxes[i].type == "checkbox" && checkboxes[i].checked==true)
            {
                var sData = $('input', oTable.fnGetNodes()).serialize();
                return false;
            }
        }
        alert("Debe seleccionar al menos un trabajo a descargar o eliminar.");
        event.preventDefault();
    });
</script>
</body>
</html>