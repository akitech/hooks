<?php

namespace hooks\Storage;


abstract class DBWrapper
{

    protected $table, $select, $update, $delete, $params, $object, $limit, $sort, $results, $isExecuted = false;

    public function select(string $select = "*"){
        $this->select = $select;
        return $this;
    }

    public function get(string $select = "*"){
        $this->select($select);/* alias of select */
        return $this;
    }

    public function update(array $array){
        $this->update = $array;
        return $this;
    }

    public function delete(){
        $this->delete = true;
        return $this;
    }

    public function from($table){
        $this->table = $table;
        return $this;
    }

    public function table($table){ /* alias of from */
        $this->from($table);
        return $this;
    }

    public function where($array){
        $this->params = $array;
        return $this;
    }

    public function limit($int){
        $this->limit = $int;
        return $this;
    }

    public function sort($sort){
        $this->sort = $sort;
        return $this;
    }

    public function cast($object){
        $this->object = $object;
        return $this;
    }

    public function execute(){

        if($this->isExecuted){
            return $this;
        }

        $this->isExecuted = true;

        $db = db();

        if($this->select !== null){
            $this->results = $db->getFrom($this->table, $this->params, $this->limit, $this->sort, $this->object, $this->select );
            return $this;
        }

        if($this->update !== null){
            if($db->updateTo($this->table, $this->update, $this->params, $this->limit)){
                $db->commit();
            }
        }

        if($this->delete !== null){
            if($db->deleteFrom($this->table, $this->params, $this->limit)){
                $db->commit();
            }
        }

    }


    /*
    +--------------------------------------------------------------------+
    +                                                                    +
    +   Following methods are helpers for select statements:             +
    +                                                                    +
    +--------------------------------------------------------------------+

    */

    public function first(){
        $this->execute();
        if(count($this->results) > 0){
            return current($this->results);
        } else {
            return null;
        }
    }

    public function firstOrDefault(){
        $first = $this->first();
        if($first == null){
            $first = new get_called_class();
        }
        return $first;
    }

    public function lastOrDefault(){
        $last = $this->last();
        if($last == null){
            $last = new get_called_class();
        }
        return $last;
    }

    public function last(){
        $this->execute();
        if(count($this->results) > 0){
            return end($this->results);
        } else {
            return null;
        }
    }

    public function count(){
        $this->execute();
        return count($this->results);
    }

    public function all(){
        $this->execute();
        return $this->results;
    }


}