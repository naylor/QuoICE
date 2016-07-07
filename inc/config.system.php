<?php

//=======================================//
//           SESSAO DO SISTEMA           //
//=======================================//
$TIMEOUT = 1440; // MINUTOS;
$_SESSION_NAME = "QuoICE";
session_name($_SESSION_NAME);
session_cache_expire($TIMEOUT);
if (!isset($_SESSION)) { session_start(); }
ini_get('register_globals');
ini_set('max_execution_time', 60);
#ini_set("memory_limit","-1");
setlocale(LC_MONETARY, 'en_US');

//=======================================//
//           ERROS DO SISTEMA            //
//=======================================//
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL );

//=======================================//
//           PATHs GLOBAIS           //
//=======================================//
// Localização do diretório raiz do sistema
define("URL", '/QuoICE', true);

// Localização do diretório raiz do sistema
define("LOCATION", '/QuoICE', true);

//PATH DO DOCUMENT ROOT
define("PATH", str_replace('inc', '', __DIR__), true);

// Diretório de Includes
define("INC", PATH.'/inc', true);

// Diretório de Bibliotecas
define("LIB", PATH.'/lib', true);

// Local do conector
define("MYSQL", INC.'/mysql.php', true);

// Arquivo de Funções
define("FUNCOES", INC.'/funcoes.inc.php', true);

// Diretório Controller
define("CONTROLLER", PATH.'/controller', true);

// Diretório de Imagens - Real Path
define("IMG", PATH.'/view/css/img/', true);

//=======================================//
//           VARIAVEIS VIEWS             //
//=======================================//
// Diretório de Views
define("VIEW", URL.'/view', true);

// Diretório de CSS
define("CSS", VIEW.'/css/', true);

// Diretório de JS
define("JS", VIEW.'/css/js', true);

// Diretório de Imagens
define("IMAGES", VIEW.'/css/img/', true);

// Diretório de Icones
define("ICONS",  VIEW.'/css/icons/', true);

?>
