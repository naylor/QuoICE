<?php
include_once "../inc/config.inc.php";
include_once INC."/funcoes.inc.php";
require CONTROLLER . "/candDespesas.class.php";

$mode = '';
$estado = '';
$sqlTOP = null;
$parTOP = null;


if (isset($_GET['politico']))
	$politico = $_GET['politico'];

$despesas = new candDespesas();

$sql = ' AND c.cpf = :cpf ';
$params = array(':cpf' => $politico);

$res = $despesas->getCandDespesas($sql, $params, 1, 10);
				
include("carousel.php");
?>
