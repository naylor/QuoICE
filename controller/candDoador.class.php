<?php
if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class candDoador extends Generic {

    public function __construct() {}

    public function getGrafico($res) {

		$newRes='';
		$i=0;
		foreach($res as $r) {
			unset($res[$i]['nome']);
			unset($res[$i]['ano']);
			unset($res[$i]['total']);
			$res[$i]['codigo'] = $i+1;
			$res[$i]['valor'] /= 1000000;
			$i++;
		}

		if ($res) {
			$label['x'] = 'Principais Doadores';
			$label['y'] = ucwords(moneyType($res[0]['valor'] * 1000000));
			$label['title'] = 'Doação para Campanha Eleitoral';
			$label['tx'] = 720;
			$label['ty'] = 280;
			$newRes['label'] = $label;
			$newRes['dados'] = $res;
			return $newRes;
        } else {
            return false;
        }
    }
     
    public function getTOP10Doadores($sqlAdicional = null, $params = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

		$nav = '';
		
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina";
        
        $sql = "SELECT t.doador as codigo, SUM(t.valor) as valor
					FROM candReceitas t, candidato c 
					WHERE t.id = c.id 
					AND t.ano = c.ano ";
        $sql .= $sqlAdicional;
        $sql .= " GROUP BY t.doador ";
        $sql .= " ORDER BY SUM(t.valor) DESC ";
        $sql .= $nav;
        
        $res = $bd->selectDB($sql, $params);
		
		$i=0;
		
        if ($res) {
			if (!class_exists('doador'))
				require_once CONTROLLER . '/doador.class.php';
			
			$res[0]['total'] = 0;
			
			$doador = new doador();
			foreach ($res as $r) {

				$sqlAdicional1 = " WHERE d.codigo = :codigo ";
				$params1 = array(':codigo' => $r['codigo']);
				$res1 = $doador->getDoadores($params1, $sqlAdicional1, 1, 1);
				if ($res1) {
					$res[$i] += $res1[0];
					$res[0]['total'] += $r['valor'];
				} else {
					unset($res[$i]);
				}
				$i++;
			}
			
			$l = "<div class=\"user-valor-td\">";
			$l .= "<h1>Total da Doação</h1><h2>".money($res[0]['total'])."</h2>";
			$l .= "</div>";
			$l .= "<div class=\"user-nome-td\">";
			$l .= "<h5 class=\"pull-right\"><strong class=\"c-black\">Doações de Campanha para o Candidato Pesquisado</strong>";
			$l .= "</div>";
			$l .= "<div class=\"user-grf\">";
			$l .= "<img src=\"".VIEW."/file.php?grafico=".urlencode(serialize($this->getGrafico($res)))."&time=".time()."\" />";
			$l .= "</div>";

			$res[0]['layout'] = $l;
			$l = '';

			$fala = str_replace('.', ',', money($res[0]['total']));
			$fala = "Aqui estão os maiores doadores de campanha, no total de $fala de reais";
			$fala = urlencode(serialize($fala));
			$res[0]['fala'] = $fala;
				
			return $res;
        } else {
            return false;
        }
    }
}
