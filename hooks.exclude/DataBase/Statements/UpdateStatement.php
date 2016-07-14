<?php

namespace hooks\DataBase\Statements;


use hooks\DataBase\SQLQuery;

class UpdateStatement extends StatementHelper
{
    protected $updateItems, $table, $parameters, $limit;

    public function __construct(array $items = [])
    {
        $this->updateItems = $items;
    }

    public function update(array $items) : UpdateStatement{
        $this->updateItems = $items;
        return $this;
    }

    public function where(array $parameters) : UpdateStatement{
        $this->parameters = $parameters;
        return $this;
    }

    public function to(string $table) : UpdateStatement{
        $this->table = $table;
        return $this;
    }

    public function limit($limit) : UpdateStatement{
        $this->limit = $limit;
        return $this;
    }

    public function prepareSQL() : SQLQuery{
        $sql = "UPDATE " . $this->table ." SET" ;
        $params = [];

        foreach ($this->updateItems as $key => $val){
            $sql .= " " . $key ." = " . ":".$key . ", ";
            $params[ $key ] = $val;
        }

        $sql = rtrim($sql,", ");

        $processedParams = $this->processParams($this->parameters);


        if($processedParams !== null){
            $params = array_merge($this->updateItems,$processedParams['params']);
            $sql .= $processedParams['sql'];
        }

        if($this->limit !== null){
            $sql .= " LIMIT ".$this->limit;
        }

        return new SQLQuery($sql, $params);

    }


}