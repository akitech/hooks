<?php


namespace hooks\DataBase\Statements;


use hooks\DataBase\SQLQuery;

class InsertStatement extends StatementHelper
{
    protected $insertItems, $table;

    public function __construct(array $items = [])
    {
        $this->insertItems = $items;
    }

    public function insert(array $items) : InsertStatement{
        $this->insertItems = $items;
        return $this;
    }

    public function to(string $table) : InsertStatement{
        $this->table = $table;
        return $this;
    }

    public function prepareSQL() : SQLQuery{
        $sql = "INSERT INTO " . $this->table ;
        $keys = ""; $vals = "";

        $params = [];

        foreach ($this->insertItems as $key => $val){
            $keys .= $key .", ";
            $vals .= ":" . $key .", ";
            $params[ ":" . $key ] = $val;
        }

        $keys = rtrim($keys,", ");
        $vals = rtrim($vals,", ");

        $sql .= " (" . $keys . ") VALUES (" . $vals .")";

        return new SQLQuery($sql, $params);

    }


}