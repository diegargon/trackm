<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
/*
  NOOB CHEATSEAT
  Muestra tablas
  sqlite> PRAGMA table_info(db_info)

  Contenido con las etiquetas
  .headers on
  .mode column
  y luego el select * o lo que sea;

 */
class newDB {

    private $version = 1;
    private $db;
    private $db_path;
    private $log = [];
    private $querys = [];

    public function __construct($db_path) {
        $this->db_path = $db_path;
    }

    public function connect() {
        $response = $this->checkInstall();
        $this->db = new SQLite3($this->db_path, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
        if (!$response && !empty($this->db)) {
            $this->createTables();
            $response = $this->checkInstall();
        }
        if (empty($this->db)) {
            return $this->fail();
        } else {
            echo "\nConnection ok";
        }
        $version = $this->getDbVersion();
        if ($version < $this->version) {
            echo "\nNeed upgrade the database... upgrading";
            $this->upgradeDb($version);
        } else if ($version > $this->version) {
            echo "\nThis database version is newer than this api";
        }
        echo "\nSeems all go fine";
        echo "\nDB Version $version";
    }

    /* Common / Helpers */

    public function addItem($table, $item) {
        $this->insert($table, $item);
    }

    public function addItems($table, $items) {
        foreach ($items as $item) {
            $this->insert($table, $item);
        }
    }

    public function addItemUniqField($table, $item, $field, $value) {
        $id = $this->getIdByField($table, $field, $value);
        if (empty($id)) {
            $this->addItem($table, $item);
            return true;
        }
        //item already exists
        return false;
    }

    public function addItemsUniqField($table, $items, $field, $value) {
        foreach ($items as $item) {
            $this->addItemUniqField($table, $item, $field, $value);
        }
    }

    //TODO create a upsert insert and replace SQLite have it... i think
    public function upsertItemByField($table, $item, $field, $value) {
        $_item = $this->getItemByField($table, $field, $value);
        if (!empty($_item)) {
            echo "\nupser actualiza";
            $this->updateItemByField($table, $item, $field, $value);
            return true;
        } else {
            echo "\nupsert crea nuevo";
            $this->addItem($table, $item);
            return true;
        }

        return false;
    }

    public function getItemById($table, $id) {
        $where['id'] = ['value' => $id];
        $result = $this->select($table, null, $where, 'LIMIT 1');
        $row = $this->fetch($result);
        $this->finalize($result);

        return $row;
    }

    public function getItemByField($table, $field, $value) {
        $where[$field] = ['value' => $value];
        $result = $this->select($table, null, $where, 'LIMIT 1');
        $row = $this->fetch($result);
        $this->finalize($result);
        return $row;
    }

    public function getIdByField($table, $field, $value) {
        $where[$field] = ['value' => $value];
        $result = $this->select($table, 'id', $where, 'LIMIT 1');
        $row = $this->fetch($result);
        $this->finalize($result);
        return $row['id'];
    }

    public function updateItemById($table, $id, $values) {
        $where['id'] = ['value' => $id];

        $this->update($table, $values, $where, 'LIMIT 1');
    }

    public function updateItemByField($table, $item, $field, $value) {
        $where[$field] = ['value' => $value];

        $this->update($table, $item, $where, 'LIMIT 1');
    }

    public function getTableData($table) {
        return $this->select($table);
    }

    public function deleteItemById($table, $id) {
        $where['id'] = ['value' => $id];
        $this->delete($table, $where, 'LIMIT 1');
    }

    public function deleteItemByField($table, $field, $value) {
        $where[$field] = ['value' => $value];
        $this->delete($table, $where, 'LIMIT 1');
    }

    //exact mean word separated with spaces don't known yet if works like this possible TODO/FIX
    public function search($table, $field, $value, $exact = null, $extra = null) {
        $value = $this->escapeString($value);
        $query = "SELECT * FROM " . $table . " WHERE " . $field . " LIKE ";
        if (!empty($exact)) {
            $query .= "'%" . $value . "%'";
        } else {
            $query .= "'% " . $value . " %'";
        }
        $this->query($query);
    }

    /* MAIN */

    public function insert($table, $values) {
        $query = 'INSERT INTO "' . $table . '" (';
        $query_binds = '';
        $query_keys = '';
        $bind_values = [];
        foreach ($values as $key => $value) {
            $query_keys .= '"' . $key . '"';
            $query_binds .= ':' . $key;
            $bind_values[':' . $key] = $value;

            if ($value != end($values)) {
                $query_keys .= ', ';
                $query_binds .= ', ';
            }
        }
        $query .= $query_keys . ') VALUES (' . $query_binds;
        $query .= ')';

        $this->querys[] = $query;

        $statement = $this->db->prepare($query);

        foreach ($bind_values as $bkey => $bvalue) {
            $statement->bindValue($bkey, $bvalue);
        }

        return (($statement->execute()) || $this->fail());
    }

    public function upsert() {
        /*
          TODO
          SYNTAX

          INSERT INTO players (user_name, age)
          VALUES('steven', 32)
          ON CONFLICT(user_name)
          DO UPDATE SET age=excluded.age;

         */
    }

    /*
      $where['userid'] = [ 'value' => diego, 'op' => '=', 'logic' => 'AND' ];
     */

    public function select($table, $what = null, $where = null, $extra = null) {

        $bind_values = [];

        $query = 'SELECT ';
        !empty($what) ? $query .= $what : $query .= '*';
        $query .= ' FROM "' . $table . '"';
        if ($where != null) {
            $query .= ' WHERE ';
            foreach ($where as $where_k => $where_v) {
                !empty($where_v['op']) ? $operator = $where_v['op'] : $operator = '=';
                !empty($where_v['logic']) ? $logic = $where_v['logic'] : $logic = 'AND';

                $query .= ' "' . $where_k . '" ' . $operator . ' :' . $where_k;
                if ($where_v != end($where)) {
                    $query .= ' ' . $operator . ' ';
                }
                $bind_values[':' . $where_k] = $where_v['value'];
            }
        }
        !empty($extra) ? $query .= ' ' . $extra : null;
        $this->querys[] = $query;
        $statement = $this->db->prepare($query);

        if (!empty($bind_values)) {
            foreach ($bind_values as $bkey => $bvalue) {
                $statement->bindValue($bkey, $bvalue);
            }
        }

        $response = $statement->execute();

        return $response;
    }

    public function delete($table, $where, $extra = null) {

        $query = 'DELETE FROM ' . $table . ' ';

        if ($where != null) {
            $query .= ' WHERE ';
            foreach ($where as $where_k => $where_v) {
                !empty($where_v['op']) ? $operator = $where_v['op'] : $operator = '=';
                !empty($where_v['logic']) ? $logic = $where_v['logic'] : $logic = 'AND';

                $query .= ' "' . $where_k . '" ' . $operator . ' :' . $where_k;
                if ($where_v != end($where)) {
                    $query .= ' ' . $operator . ' ';
                }
                $bind_values[':' . $where_k] = $where_v['value'];
            }
        }
        !empty($extra) ? $query .= ' ' . $extra : null;
        $this->querys[] = $query;
        $statement = $this->db->prepare($query);

        if (!empty($bind_values)) {
            foreach ($bind_values as $bkey => $bvalue) {
                $statement->bindValue($bkey, $bvalue);
            }
        }

        $response = $statement->execute();

        return $response;
    }

    /*
      $set['username'] = 'diego';
      $where['userid'] = [ 'value' => diego, 'op' => '=', 'logic' => 'AND' ];
     */

    public function update($table, $set, $where = null, $extra = null) {
        $bind_values = [];

        $query = 'UPDATE ' . $table;

        $query .= ' SET ';
        foreach ($set as $set_k => $set_v) {
            $query .= ' "' . $set_k . '" = ' . ':' . $set_k;
            ($set_v != end($set)) ? $query .= ', ' : null;
            $bind_values[':' . $set_k] = $set_v;
        }

        if ($where != null) {
            $query .= ' WHERE ';
            foreach ($where as $where_k => $where_v) {
                !empty($where_v['op']) ? $operator = $where_v['op'] : $operator = '=';
                !empty($where_v['logic']) ? $logic = $where_v['logic'] : $logic = 'AND';

                $query .= ' "' . $where_k . '" ' . $operator . ' :' . $where_k;
                if ($where_v != end($where)) {
                    $query .= ' ' . $logic . ' ';
                }
                $bind_values[':' . $where_k] = $where_v['value'];
            }
        }
        !empty($extra) ? $query .= ' ' . $extra : null;
        $this->querys[] = $query;

        $statement = $this->db->prepare($query);

        if (!empty($bind_values)) {
            foreach ($bind_values as $bkey => $bvalue) {
                $statement->bindValue($bkey, $bvalue);
            }
        }

        return (($statement->execute()) || $this->fail());
    }

    public function escape($string) {
        return $this->db->escapeString($string);
    }

    public function fetch($result) {
        return $result->fetchArray(SQLITE3_ASSOC);
    }

    public function fetch_all($result) {
        $rows = [];
        while ($row = $result->fetchArray()) {
            $rows[] = $row;
        }
        $this->finalize($result);
        return $rows;
    }

    public function getLastId() {
        return $this->db->lastInsertRowID();
    }

    public function finalize($result) {
        $result->finalize();
    }

    public function getDbVersion() {
        $query = $this->select('db_info');
        $result = $this->fetch($query);
        return $result['version'];
    }

    function query($query) {
        return $this->db->query($query);
    }

    private function checkInstall() {
        if (file_exists($this->db_path)) {
            $this->log[] = 'db file exists';
            //check if exist tables
            return true;
        } else {
            $this->log[] = 'db file not exists';
            return false;
        }
    }

    private function fail() {
        echo "\n FAIL \n";
        echo print_r($this->querys);
        return false;
    }

    private function createTables() {
        echo "\nEntering create tables\n";
        require('../config/db.sql.php');

        return create_db();
    }

    private function upgradeDb($from) {
        echo "\nEntering updatedb from $from \n";
        require('../config/db.sql.php');

        return update_db($from);
    }

}