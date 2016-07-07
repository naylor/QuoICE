<?php
if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';

class candDespesas extends Generic {

    public function __construct() {}

    public function getGrafico($res, $tipo) {
		
		$newRes='';
		$i=0;
		foreach($res as $r) {
			unset($res[$i]['codigo']);
			$res[$i]['valor'] /= 1000000;
			$i++;
		}
	
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
     
    public function getCandDespesas($sqlAdicional = null, $params = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

		$nav = '';
		
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina";
        
        $sql = "SELECT c.cpf as codigo, c.ano, SUM(t.valor) as valor
					FROM candDespesas t, candidato c
					WHERE c.id = t.id 
					AND c.ano = t.ano 
					AND c.estado = t.estado";
        $sql .= $sqlAdicional;
        $sql .= " GROUP BY c.cpf, c.ano ASC ";
        $sql .= $nav;
        
        $res = $bd->selectDB($sql, $params);
		
		$i=0;
		
        if ($res) {
			if (!class_exists('candidato'))
				require_once CONTROLLER . '/candidato.class.php';

			$candidato = new candidato();

			$sqlAdicional1 = " AND c.cpf = :cpf";
			$params1 = array(':cpf' => $res[0]['codigo']);
			$res1 = $candidato->getCandidatos($params1, $sqlAdicional1, 1, 1);
			if ($res1) {
				$res1[0] += $res[0];
			
				$l = "<div class=\"user-img\">";
				$l .= "<img src=\"".VIEW."/file.php?cpf=".$res[0]['codigo']."&time=".time()."\" class=\"img-u image-responsive\" />";
				$l .= "</div>";
				$l .= "<div class=\"user-nome\">";
				$l .= "<h5 class=\"pull-right\"><strong class=\"c-black\">".$res1[0]['nome']."</strong>";
				$l .= "<br /><strong class=\"c-sub\">".$res1[0]['situacao']."</strong></h5>";
				$l .= "</div>";
				$l .= "<div class=\"user-valor\">";
				$l .= "<h1>Despesas</h1><h2>".money($vl=isset($res[1]['valor'])?$res[1]['valor']:$res[0]['valor'])."</h2>";
				$l .= "</div>";
				$l .= "<div class=\"user-grf\">";
				$l .= "<img src=\"".VIEW."/file.php?grafico=".urlencode(serialize($this->getGrafico($res, 'despesa')))."&time=".time()."\" />";
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

				$res1[0]['layout'] = $l;
				$l = '';

				$fala = str_replace('.', ',', money($vl=isset($res[1]['valor'])?$res[1]['valor']:$res[0]['valor']));
				$fala = $res1[0]['nome'].", despesa de campanha no valor de $fala de reais";
				$fala = urlencode(serialize($fala));
				$res1[0]['fala'] = $fala;
				
				return $res1;
			} else {
				return false;
			}
        }
    }
}
