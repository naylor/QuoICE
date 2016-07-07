<?php
include_once "../inc/config.inc.php";
include_once INC."/funcoes.inc.php";
require CONTROLLER . "/topDoador.class.php";

$estado = '';
$sqlTOP = null;
$parTOP = null;

if (isset($_GET['estado']))
	$estado = $_GET['estado'];

$topDoador = new topDoador();

$sqlTOP = '';
$parTOP = array();
if ($estado) {
	$parTOP = array(':estado' => $estado);
	$sqlTOP = ' WHERE t.estado = :estado AND t.valor > 0 ';
}
$res = $topDoador->getTOP10Doadores($sqlTOP, $parTOP, 1, 10);

include("carousel.php");
?>
