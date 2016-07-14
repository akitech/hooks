<?php
namespace hooks\DataBase;

use hooks\Errors\Error;
use hooks\Utils\PageResults;

class SQLQuery
{

    private $sql, $params;
    public $errors = [];
    public $returnClass = "stdClass";
    private $PDOStatement;
    private $isExecuted = false;
    private $execution = false;
    private $pdo;


    public function __construct(string $sql, array $params = [], $returnClass = "stdClass")
    {
        $this->sql = $sql;
        $this->params = $params;
        $this->returnClass = $returnClass;
    }

    public function getSql(){
        return $this->sql;
    }
    public function getParams(){
        return $this->params;
    }

    public function sql($sql){
        $this->sql = $sql;
    }

    public function setDBInstance(\PDO $pdo){
        $this->pdo = $pdo;
    }

    public function getDBInstance() : \PDO{
        if($this->pdo == null){
            $this->pdo = db()->getInstance();
        }
        return $this->pdo;
    }

    public function getResults(string $class = "stdClass")
    {
        $this->returnClass = $class;
        if(!$this->isExecuted){
            $this->execute();
        }

        return $this->getStatement()->fetchAll(\PDO::FETCH_CLASS, $this->returnClass);
    }

    public function rowCount()
    {
        if(!$this->isExecuted){
            $this->execute();
        }
        return $this->getStatement()->rowCount();
    }

    public function exists(){
        return ($this->rowCount() > 0);
    }

    public function getErrors()
    {
        if(!$this->isExecuted){
            $this->execute();
        }
        return $this->errors;
    }

    public function hasError()
    {
        if(!$this->isExecuted){
            $this->execute();
        }
        return count($this->errors) > 0;
    }

    public function lastInsertId()
    {
        return $this->getDBInstance()->lastInsertId();
    }

    public function preparePDOStatement() : \PDOStatement
    {
        return $this->getDBInstance()->prepare($this->sql);
    }

    public function execute() : bool
    {
        if($this->isExecuted){
            return $this->execution;
        }

        //$begin = microtime(true);
        $this->PDOStatement = $this->preparePDOStatement();
        try{
            $this->execution = $this->PDOStatement->execute($this->params);
            //$this->timeSpent =  microtime(true) - $begin;
            if(!$this->execution){
                $this->errors[] = new Error("Query Execution Failed". "\nQuery: " . $this->readableSQL());
            }

            return $this->execution;
        } catch( \Exception $e){
            $this->errors[] = new Error($e->getMessage() . "\nQuery: " . $this->readableSQL() );
            return false;
        }
    }

    public function getStatement() : \PDOStatement{
        return $this->PDOStatement;
    }

    public function readableSQL() : string {
        $sql = $this->sql;

        foreach ($this->params as $k => $v){
            $sql = str_replace( ":$k", "'$v'", $sql);       // If has : before params
            $sql = str_replace( "$k", "'$v'", $sql);        // If has no colons before params
        }

        return $sql;
    }

    public function currentPageResults(int $items_per_page = ITEMS_PER_PAGE) : PageResults {

        $page =  route()->pageIndex();

        //Two Queries:
        $qs = new QueryStack();

        $qs->add(new SQLQuery($this->prepareSQLforCount($this->sql), $this->params));

        //Cleaning up this own
        $this->sql = str_replace("limit", "LIMIT", $this->sql);
        $explode = explode("LIMIT", $this->sql);

        $limitString = ($page - 1) * $items_per_page .",". $items_per_page;
        $this->sql = $explode[0] . " LIMIT " . $limitString;

        $qs->add($this);

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

    private function  prepareSQLforCount($string){

        $string = str_replace("from", "FROM", $string);
        $explode = explode("FROM", $string, 2);
        $string = "SELECT COUNT(*) AS total FROM " . $explode[1];


        $string = str_replace("limit", "LIMIT", $string);
        $explode = explode("LIMIT", $string);
        $string = $explode[0];

        return $string;
    }


}