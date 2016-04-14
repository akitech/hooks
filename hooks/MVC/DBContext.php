<?php

namespace hooks\MVC;


use hooks\DataBase\DBHelpers;
use hooks\DataBase\Statements\SelectStatement;
use hooks\Form\FormBuilder;
use hooks\Utils\Etc;
use Models\ViewModels\PageResults;

abstract class DBContext extends DBContextRelational
{

    public function __construct($key = null)
    {
        if($key != null && is_scalar($key) && self::getContextKey() != null){
            $this->find(self::getContextKey(), $key);
        }
        parent::__construct();
    }

    public function improvise(){

    }

    protected function find($prop, $val){
        $data = select()->from(self::getContext())->where([$prop => $val])->first();
        if($data != null){
            $this->refreshDataToItem($data);
        }
    }

    public function __toString() : string{
        return json_encode($this, JSON_PRETTY_PRINT);
    }

    public static function __callStatic($method, $arguments = [])
    {
        if(method_exists(__CLASS__, $method)){
            return call_user_func_array(array(__CLASS__, $method), $arguments);
        }

        $names = explode("where", $method);
        if(count($names) == 2 && $names[0] == ""){

            $prop = $names[1];
            $firstLetterLowerCase =  strtolower($prop[0]) . substr($prop,1);

            if(property_exists(get_called_class(), $firstLetterLowerCase)){

                return self::where([$firstLetterLowerCase => $arguments[0]]);
            }
            //As Passed Test for those who like Property Names beginning with UpperCase
            if(property_exists(get_called_class(), $prop)){

                return self::where([$prop => $arguments[0]]);
            }

        }


        die("Method " . $method . "() does not exist in " . __CLASS__. " at line <strong>" .
            __LINE__ . "</strong> in file <strong>" . __FILE__ . "</strong>");

    }

    public function getScalarData(){
        $data = [];

        $cols = DBHelpers::getColumns(self::getContext());

        foreach ($cols as $col){
            $prop = $col->Field;
            if(property_exists($this, $prop)){
                if(is_scalar($this->$prop) || is_null($this->$prop)){
                    $data[$prop] = $this->$prop;
                } else {
                    $data[$prop] = $this->getScalarValue($this->$prop);
                }
            }
        }

        return $data;
    }

    public function getScalarValue($obj){


        if($obj instanceof \DateTime){
            return $obj->format("Y-m-d H:i:a");
        }

        if(is_object($obj) && property_exists($obj,"contextPrimaryKey")){
            $prop = $obj::$contextPrimaryKey;
            return $obj->$prop;
        }

        return null;
    }

    public function beforeSave(){
        $primaryId = self::getContextKey();
        if($this->$primaryId == ""){
            $this->$primaryId = null;
        }
    }

    public function save() : bool {

        $this->beforeSave();

        $data = $this->getScalarData();

        $key = self::getContextKey();
        $params = [$key => $this->$key];

        $counts = select()->from(self::getContext())->where($params)->rowCount();

        if($counts > 0){
            //Update
            return self::update($data, $params);
        } else {
            //Add : We need last insert ID so we go the legit way, no Statics
            $query = insert($data)->to(self::getContext())->prepareSQL();

            if($query->execute()) {

                if($this->$key == null){
                    $this->$key = $query->lastInsertId();      //to get last insert Id, we need Query
                }

                return true;
            }
        }

        return false;
    }

    public function delete(){
        $key = self::getContextKey();
        $params = [$key => $this->$key];
        return self::deleteWhere($params);
    }

    public function refreshDataToItem($data){
        foreach($data as $k => $v) {
            if (property_exists($this, $k)) {
                $this->$k = $v;
            }
        }
        return $this;
    }
    //Alias of refreshDataToItem
    public function fill($data){
        return $this->refreshDataToItem($data);
    }

    public function collect(array $fields, bool $required = true){
        $data = Etc::parseFields($fields, $required);
        return $this->fill($data);
    }

    /* Static Helper Methods */

    public static function context(){
        return self::$context;
    }

    public static function contextPrimaryKey(){
        return self::$contextPrimaryKey;
    }

    public static function all() : array{
        return self::get()->all();
    }

    public static function currentPageResults() : PageResults{
        return self::get()->currentPageResults();
    }

    public static function exists($array) : bool {
        $counts = select()->from(self::getContext())->where($array)->rowCount();
        return ($counts > 0);
    }

    public static function update(array $data, array $params) : bool{
        return update($data)->to(self::getContext())->where($params)->execute();

    }

    public static function where(array $array = []) : SelectStatement {
        return self::get($array);
    }

    public static function whereId($id) : SelectStatement {
        return self::where([self::getContextKey() => $id]);
    }

    public static function get(array $params = []) : SelectStatement {
        return select()->from(self::getContext())->where($params)->cast(get_called_class());
    }

    public static function put(array $data) : int{
        $query = insert($data)->to(self::getContext())->prepareSQL();
        $query->execute();
        return $query->lastInsertId();      //to get last insert Id, we need Query
    }

    public static function add(array $data) : int {
        return self::put($data);
    }

    public static function deleteWhereId($id) : bool{
        return self::deleteWhere([self::getContextKey() => $id]);
    }

    public static function deleteWhere(array $array) : bool{
        return delete($array)->from(self::getContext())->execute();
    }

    public static function allIndexed() : array
    {
        $key = self::getContextKey();
        $list = self::all();
        $indexed = [];
        foreach ($list as $item){
            $indexed[$item->$key] = $list;
        }

        return $indexed;
    }

    public static function allForSelect(string $id = "id", string $title = "title") {
        $results = select($id . "," . $title)->from(self::getContext())->prepareSQL()->getResults();
        $select = [];
        foreach($results as $result){
            $select[$result->$id] = $result->$title;
        }
        return $select;
    }

    public function inputFor(string $prop, $type = "text", $attributes = null) : string {

        $props = explode(":",$prop); //In case property has Label:property type
        $name = end($props);

        if(property_exists($this, $name)){
            if($attributes == null){
                $attributes = $this->$name;
            } else if(is_array($attributes) && !isset($attributes["value"])) {
                $attributes["value"] = $this->$name;
            }
            return new FormBuilder($prop, $type, $attributes);
        }
        return "Invalid Property";
    }

    public function getId()
    {
        $key = static::getContextKey();
        return $this->$key;
    }


}