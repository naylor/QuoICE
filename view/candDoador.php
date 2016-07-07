<?php
include_once "../inc/config.inc.php";
include_once INC."/funcoes.inc.php";
require CONTROLLER . "/candDoador.class.php";

$sqlTOP = null;
$parTOP = null;

$doador = new candDoador();

if (isset($_GET['politico']))
	$politico = $_GET['politico'];
	
$sqlTOP = ' AND c.cpf = :cpf ';
$parTOP = array(':cpf' => $politico);

$res = $doador->getTOP10Doadores($sqlTOP, $parTOP, 1, 10);

if ($res) {
	$i=0;
	foreach ($res as $r) {
		$menu[$r['codigo']] = urlencode($r['nome']);
		if ($i > 0)
			unset($res[$i]);
		$i++;
	}
}

include("carousel.php");
?>
