<?php

namespace hooks\Storage;

use hooks\DataBase\DBConnection;

require_once BASE_DIR.'/config/DBConstants.php';

class DB extends DBWrapper{

    protected $pdo = null;
    public $useTransactions = false;
    public $errors = [];
    public $results = [];
    public $lastInsertId;
    protected $statement;
    protected $sql;
    protected $params;

    public function __construct($host = DB_HOST, $db = DB_NAME, $user = DB_USER, $pass = DB_PASS){
        if($this->pdo == NULL){
            $this->pdo = new DBConnection();
        }
    }

    public function __destruct(){
        $this->pdo = null;
    }

    public function getInstance() : \PDO{
        return $this->pdo;
    }

    public function lastInsertedId(){
        return $this->lastInsertId;
    }

    public function processParams($params){

        if(count($params) > 0){

            $sql = " WHERE";
            $DBParams = [];

            $accepted_params = array('=','<','>','LIKE','BETWEEN','!=');

            //Case of 1D Naked array with operator
            //Eg : ["name","=","tika"]
            if(count($params) === 3 && isset($params[1]) &&in_array($params[1], $accepted_params)){
                $key = $params[0];
                $operator = $params[1];
                $value = $params[2];

                $sql .= " " . $key. " " . $operator . " :p" . $key ." && "; // name = :name
                $DBParams[ ":p" . $key ] = $value;               // $DBParams[":name"] = "Tika"

            } else {
                foreach($params as $key => $value){

                    if(is_array($value)){
                        if(count($value) === 3 && isset($value[1]) &&in_array($value[1], $accepted_params)){
                            $key = $value[0];
                            $operator = $value[1];
                            $value = $value[2];
                            $paramId = $key . rand(0,99999);

                            $sql .= " " . $key. " " . $operator . " :" . $paramId ." && "; // name = :name_987
                            $DBParams[ ":" . $paramId ] = $value;               // $DBParams[":name"_987] = "Tika"
                        }

                    } else {
                        $operator = "=";
                        $paramId = $key . rand(0,99999);

                        $sql .= " " . $key. " " . $operator . " :" . $paramId ." && "; // name = :name_987
                        $DBParams[ ":" . $paramId ] = $value;               // $DBParams[":name"_987] = "Tika"
                    }


                }
            }

            $sql = rtrim($sql," && ");

            return [
                "sql" => $sql,
                "DBParams" => $DBParams
            ];

        }
        return null;

    }

    public function getFrom ($tbl, $params = array(), $limit = null, $sort = null, $class = null, $select = "*") : array {
        $this->sql = "SELECT " . $select . " FROM ". $tbl;

        $processedParams = $this->processParams($params);
        if($processedParams === null){
            $this->params = [];
        } else {
            $this->params = $processedParams['DBParams'];
            $this->sql .= $processedParams['sql'];
        }

        if($sort != null){
            $orderKey = array_keys($sort)[0];
            $order = $sort[$orderKey];
            $this->sql .= " ORDER BY ". $orderKey . " " . $order;
        }

        if($limit !== null){
            $this->sql .= " LIMIT ".$limit;
        }


        $this->useTransactions = false;
        if($this->query($this->sql,$this->params)){
            if($class === null){
                $this->results = $this->statement->fetchAll(\PDO::FETCH_OBJ);
            } else {
                $this->results =  $this->statement->fetchAll(\PDO::FETCH_CLASS, $class);
            }
        }

       return $this->results;

    }

    public function save($tbl, $fields, $params) : bool {

        if($this->exists($tbl, $params)){
            $response = $this->updateTo($tbl, $fields, $params);
        } else {
            $response = $this->insertTo($tbl, $fields);
        }

        $this->lastInsertId = $this->getInstance()->lastInsertId();

        if($response){
            return $this->commit();
        }


        return false;

    }

    public function insertTo($tbl, $fields) : bool{
        $this->sql = "INSERT INTO " . $tbl ;
        $keys = ""; $vals = "";

        $this->params = [];

        foreach ($fields as $key => $val){
            $keys .= $key .", ";
            $vals .= ":" . $key .", ";
            $this->params[ ":" . $key ] = $val;
        }

        $keys = rtrim($keys,", ");
        $vals = rtrim($vals,", ");

        $this->sql .= " (" . $keys . ") VALUES (" . $vals .")";

        return $this->query($this->sql,$this->params);
    }

    public function updateTo($tbl, $fields, $params, $limit = null) : bool {

        $this->sql = "UPDATE " . $tbl ." SET" ;
        $this->params = [];

        foreach ($fields as $key => $val){
            $this->sql .= " " . $key ." = " . ":".$key . ", ";
            $this->params[ ":" . $key ] = $val;
        }

        $this->sql = rtrim($this->sql,", ");

        $processedParams = $this->processParams($params);

        if($processedParams !== null){
            $this->params = array_merge($this->params,$processedParams['DBParams']);
            $this->sql .= $processedParams['sql'];
        }


        if($limit !== null){
            $this->sql .= " LIMIT ".$limit;
        }

        return $this->query($this->sql, $this->params );
    }

    public function deleteFrom($tbl, array $params = null, $limit = null) : bool {
        $this->sql = "DELETE FROM ". $tbl;

        $processedParams = $this->processParams($params);

        if($processedParams === null){
            $this->params = [];
        } else {
            $this->params = $processedParams['DBParams'];
            $this->sql .= $processedParams['sql'];
        }

        if($limit !== null){
            $this->sql .= " LIMIT ".$limit;
        }

        return $this->query($this->sql,$this->params);
    }

    public function exists($tbl,$params){

        //New Instance of db() so that it doesnot change existing
        $items = db()->getFrom($tbl,$params);

        return count($items) > 0;
    }

    public function filter($array, $key){

        foreach($array as $index => $item){

            foreach($item as $subItem){

                if(strpos($subItem, $key) !== false){
                    continue 2;
                }
            }
            unset($array[$index]);
        }

        return $array;

    }

    public function getColumns($tbl){
        $sql = "SHOW COLUMNS FROM ". $tbl;
        try{
            $stm = $this->getInstance()->prepare($sql);
            $stm->execute();
            return $stm->fetchAll(\PDO::FETCH_OBJ);
        } catch (\Exception $e){
            return [];
        }
    }

    public function getTables($db){
        $sql = "SHOW TABLES FROM ". $db;
        try{
            $stm = $this->getInstance()->prepare($sql);
            $stm->execute();
            return $stm->fetchAll(\PDO::FETCH_OBJ);
        } catch (\Exception $e){
            return [];
        }
    }

    public function query($sql, $params){
        if($this->prepareStatement($sql, $params)){
            return $this->commit();
        }
        return false;
    }

    public function prepareStatement($sql, $params = []) : bool {

        if($this->useTransactions && !($this->getInstance()->inTransaction())){
            $this->getInstance()->beginTransaction();
        }

        $this->statement = $this->getInstance()->prepare($sql);

        try{
            return $this->statement->execute($params);
        } catch (\PDOException $e){
            $this->errors[] = $e->getMessage();
            return false;
        }

    }

    public function commit(){
        if($this->getInstance()->inTransaction()){
            if( !$this->getInstance()->commit() ){
                $this->rollback();
                errorLog("DB Transaction Failed. Event Rolled Back.",2);
            }
        }
        return true;
    }

    public function rollback(){
        $this->getInstance()->rollBack();
    }

}
