<?php
include_once "../inc/config.inc.php";
include_once INC."/funcoes.inc.php";
require CONTROLLER . "/topRendas.class.php";

$estado = '';
$politico = '';
$sqlTOP = null;
$parTOP = null;

if (isset($_GET['estado']))
	$estado = $_GET['estado'];

if (isset($_GET['politico']))
	$politico = $_GET['politico'];

$topBens = new topRendas();

$sqlTOP = ' ORDER BY t.valor DESC ';
if ($estado) {
	$parTOP = array(':estado' => $estado);
	$sqlTOP = ' WHERE t.estado = :estado AND t.valor > 0 ORDER BY t.valor DESC ';
}

if ($politico) {
	$parTOP = array(':cpf' => $politico);
	$sqlTOP = ' WHERE t.cpf = :cpf ';
}

$res = $topBens->getTOP10Rendas($sqlTOP, $parTOP, 1, 10);

include("carousel.php");
?>
