<?php
include_once "../inc/config.inc.php";
include_once INC."/funcoes.inc.php";
require CONTROLLER . "/topCandidatos.class.php";

$mode = '';
$estado = '';
$sqlTOP = null;
$parTOP = null;

if (isset($_GET['mode']))
	$mode = $_GET['mode'];

if (isset($_GET['estado']))
	$estado = $_GET['estado'];

if (isset($_GET['politico']))
	$politico = $_GET['politico'];

$topCand = new topCandidatos();

$sqlTOP = ' WHERE t.tipo = :tipo ';
$parTOP = array(':tipo' => $mode);
if ($estado) {
	$parTOP = array(':estado' => $estado, ':tipo' => $mode);
	$sqlTOP = ' WHERE t.estado = :estado AND t.valor > 0 AND t.tipo = :tipo ';
}

$res = $topCand->getTOP10Candidatos($sqlTOP, $parTOP, 1, 10);
				
include("carousel.php");
?>
