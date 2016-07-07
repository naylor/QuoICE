<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';
    
class topRendas extends Generic {

    public function __construct() {}

    public function getTOP10Rendas($sqlAdicional = null, $params = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

		$nav = '';
		
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina";
        
        $sql = "SELECT t.cpf as codigo, t.estado, t.valor, t.periodo
					FROM topRendas t ";
        $sql .= $sqlAdicional;
        $sql .= $nav;
        $res = $bd->selectDB($sql, $params);

		$i=0;
		
        if ($res) {
			if (!class_exists('candidato'))
				require_once CONTROLLER . '/candidato.class.php';

			$candidato = new candidato();
			foreach ($res as $r) {
				$sqlAdicional1 = " AND c.cpf = :cpf
							      AND c.estado = :estado ";
				$params1 = array(':cpf' => $r['codigo'], ':estado' => $r['estado']);
				$res1 = $candidato->getCandidatos($params1, $sqlAdicional1, 1, 1);
				if ($res1) {
					$res[$i] += $res1[0];

				if (!class_exists('topBens'))
					require_once CONTROLLER . '/topBens.class.php';
					$bens = new topBens();

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
					$l .= "<img src=\"".VIEW."/file.php?grafico=".urlencode(serialize($bens->getGraficoBens($r['codigo'])))."&time=".time()."\" />";
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
					
					$fala = str_replace('.', ',', money($r['valor']));
					$fala = $res1[0]['nome'].", aumento de renda no valor de $fala de reais";
					$fala = urlencode(serialize($fala));
					$res[$i]['fala'] = $fala;
					
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
