<?php
require '../inc/config.inc.php';
require CONTROLLER . '/candidato.class.php';
require INC.'/funcoes.inc.php';

$cpf = isset($_GET['cpf']) ? $_GET['cpf'] : '';
$grafico = isset($_GET['grafico']) ? $_GET['grafico'] : '';
$fala = isset($_GET['fala']) ? $_GET['fala'] : '';

if ($cpf) {
	$candidato = new candidato();
	$sqlAdicional = ' WHERE cpf = :cpf ';
	$params = array('cpf' => $cpf);
	$res = $candidato->getList($params, $sqlAdicional);

	// Caso o user tenha foto
	if ($res[0]['foto']) {
		header('Cache-Control: no-cache');
		header('Pragma: no-cache');
		header("Content-Type: image/jpeg");
		print $res[0]['foto'];
	} else { // User sem foto
		$arquivo = IMG . "/nopic.gif";
		$fp = fopen($arquivo, "rb");
		$pic = fread($fp, filesize($arquivo));
		fclose($fp);
		header('Content-type: image/png');
		print $pic;
	}
}

if ($grafico) {
	require_once (LIB."/phplot-6.2.0/phplot.php");
	
	$grafico = unserialize(urldecode($grafico));
	$label = $grafico['label'];
	
	$plot = new PHPlot($label['tx'],$label['ty']);
	$plot->SetDefaultTTFont(LIB.'/phplot-6.2.0/verdanab.ttf');
	$plot->SetPlotType('bars');
	$plot->SetUseTTF(true);
	$plot->SetBackgroundColor('#64A2DB');
	$plot->SetDataColors(array('red',	 'green', 'blue'));
	$plot->SetDataValueLabelColor('#FFFFFF');
	$plot->SetTextColor('#000000');
	$plot->SetTitleColor('#000000');

	$plot->SetFontTTF('title',   LIB.'/phplot-6.2.0/verdanab.ttf', 12);
	$plot->SetFontTTF('x_title', LIB.'/phplot-6.2.0/verdanab.ttf', 12);
	$plot->SetFontTTF('y_title', LIB.'/phplot-6.2.0/verdanab.ttf', 12);
	$plot->SetFontTTF('x_label', LIB.'/phplot-6.2.0/verdanab.ttf', 10);
	$plot->SetFontTTF('y_label', LIB.'/phplot-6.2.0/verdanab.ttf', 10);

	$plot->SetDataValues($grafico['dados']);
	$plot->SetXTickLabelPos('none');
	
	$plot->SetXTickPos('none');
	$plot->SetTitle(br2nl($label['title']));
	$plot->SetXTitle($label['x']);
	$plot->SetYTitle($label['y']);

	$plot->DrawGraph();	
}

if ($fala) {
	$tempo = isset($_GET['tempo']) ? $_GET['tempo'] : '';

	$temp = sys_get_temp_dir();
	$fala = $grafico = unserialize(urldecode($fala));
	$x='/usr/bin/espeak -v pt -s 130 -a 100 -g 1 "'.$fala.'" -w '.$temp.'/'.$tempo.'.wav ';
	exec("$x");
	$fp = fopen($temp.'/'.$tempo.'.wav', "rb");
	$pic = fread($fp, filesize($temp.'/'.$tempo.'.wav'));
	fclose($fp);
	header('Content-type: audio/mpeg');
	print $pic;
}
?>
