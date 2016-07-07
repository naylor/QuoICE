<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class topBens extends Generic {

    public function __construct() {}

    public function getGraficoBens($cpf) {
        $bd = new database();
        
        $sql = "SELECT ano, (valor/1000000) as valor
					FROM topBens
					WHERE cpf = :cpf GROUP BY ano, valor";
					
		$params = array(':cpf' => $cpf);
        $res = $bd->selectDB($sql, $params);

		$newRes='';
		
        if ($res) {
			$label['x'] = 'Anos';
			$label['y'] = ucwords(moneyType($res[0]['valor'] * 1000000));
			$label['title'] = 'Bens Declarados';
			$label['tx'] = 320;
			$label['ty'] = 290;
			$newRes['label'] = $label;
			$newRes['dados'] = $res;
			return $newRes;
        } else {
            return false;
        }
    }
}
