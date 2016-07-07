<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class topCandidatos extends Generic {

    public function __construct() {}

    public function getGrafico($cpf, $tipo) {
        $bd = new database();
        
        $sql = "SELECT DISTINCT ano, (valor/1000000) as valor
					FROM topCandidatos t 
					WHERE t.cpf = :cpf
					AND t.tipo = :tipo";
        
		$params = array(':cpf' => $cpf, ':tipo' => $tipo);
        $res = $bd->selectDB($sql, $params);

		$newRes='';
		
        if ($res) {
			$label['x'] = 'Anos';
			$label['y'] = ucwords(moneyType($res[0]['valor'] * 1000000));
			$label['title'] = ucwords($tipo).' para Campanha Eleitoral';
			$label['tx'] = 320;
			$label['ty'] = 280;
			$newRes['label'] = $label;
			$newRes['dados'] = $res;
			return $newRes;
        } else {
            return false;
        }
    }
     
    public function getTOP10Candidatos($sqlAdicional = null, $params = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

		$nav = '';
		
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina";
        
        $sql = "SELECT c.cpf as codigo, t.ano, SUM(t.valor)
					FROM candReceitas t, candidato c
					WHERE c.id = t.id 
					AND c.ano = t.ano 
					AND c.estado = t.estado 
					GROUP BY c.cpf, t.ano ";
        $sql .= $sqlAdicional;
        $sql .= " ORDER BY t.ano DESC, t.valor DESC ";
        $sql .= $nav;
        
        $res = $bd->selectDB($sql, $params);
		
		$i=0;
		
        if ($res) {
			if (!class_exists('candidato'))
				require_once CONTROLLER . '/candidato.class.php';

			$candidato = new candidato();
			foreach ($res as $r) {

				$sqlAdicional1 = " AND c.cpf = :cpf
							      AND c.ano = :ano ";
				$params1 = array(':cpf' => $r['codigo'],
									':ano' => $r['ano']);
				$res1 = $candidato->getCandidatos($params1, $sqlAdicional1, 1, 1);
				if ($res1) {
					$res[$i] += $res1[0];

					$l = "<div class=\"user-img\">";
					$l .= "<img src=\"".VIEW."/file.php?cpf=".$r['codigo']."&time=".time()."\" class=\"img-u image-responsive\" />";
					$l .= "</div>";
					$l .= "<div class=\"user-nome\">";
					$l .= "<h5 class=\"pull-right\"><strong class=\"c-black\">".$res1[0]['nome']."</strong>";
					$l .= "<br /><strong class=\"c-sub\">".$res1[0]['situacao']."</strong></h5>";
					$l .= "</div>";
					$l .= "<div class=\"user-valor\">";
					$l .= "<h1>Aumento de Renda</h1><h2>".money($r['valor'])."</h2>";
					$l .= "</div>";
					$l .= "<div class=\"user-grf\">";
					$l .= "<img src=\"".VIEW."/file.php?grafico=".urlencode(serialize($this->getGrafico($r['codigo'], $params[':tipo'])))."&time=".time()."\" />";
					$l .= "</div>";
					$l .= "<div class=\"user-dados\">";
					$l .= "<h1>Cargo Disputado</h1><h2>".$res1[0]['cargo']."</h2>";
					$l .= "<h1>Profissão</h1><h2>".$res1[0]['profissao']."</h2>";
					$l .= "<h1>Escolaridade</h1><h2>".$res1[0]['escola']."</h2>";
					$l .= "</div>";
					$l .= "<div class=\"user-proc\">";
					$l .= "<h1>Processos</h1><h2>".abs($res1[0]['qdeProcessos'])."</h2>";
					$l .= "<h1>Participação em Sessões</h1><h2>".$res1[0]['qdeSessoes']."</h2>";
					$l .= "</div>";

					$res[$i]['layout'] = $l;
					$l = '';
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
