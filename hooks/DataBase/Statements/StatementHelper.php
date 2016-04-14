<?php

namespace hooks\DataBase\Statements;


use hooks\DataBase\DBConnection;
use hooks\DataBase\SQLQuery;

abstract class StatementHelper
{
    /**
     * @param $params
     * @return array | null
     */
    protected $pdoStatement;
    public $isExecuted = false;
    public $execution;
    public $glue = "&&";

    public function glue($glue){
        $this->glue = ($glue == "&&" || $glue == "||" || $glue == "AND" || $glue = "OR") ? $glue : "&&";
        return $this;
    }

    public function processParams($params){


        $glue = $this->glue;

        if(count($params) > 0){

            $sql = " WHERE";
            $DBParams = [];

            $accepted_params = array('=','<','>','LIKE','NOT LIKE','BETWEEN','!=','IN','NOT IN');

            //Case of 1D Naked array with operator
            //Eg : ["name","=","tika"]
            if(count($params) === 3 && isset($params[1]) &&in_array($params[1], $accepted_params)){
                $key = $params[0];
                $operator = $params[1];
                $value = $params[2];


                $prep = $this->prepareCompareString($key, $operator, $value, $glue);

                $sql .= $prep["sql"];
                $DBParams = array_merge($DBParams, $prep["params"]);

            } else {
                foreach($params as $key => $value){

                    if(is_array($value)){
                        if(count($value) === 3 && isset($value[1]) &&in_array($value[1], $accepted_params)){
                            $key = $value[0];
                            $operator = $value[1];
                            $value = $value[2];

                            $prep = $this->prepareCompareString($key, $operator, $value, $glue);

                            $sql .= $prep["sql"];
                            $DBParams = array_merge($DBParams, $prep["params"]);

                        }

                    } else {
                        $operator = "=";
                        $paramId = $key . rand(0,99999);

                        $sql .= " " . $key. " " . $operator . " :" . $paramId ." $glue "; // name = :name_987
                        $DBParams[ ":" . $paramId ] = $value;               // $DBParams[":name"_987] = "Tika"
                    }


                }
            }

            $sql = rtrim($sql," ");
            $sql = rtrim($sql, $glue);

            return [
                "sql" => $sql,
                "params" => $DBParams
            ];

        }
        return null;

    }

    private function prepareCompareString($entityKey, $operator, $value, $glue){

        $params = [];

        if(($operator == "IN" || $operator == "NOT IN" ) || is_array($value))
        {
            //Splitting into sets of parameters
            $replacement = [];
            foreach ($value as $index => $val){
                $paramId = ":_parameter_" . rand(0,9999999) ."_".  $index;
                $replacement[] = $paramId;
                $params[ $paramId ] = $val;
            }

            $paramId = "("  . implode(",",$replacement) . ")"; //Making (:parameter0, :parameter1, :parameter2)
        }

        else if($operator == "LIKE" && strlen($value) > 0)

        {
            if( $value[0] != "%"  && $value[strlen($value) - 1] != "%")
            {
                //Ading % on both sides. Probably forgot that by Programmer
                $value = "%" . $value . "%";
            }
            $paramId = ":_parameter_" . rand(0,9999999);
            $params[$paramId] = $value;
        }

        else
        {
            $paramId = ":_parameter_" . rand(0,9999999);
            $params[$paramId] = $value;
        }


        $sql = " $entityKey $operator $paramId $glue ";


        return [ "sql" => $sql, "params" => $params ];
    }

    public function prepareSQL() : SQLQuery{
        return new SQLQuery("");
    }

    public function getPDOStatement() : \PDOStatement{
        return $this->pdoStatement;
    }

    public function execute(\PDO $pdo = null) : bool {
        if($this->isExecuted){ //Saving some un-necessary work...
            return $this->execution;
        }
        if($pdo == null){
            $pdo = new DBConnection();
        }
        $query = $this->prepareSQL();
        $this->execution = $query->execute($pdo);
        /*
        if(!$this->execution){
            print_pre($pdo->errorInfo());
        }
        */
        $this->pdoStatement = $query->getStatement();
        return $this->execution;
    }
}