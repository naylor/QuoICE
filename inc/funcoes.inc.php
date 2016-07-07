<?php

function money($n) {
	// first strip any formatting;
	$n = (0+str_replace(",","",$n));
	
	// is this a number?
	if(!is_numeric($n)) return false;
	
	// now filter it;
	if($n>1000000000000) {
		$t = round(($n/1000000000000),1);
		if ($t >= 2)
			return $t.' trilhões';
		else
			return $t.' trilhão';
	}
	else if($n>1000000000) {
		$t = round(($n/1000000000),1);
		if ($t >= 2)
			return $t.' bilhões';
		else
			return $t.' bilhão';
	}
	else if($n>1000000) {
		$t = round(($n/1000000),1);
		if ($t >= 2)
			return $t.' milhões';
		else
			return $t.' milhão';
	}
	else if($n>1000) return round(($n/1000),1).' mil';
	
	return number_format($n);
}

function moneyType($n) {
	// first strip any formatting;
	$n = (0+str_replace(",","",$n));
	
	// is this a number?
	if(!is_numeric($n)) return false;
	
	// now filter it;
	if($n>1000000000000) return ' trilhões';
	else if($n>1000000000) return ' bilhões';
	else if($n>1000000) return ' milhões';
	else if($n>1000) return ' mil';
	
	return '';
}

function abreviar($string, $tamanho) {
	if (strlen($string) >= $tamanho) {
		return substr($string, 0, $tamanho). "...";
	}
	else {
		return $string;
	}
}

function br2nl($string)
{
    return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
}

// VERIFICANDO O DIRETORIO TEMPORARIO
if (!function_exists('sys_get_temp_dir')) {

    function sys_get_temp_dir() {
        if (!empty($_ENV['TMP'])) {
            return realpath($_ENV['TMP']);
        }
        if (!empty($_ENV['TMPDIR'])) {
            return realpath($_ENV['TMPDIR']);
        }
        if (!empty($_ENV['TEMP'])) {
            return realpath($_ENV['TEMP']);
        }
        $tempfile = tempnam(__FILE__, '');
        if (file_exists($tempfile)) {
            unlink($tempfile);
            return realpath(dirname($tempfile));
        }
        return null;
    }

}

?>
