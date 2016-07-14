<?php


namespace hooks\DataBase;


class DBConnection extends \PDO
{

    public function __construct($host = DB_DSN, $db = DB_NAME, $user = DB_USER, $pass = DB_PASS)
    {
        try{
            parent::__construct($host.';dbname='.$db, $user, $pass, [ \PDO::ATTR_PERSISTENT => true ]);
            $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
            $this->setAttribute(\PDO::ATTR_PERSISTENT, true);
        } catch (\Exception $e){
            errorLog("Database Connection Failed");
            die("Database Connection Failed");
        }
    }

}