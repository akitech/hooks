<?php

namespace hooks\DataBase\Statements;

use hooks\DataBase\QueryStack;
use hooks\DataBase\SQLQuery;
use Models\ViewModels\PageResults;

class SelectStatement extends StatementHelper
{

    protected $select, $table, $parameters, $class = "stdClass", $limit, $sort;

    public function __construct(string $select = "*")
    {
        $this->select = $select;
    }

    public function select(string $select = "*")
    {
        $this->select = $select;
        return $this;
    }

    public function where(array $parameters) : SelectStatement{
        $this->parameters = $parameters;
        return $this;
    }

    public function from(string $table) : SelectStatement{
        $this->table = $table;
        return $this;
    }

    public function cast(string $class = "stdClass") : SelectStatement{
        $this->class = $class;
        return $this;
    }

    public function limit($limit) : SelectStatement{
        $this->limit = $limit;
        return $this;
    }

    public function sortBy($sort) : SelectStatement{
        $this->sort = $sort;
        return $this;

    }

    public function prepareSQL() : SQLQuery{
        $sql = "SELECT " . $this->select . " FROM ". $this->table;

        $processedParams = $this->processParams($this->parameters);

        if($processedParams === null){
            $params = [];
        } else {
            $params = $processedParams['params'];
            $sql .= $processedParams['sql'];
        }

        if($this->sort != null){
            $orderKey = array_keys($this->sort)[0];
            $order = $this->sort[$orderKey];
            $sql .= " ORDER BY ". $orderKey . " " . $order;
        }

        if($this->limit !== null){
            $sql .= " LIMIT ".$this->limit;
        }

        return new SQLQuery($sql, $params, $this->class);

    }

    public function all() : array {
        if($this->execute()){
            return $this->getPDOStatement()->fetchAll(\PDO::FETCH_CLASS, $this->class);
        }
        return [];
    }

    public function currentPageResults(int $items_per_page = ITEMS_PER_PAGE) : PageResults {

        $page =  route()->pageIndex();

        //Two Queries:
        $qs = new QueryStack();

        $counter = new SelectStatement();
        $counter->from($this->table);
        $counter->select("COUNT(*) AS total");

        $counter->where($this->parameters);
        $counter->glue($this->glue);

        $qs->add($counter->prepareSQL());



        $this->limit( ($page - 1) * $items_per_page .",". $items_per_page);
        $qs->add($this->prepareSQL());

        if($qs->execute()){

            $pageRes = new PageResults();
            $pageRes->setItemsPerPage($items_per_page);
            $pageRes->setCurrentPage($page);
            $pageRes->setTotalItems($qs->results[0][0]->total);
            $pageRes->setItems($qs->results[1]);

            return $pageRes;
        }

        return new PageResults();
    }

    public function first(){
        if($this->execute()){
            return $this->getPDOStatement()->fetchObject($this->class);
        }
        return null;
    }

    public function rowCount() : int{
        if($this->execute()){
            return $this->getPDOStatement()->rowCount();
        }
        return 0;
    }

    public function firstOrNew()  {
        $first = $this->first();
        if($this->first() == null){
            return new $this->class;
        }
        return $first;
    }

}