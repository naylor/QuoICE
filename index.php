<?php
include_once "inc/config.inc.php";
include_once INC."/funcoes.inc.php";

$mode = '';

if (isset($_GET['mode']))
	$mode = $_GET['mode'];

//DEFAULT DO SISTEMA DE FALA
$_SESSION["sound"] = 'OFF';

?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>QuoICE</title>

		<!-- BOOTSTRAP STYLES-->
		<link href="<?= CSS ?>/bootstrap.css" rel="stylesheet" />
		<!-- FONTAWESOME STYLES-->
		<link href="<?= CSS ?>/font-awesome.css" rel="stylesheet" />
		   <!--CUSTOM BASIC STYLES-->
		<link href="<?= CSS ?>/basic.css" rel="stylesheet" />
		<!--CUSTOM MAIN STYLES-->
		<link href="<?= CSS ?>/custom.css" rel="stylesheet" />

		<!-- JS PADRAO -->
		<script src="<?= JS ?>/jquery-2.1.3.min.js"></script>

        <script type="text/javascript">
            $(document).ajaxStart(function () {
                $('#loading').show();
            }).ajaxStop(function () {
                $('#loading').hide();
            });
                       
			$(document).ready(function(){
				$('button').click(function(){
					$('button').removeClass('clicked');
					$(this).addClass('clicked');
				});
				$('.main-box').click(function(){
					$('button, .main-box').removeClass('clicked');
					$(this).addClass('clicked');
				});
			});
        </script>
	</head>
<body>
	<div id="loading" class="loading"></div>
    <div id="wrapper">
			<?php require('view/header.php'); ?>
        <!-- /. lista  -->
        <nav class="navbar-default navbar-side" id="menu" role="navigation"></nav>
        <!-- /. NAV SIDE  -->
        <div id="page-wrapper">
			<div id="reviews" class="carousel slide" data-ride="carousel"></div>
        </div>
    </div>
    <div id="footer-sec">
        &copy; 2016 SSC5723 - Sistemas Operacionais - Jó Ueyama | Design By: Naylor, Silvia e Vitor
    </div>
</body>
</html>
