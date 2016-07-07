<?php
if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class topPartidos extends Generic {

    public function __construct() {}

    public function getGrafico($partido, $p) {
        $bd = new database();

        $s = '';
        if (isset($p[':estado']))
			$s = ' AND t.estado = :estado ';
			
        $sql = "SELECT t.ano, SUM(valor/1000000) as valor
					FROM topPartidos t 
					WHERE t.partido = :partido
					AND t.tipo = :tipo
					$s
					GROUP BY t.ano, t.partido";
        
        if (isset($p[':estado']))
			$params = array(':partido' => $partido, ':tipo' => $p[':tipo'], ':estado' => $p[':estado']);
		else
			$params = array(':partido' => $partido, ':tipo' => $p[':tipo']);

        $res = $bd->selectDB($sql, $params);

		$newRes='';
		
        if ($res) {
			$label['x'] = 'Anos';
			$label['y'] = ucwords(moneyType($res[0]['valor'] * 1000000));
			$label['title'] = ucwords($p[':tipo']).' para Campanha Eleitoral';
			$label['tx'] = 320;
			$label['ty'] = 280;
			$newRes['label'] = $label;
			$newRes['dados'] = $res;
			return $newRes;
        } else {
            return false;
        }
    }
     
    public function getTOP10Partidos($sqlAdicional = null, $params = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

		$nav = '';
		
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina";
        
        $sql = "SELECT t.partido as codigo, 
					t.ano, SUM(t.valor) as valor
					FROM topPartidos t ";
        $sql .= $sqlAdicional;
        $sql .= " GROUP BY t.partido, t.ano ORDER BY t.ano DESC, valor DESC ";
        $sql .= $nav;

        $res = $bd->selectDB($sql, $params);
		
		$i=0;
		
        if ($res) {
			if (!class_exists('partido'))
				require_once CONTROLLER . '/partido.class.php';

			$candidato = new partido();
			foreach ($res as $r) {

				$sqlAdicional1 = " WHERE c.codigo = :partido ";
				$params1 = array(':partido' => $r['codigo']);
				$res1 = $candidato->getPartidos($params1, $sqlAdicional1, 1, 1);
				if ($res1) {
					$res[$i] += $res1[0];

					$l = "<div class=\"user-valor-td\">";
					$l .= "<h1>Valor da ".ucwords($params[':tipo'])."</h1><h2>".money($r['valor'])."</h2>";
					$l .= "</div>";
					$l .= "<div class=\"user-nome-td\">";
					$l .= "<h5 class=\"pull-right\"><strong class=\"c-black\">".$res1[0]['nome2']."</strong>";
					$l .= "</div>";
					$l .= "<div class=\"user-grf\">";
					$l .= "<img src=\"".VIEW."/file.php?grafico=".urlencode(serialize($this->getGrafico($r['codigo'], $params)))."&time=".time()."\" />";
					$l .= "</div>";
					
					$res[$i]['layout'] = $l;
					$l = '';
					
					$fala = str_replace('.', ',', money($r['valor']));
					$fala = $res1[0]['nome'].", ".$params[':tipo']." de campanha no valor de $fala de reais";
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
