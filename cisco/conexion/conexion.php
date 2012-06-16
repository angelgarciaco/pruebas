<?
//====================================
// CONFIGURACION EDITADA
//====================================
@session_start(); 
$_SESSION["usuarioBD"] = "";
$_SESSION["claveBD"] = "";
// Establecen la conexion con la BD::
$_SESSION["basededatos"] = "cisco";
$_SESSION["servidor"] = "localhost"; //200.93.xxx.xxx
$_SESSION["root"] = "root";
$_SESSION["claveBD"]="1234";

mysql_connect($_SESSION["servidor"],$_SESSION["root"],$_SESSION["claveBD"]);
mysql_select_db($_SESSION["basededatos"]);
//===================================
?>