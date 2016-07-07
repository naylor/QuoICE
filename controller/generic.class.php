<?php

if (!class_exists('database'))
    require_once MYSQL;

Abstract class Generic {

    public function __construct() {
        //
    }

    // MÉTODO PARA INSERÇÃO DE OBJETO
    public function insertOrUpdate($params, $table = null) {
        $bd = new database();

        // decriptografa elementos que possam
        // estar criptografados dentro do Array
        $params = dcripArray($params);
        foreach (array_keys($params) as $key) {
            if ($key == 'codigo') {
                $INS[] = 'NULL';
                $COL[] = $key;
            } else {
                $COL[] = $key;

                if ($key == 'senha') {
                    $INS[] = 'PASSWORD(:' . $key . ')';
                    $UP[] = $key . '=PASSWORD(:' . $key . ')';
                } else if ($params[$key] == 'NULL') {
                    $INS[] = 'NULL';
                    $UP[] = $key . '=NULL';
                    unset($params[$key]);
                } else if ($params[$key] == 'NOW()') {
                    $INS[] = 'NOW()';
                    $UP[] = $key . '=NOW()';
                    unset($params[$key]);
                } else if ($params[$key] == '--') { //datas vazias
                    $INS[] = 'NULL';
                    $UP[] = $key . '=NULL';
                    unset($params[$key]);
                } else {
                    $params[$key] = xss_clean($params[$key]);
                    $INS[] = ':' . $key;
                    $UP[] = $key . '=:' . $key;
                }
            }
        }

        $INS = implode(',', $INS);
        $COL = implode(',', $COL);
        $UP = implode(',', $UP);

        if (!$table)
            $table = get_called_class();

        if (!$params['codigo']) {
            $sql = "INSERT INTO $table ($COL) VALUES ($INS)";
            unset($params['codigo']);
            $res = $bd->insertDB($sql, $params);
        } else {
            $sql = "UPDATE $table SET $UP "
                    . "WHERE codigo=:codigo";
            $res = $bd->updateDB($sql, $params);
        }

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    public function count() {
        $bd = new database();
        $table = get_called_class();

        $sql = "SELECT COUNT(*) as total FROM $table";

        $res = $bd->selectDB($sql);
        if ($res[0]) {
            return $res[0]['total'];
        } else {
            return false;
        }
    }

    public function getList($params = null, $sqlAdicional = null, $item = null, $itensPorPagina = null) {
        $bd = new database();
        $table = get_called_class();

        $nav = null;

        if ($item && $itensPorPagina)
            $nav = "LIMIT " . ($item - 1) . ",$itensPorPagina ";

        if (!isset($params['codigo'])) {
            $sql = "SELECT * FROM $table $sqlAdicional $nav ";
        } else {
            $sql = "SELECT * FROM $table "
                    . "WHERE codigo=:codigo $sqlAdicional $nav ";
        }

        $res = $bd->selectDB($sql, $params);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    public function delete($codigo) {
        $bd = new database();
        $table = get_called_class();

        // PDO NÃO ACEITA VÁRIOS ARGUMENTOS PARA DELETE
        // É NECESSÁRIO PREPARAR A QUERY
        // OBS: NÃO FOI FEITO DIRETO PARA NÃO COMPROMETER
        // A SEGURANÇA FORNECIDA PELO PDO CONTRA SQLInjection
        $codigo = explode(',', $codigo);

        if ($codigo[0] == '0')
            unset($codigo[0]);

        $i = 0;
        foreach ($codigo as $value) {
            $indice = 'A' . $i;
            $new_array[$indice] = dcrip($value);
            $new_params[] = ':' . $indice;
            $i++;
        }
        $param = implode($new_params, ',');

        $sql = "DELETE FROM $table WHERE codigo IN ($param)";

        $params = $new_array;
        $res = $bd->deleteDB($sql, $params);

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

}

?>
