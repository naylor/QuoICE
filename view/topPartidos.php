<?php
include_once "../inc/config.inc.php";
include_once INC."/funcoes.inc.php";
require CONTROLLER . "/topPartidos.class.php";

$mode = '';
$estado = '';
$sqlTOP = null;
$parTOP = null;

if (isset($_GET['mode']))
	$mode = $_GET['mode'];

if (isset($_GET['estado']))
	$estado = $_GET['estado'];

$topPartidos = new topPartidos();

$sqlTOP = ' WHERE t.tipo = :tipo ';
$parTOP = array(':tipo' => $mode);
if ($estado) {
	$parTOP = array(':estado' => $estado, ':tipo' => $mode);
	$sqlTOP = ' WHERE t.estado = :estado AND t.valor > 0 AND t.tipo = :tipo ';
}
$res = $topPartidos->getTOP10Partidos($sqlTOP, $parTOP, 1, 10);
				
include("carousel.php");

?>
