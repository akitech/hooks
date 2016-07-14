<?php

namespace hooks\DataBase\Statements;


use hooks\DataBase\SQLQuery;

class DeleteStatement extends StatementHelper
{
    protected $parameters, $table, $limit;

    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    public function where(array $parameters) : DeleteStatement{
        $this->parameters = $parameters;
        return $this;
    }

    public function from(string $table) : DeleteStatement{
        $this->table = $table;
        return $this;
    }
    public function limit($limit) : DeleteStatement{
        $this->limit = $limit;
        return $this;
    }

    public function prepareSQL() : SQLQuery{
        $sql = "DELETE FROM ". $this->table;

        $processedParams = $this->processParams($this->parameters);

        if($processedParams === null){
            $params = [];
        } else {
            $params = $processedParams['params'];
            $sql .= $processedParams['sql'];
        }

        if($this->limit !== null){
            $sql .= " LIMIT ".$this->limit;
        }

        return new SQLQuery($sql, $params);

    }



}