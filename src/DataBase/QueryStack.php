<?php

namespace hooks\DataBase;


class QueryStack
{

    public $stacks = [];
    public $errors = [];
    public $results = [];

    public function add(SQLQuery $query){
        $this->stacks[] = $query;
    }

    public function remove(int $index){
        unset($this->stacks[$index]);
    }

    public function get(int $index) : SQLQuery{
        return $this->stacks[$index];
    }

    public function execute(){
        $pdo = new DBConnection();
        $pdo->beginTransaction();

        foreach ($this->stacks as $index => $query){
            $query->setDBInstance($pdo);
            $query->execute($pdo);
            $this->results[$index] = $query->getResults($query->returnClass);
            $this->errors[$index] = $query->errors;
        }
        if(count($this->getErrors()) == 0){
            return $pdo->commit();
        } else {
            $pdo->rollBack();
        }
        return false;
    }

    public function getErrors(){
        return call_user_func_array('array_merge', $this->errors);
    }



}