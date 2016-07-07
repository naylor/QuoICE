<?php
include_once "../inc/config.inc.php";
include_once INC."/funcoes.inc.php";
require CONTROLLER . "/candReceitas.class.php";

$mode = '';
$estado = '';
$sqlTOP = null;
$parTOP = null;


if (isset($_GET['politico']))
	$politico = $_GET['politico'];

$receitas = new candReceitas();

$sql = ' AND c.cpf = :cpf ';
$params = array(':cpf' => $politico);

$res = $receitas->getCandReceitas($sql, $params, 1, 10);
				
include("carousel.php");
?>
