<?php

if (!class_exists('Generic'))
    require_once CONTROLLER . '/generic.class.php';
    
class doador extends Generic {

    public function __construct() {}
    
    public function getDoadores($params, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();

		$nav = '';
        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ", $itensPorPagina";

        $sql = "SELECT d.codigo, d.nome
							FROM doador d";

        $sql .= " $sqlAdicional ";

        $sql .= " ORDER BY nome ";

        $sql .= "$nav";

        $res = $bd->selectDB($sql, $params);
		
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

}
