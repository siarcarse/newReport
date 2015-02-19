<?php
session_start();
if(!isset($_SESSION['Username'])) { header("location: ../../../login.php?error=hack"); header('Content-Type: text/html; charset=latin1');  }
?>

<?
include("libs/db.class.php");
$template = new DB("template", "id");

//echo $_REQUEST['obj'];

//$id = $_REQUEST['obj'];
//echo $id;
$calendar = $_REQUEST['calendar'];

$db = NEW DB();
$db2 = NEW DB();
$sql = "SELECT name 
FROM city 
WHERE id IN
(SELECT city FROM commune WHERE id IN
(SELECT commune FROM branch WHERE id IN
(SELECT branch FROM room WHERE id IN
(SELECT room FROM calendar WHERE id=$calendar))))";
$city = $db2->doSql($sql);
$ciudad = $city['name'];
$sql = "SELECT calendar.date_c AS date, (patient.name || ' ' || patient.lastname) AS patient, patient.rut AS rut, (ref_doctor.name || ' ' || ref_doctor.lastname) AS ref_doctor, patient.birthdate as birthdate
FROM calendar 
LEFT JOIN patient ON patient.id=calendar.patient
LEFT JOIN ref_doctor ON ref_doctor.id=calendar.ref_doctor
WHERE calendar.id=$calendar";
$data = $db->doSql($sql);
$patient = $data['patient'];
$dat = split('-',$data['date']);
$date = $dat[2].'/'.$dat[1].'/'.$dat[0];
$age= split('-',$data['birthdate']);
$anio_dif_age = date("Y") - $age[0];
$mes_dif_age = date("m") - $age[1];
$dia_dif_age = date("d") - $age[2];
if ($dia_dif_age < 0 || $mes_dif_age < 0)
$anio_dif_age--;
$anio_dif_age;
//$date->format('d-m-Y');
$rut = $data['rut'];
$ref_doctor = $data['ref_doctor'];
$row = $template->doSql("SELECT report FROM template WHERE id=".$_REQUEST['id']);
$report = $row['report'];
$report = str_replace('%patient%', $patient, $report);
$report = str_replace('%date%', $date, $report);
$report = str_replace('%city%', $ciudad, $report);
$report = str_replace('%rut%', $rut, $report);
$report = str_replace('%ref_doctor%', $ref_doctor, $report);
$report = str_replace('%birthdate%', $anio_dif_age, $report);

echo $report;
?>
