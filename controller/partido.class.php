<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';
    
class partido extends Generic {

    public function __construct() {}
    
    public function getPartidos($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

		$nav = '';
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT c.nome as nome2, c.sigla as nome
					FROM partido c ";

        $sql .= " $sqlAdicional ";

        $sql .= " ORDER BY c.nome ";

        $sql .= "$nav";

        $res = $bd->selectDB($sql, $params);
		
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

}
