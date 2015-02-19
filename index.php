<?php
session_start();
$users = $_SESSION['UserId']; 
$calendar = $_REQUEST['calendar']; 
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Modulo de informes</title>
    <meta charset="utf-8">
    <!-- Latest compiled and minified CSS & JS -->
    <link rel="stylesheet" media="screen" href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
    <script src="//code.jquery.com/jquery.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
    <script src="js/ckeditor/ckeditor.js"></script>
    <script src="js/ckeditor/config.js"></script>
    <style type="text/css">
    .label {
        color: black;
        font-size: 12px;
    }
    .divider {
        height: 40px;
        margin: 0 9px;
        border-bottom: 1px solid #d6d6d6;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Modulo de Informes</h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                <legend>Paciente: </legend>
                            </div>
                            <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                                <span class="label">Fecha</span>
                                <br>
                                <input type="date" name="" id="date" class="form-control" value="" required="required" title="">
                            </div>
                            <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                                <span class="label">Nombre Examen/Informe</span>
                                <br>
                                <input type="text" name="" id="name" class="form-control" value="" required="required" pattern="" title="" placeholder="Nombre">
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                <div class="divider"></div>
                            </div>
                            <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                                <span class="label">Médico Informante</span>
                                <br>
                                <select name="" id="doctor" class="form-control">
                                    <option value="">-- Seleccione Doctor --</option>
                                </select>
                            </div>
                            <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                                <span class="label">Plantillas</span>
                                <br>
                                <select name="" id="template" class="form-control">
                                    <option value="">-- Seleccione Plkantilla --</option>
                                </select>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                <span class="label">Informe</span>
                                <br>
                                <textarea class="ckeditor" id="report" name="report"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</body>
<script type="text/javascript">
var users_session = '<?php echo $users ?>';
var calendar = '<?php echo $calendar ?>';
function loadDoctor() {
    $.ajax({
            url: 'getDoctor.php',
            dataType: 'json',
        })
        .done(function(data) {
            var html = '';
            html += '<option value="">-- Seleccione Doctor --</option>';
            for (var i = 0; i < data.length; i++) {
                html += '<option value="' + data[i].id + '">' + data[i].name + '</option>';
            }
            $('#doctor').html(html);
        });

}
function loadTemplate(users) {
    var url;
    if(users !== 0) url = 'listTemplate.php?users='+users;
    else url = 'listTemplate.php';
    $.ajax({
            url: url,
            dataType: 'json',
        })
        .done(function(data) {
            var html = '';
            html += '<option value="">-- Seleccione Plantilla --</option>';
            if(data.length != 1) {
                for (var i = 0; i < data.length; i++) {
                    html += '<option value="' + data[i].id + '">' + data[i].name + '</option>';
                }
            }
            $('#template').html(html);
        });

}
function createReport(original) {
    str = original;
    setEditorValue('report', str);
}
function setEditorValue( instanceName, text )
{
    var oEditor = CKEDITOR.instances[instanceName] ;
    oEditor.setData( text ) ;
}
$(document).ready(function() {
    loadDoctor();
    loadTemplate(0);
    $('#doctor').change(function(event) {
        loadTemplate($(this).val());
    });
    $('#template').change(function(event) {
        $.post('findTemplate.php', {id:$(this).val(), calendar:calendar}, function(data, textStatus, xhr) {
            //console.log(data);
            createReport(data);
        });
    });
});
</script>

</html>
