<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class topDoador extends Generic {

    public function __construct() {}

    public function getGrafico($codigo, $p) {
        $bd = new database();
        
        $s = '';
        if (isset($p[':estado']))
			$s = ' AND c.estado = :estado ';

        $sql = "SELECT a.partido, a.sigla, SUM(valor) as valor FROM
			(
				SELECT c.partido, p.sigla,
					(SUM(c.valor)/1000000) as valor
					FROM partReceitas c, partido p 
					WHERE c.partido = p.codigo 
					AND c.ano = '2006'
					$s
					AND c.doador = :codigo
					GROUP BY 1, 2
			UNION ALL
					SELECT d.partido, p.sigla,
					(SUM(c.valor)/1000000) as valor
					FROM candReceitas c, candidato d, partido p 
					WHERE c.id = d.id 
					AND c.ano = d.ano
					AND c.estado = d.estado
					AND d.partido = p.codigo
					AND c.ano = '2006'
					AND c.doador = :codigo
					$s
					GROUP BY 1, 2
			) a
			GROUP BY a.partido, a.sigla
			ORDER BY SUM(valor) DESC
			LIMIT 10";
        
        if (isset($p[':estado']))
			$params = array(':codigo' => $codigo, ':estado' => $p[':estado']);
		else
			$params = array(':codigo' => $codigo);
		
        $res = $bd->selectDB($sql, $params);

		$resRem='';

		foreach($res as $r){
			unset($r['partido']);
			$resRem[] = $r;
		}

		$newRes='';
        if ($resRem) {
			$label['x'] = 'Principais Partidos';
			$label['y'] = ucwords(moneyType($resRem[0]['valor'] * 1000000));
			$label['title'] = 'Doação para Campanha Eleitoral';
			$label['tx'] = 720;
			$label['ty'] = 280;
			$newRes['label'] = $label;
			$newRes['dados'] = $resRem;
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
        
        $sql = "SELECT DISTINCT t.doador as codigo, 
					t.valor, t.ano
					FROM topDoador t ";
        $sql .= $sqlAdicional;
        $sql .= " ORDER BY t.ano DESC, t.valor DESC ";
        $sql .= $nav;
       
        $res = $bd->selectDB($sql, $params);
		
		$i=0;
		
        if ($res) {
			if (!class_exists('doador'))
				require_once CONTROLLER . '/doador.class.php';

			$doador = new doador();
			foreach ($res as $r) {

				$sqlAdicional1 = " WHERE d.codigo = :codigo ";
				$params1 = array(':codigo' => $r['codigo']);
				$res1 = $doador->getDoadores($params1, $sqlAdicional1, 1, 1);
				if ($res1) {
					$res[$i] += $res1[0];


					$l = "<div class=\"user-valor-td\">";
					$l .= "<h1>Total da Doação</h1><h2>".money($r['valor'])."</h2>";
					$l .= "</div>";
					$l .= "<div class=\"user-nome-td\">";
					$l .= "<h5 class=\"pull-right\"><strong class=\"c-black\">".$res1[0]['nome']."</strong>";
					$l .= "</div>";
					$l .= "<div class=\"user-grf\">";
					$l .= "<img src=\"".VIEW."/file.php?grafico=".urlencode(serialize($this->getGrafico($r['codigo'], $params)))."&time=".time()."\" />";
					$l .= "</div>";

					$res[$i]['layout'] = $l;
					$l = '';
					
					$fala = str_replace('.', ',', money($r['valor']));
					$fala = $res1[0]['nome'].", doou para os partidos abaixo o valor de $fala de reais";
					$fala = urlencode(serialize($fala));
					$res[$i]['fala'] = $fala;
					
				} else {
					unset($res[$i]);
				}
				$i++;
			}
			return $res;
        } else {
            return false;
        }
    }
}
