<?php

namespace hooks\Storage;


abstract class DBObject
{

    protected $dbTable;
    protected $dbPrimaryKey;

    public function __construct($search = null)
    {
        if($this->dbTable == null){
            $class = explode("\\",get_class($this));
            $this->dbTable = end($class); //End Part of class
        }
        if($this->dbPrimaryKey == null){
            $this->dbPrimaryKey();
        }

        if($search !== null){
            $this->find($search);
        }

    }

    private function dbPrimaryKey(){
        $databaseColumns = DB::getColumns($this->dbTable);

        foreach($databaseColumns as $column){
            if($column->Key == "PRI"){
                $this->dbPrimaryKey = $column->Field;
                return true;
            }
        }
        $this->dbPrimaryKey = "id";
    }


    public function find($search){
        $params = [];

        if(is_scalar($search)){
            $paramKey = $this->dbPrimaryKey;
            $paramValue = $search;
            $params[$paramKey]  = $paramValue;
        } else {
            foreach($search as $paramKey => $paramValue){
                $params[$paramKey]  = $paramValue;
            }
        }

        $response = DB::get($this->dbTable, $params, 1);
        if(count($response) > 0)
        {
            $first = $response[0];
            foreach($first as $key => $value){
                if(property_exists($this, $key)){
                    $this->$key = $value;
                }
            }
            return true;
        } else {
            return false;
        }
    }

    public function save(){

        $key = $this->dbPrimaryKey;
        $params = [$key => $this->$key];

        $data = [];


        $databaseColumns = DB::getColumns($this->dbTable);
        foreach($databaseColumns as $column){
            if(property_exists($this, $column->Field)){
                $property = $column->Field;
                $data[$property] = $this->$property;
            }
        }

        return DB::save($this->dbTable, $data, $params);

    }


    public function __toString(){
        $props = get_object_vars($this);
        return json_encode($props);
    }

    public function __get($name)
    {
        if(isset($this->$name)){
            return $this->$name;
        } else {
            die("Property " . $name . " does not exist in " . get_class($this) . " at line <strong>" .
                __LINE__ . "</strong> in file <strong>" . __FILE__ . "</strong>");
        }
    }

    public function __set($name, $value)
    {
        if(isset($this->$name)){
            $this->$name = $value;
        } else {
            die("Property " . $name . " does not exist in " . get_class($this) . " at line <strong>" .
                __LINE__ . "</strong> in file <strong>" . __FILE__ . "</strong>");
        }
    }


}