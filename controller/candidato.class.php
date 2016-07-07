<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';
    
class candidato extends Generic {

    public function __construct() {}

    public function buscaSimiliar($string) {
        $bd = new database();

		$chave='';
		$words = explode(" ", $string);
		foreach($words as $word)
			$chave .= '%'.$word;
		$chave .= '%';
			
    	$sql = "SELECT cpf, nome, 
				LEVENSHTEIN(nome, '$string') AS distance 
				FROM candidato WHERE nome LIKE '$chave'
				ORDER BY distance ASC LIMIT 1";

        $res = $bd->selectDB($sql);

        if ( $res )
        {
            return $res;
        }
        else
        {
            return false;
        }
    }
    
    public function listCandToJSON($string) {
        $bd = new database();

    	$sql = "SELECT c.cpf as id, c.nome as name
							FROM candidato c
							WHERE c.nome LIKE :s 
							ORDER BY nome ASC
							LIMIT 10";

        $params = array(':s' => '%' . $string . '%');
        $res = $bd->selectDB($sql, $params);

        if ( $res )
        {
            return $res;
        }
        else
        {
            return false;
        }
    }
    
    public function getCandidatos($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

		$nav = '';
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT c.nome, c.estado, c.situacao,
        						IF(c.qdeSessoes is NULL,'N/D',c.qdeSessoes) as qdeSessoes,
        						IF(c.qdeProcessos is NULL,'N/D',c.qdeProcessos) as qdeProcessos,
								p.nome as profissao, ca.nome as cargo,
								e.nome as escola, c.foto
							FROM candidato c, candProfissao p, candEscolaridade e, 
								candCargo ca
							WHERE c.profissao = p.codigo
							AND	c.cargo = ca.codigo
							AND c.escolaridade = e.codigo";

        $sql .= " $sqlAdicional ";

        $sql .= " ORDER BY nome, ano DESC, situacao DESC ";

        $sql .= "$nav";

        $res = $bd->selectDB($sql, $params);
		
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

}
