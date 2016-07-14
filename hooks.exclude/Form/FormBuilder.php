<?php

namespace hooks\Form;


class FormBuilder
{
    public $type = "text";
    public $value = null;
    public $name = null;
    public $label = null;

    public $validate = false;
    public $validationPattern = null;

    public $random = 0;

    public $attributes = [];

    public function __construct(string $name, $type = "text",  $value = null)
    {
        $name = explode(":",$name);
        $this->label = reset($name);
        $this->name = end($name);
        $this->random = rand(0,10e10);

        $this->type     = $type; //May be array....

        if (is_array($value)){
            $this->value    = isset($value["value"]) ? $value["value"] : null;
            unset($value["value"]);
            $this->attributes = $value;
        } else {
            $this->value    = $value;
        }
    }

    public function setAttribute($key, $value){
        $this->attributes[$key] = $value;
        return $this;
    }

    public function build() : string {
        if(is_array($this->type)){
            return $this->buildSelect();
        } else if($this->type == "textarea"){
            return $this->buildTextArea();
        } else {
            return $this->buildInput();
        }
    }

    public function buildLabel() : string {

        $builder = "<label ";

        $builder .= "class='form-label' ";

        $builder .= "for= '{$this->name}-{$this->random}' ";
        $builder .= ">" . ucwords($this->label) ."</label>";

        return $builder;
    }

    public function createOptionsForSelect() :string {
        $optBuilder = "";

        foreach ($this->type as $index => $option){

        /*
            if(Etc::isAssociativeArray($this->type)){
                $value = $index;
            } else {
                $value = $option;
            }
        */

            $value = $index;

            if(
                (is_scalar($this->value) && $this->value == $index && $this->value != null )
                ||
                (is_array($this->value) && in_array($index,$this->value))
            )
            {

                $optBuilder.= "<option value='{$value}' selected>" .$option. "</option>";
            } else {
                $optBuilder.= "<option value='{$value}'>" .$option. "</option>";
            }
        }
        return $optBuilder;
    }

    public function buildSelect() : string {

        $builder  = "<div class='form-group'>";

        $builder .= $this->buildLabel();

        $builder .= "<select ";
        $builder .= "id= '{$this->name}-{$this->random}' ";

        if( isset($this->attributes['multiple']) ){
            $builder .= "name= '{$this->name}[]' ";
        } else {
            $builder .= "name= '{$this->name}' ";
        }

        if(isset($this->attributes["class"])){
            $builder .= "class='" . $this->attributes['class'] . "'";
        } else {
            $builder .= "class='form-control'";
        }

        foreach ($this->attributes as $attr => $value){
            $builder .= "{$attr}= '{$value}'";
        }

        $builder .= ">";

        //Fetch Options.... ;)
        $builder .= $this->createOptionsForSelect();


        $builder .= "</select>";
        $builder .= "</div>";

        return $builder;
    }

    public function buildInput() : string {

        if($this->type == "hidden"){
            $builder  = "<div class='form-group' style='display: none !important;'>";
        } else {
            $builder  = "<div class='form-group'>";
        }

        $builder .= $this->buildLabel();

        $builder .= "<input ";

        if($this->type == "checkbox" || $this->type == "radio"){
            if($this->value == 1){
                $builder .= "checked='checked' ";
            }
        }

        if($this->type == "datetime"){
            if($this->value instanceof \DateTime){
                $this->value = $this->value->format(DATE_FORMAT_LONG_HTML5);
            }
        }

        if($this->type == "date"){
            if($this->value instanceof \DateTime){
                $this->value = $this->value->format(DATE_FORMAT_SHORT_HTML5);
            }
        }

        $builder .= "value= '{$this->value}' ";
        $builder .= "type= '{$this->type}' ";
        $builder .= "id= '{$this->name}-{$this->random}' ";
        $builder .= "name= '{$this->name}' ";

        if(isset($this->attributes["class"])){
            $builder .= "class='" . $this->attributes['class'] . "'";
        } else {
            $builder .= "class='form-control'";
        }

        foreach ($this->attributes as $attr => $value){
            $builder .= "{$attr}= '{$value}'";
        }

        $builder .= "></input>";
        $builder .= "</div>";

        return $builder;
    }

    public function buildTextArea() : string {

        if($this->type == "hidden"){
            $builder  = "<div class='form-group' style='display: none !important;'>";
        } else {
            $builder  = "<div class='form-group'>";
        }

        $builder .= $this->buildLabel();

        $builder .= "<textarea ";
        $builder .= "id= '{$this->name}-{$this->random}' ";
        $builder .= "name= '{$this->name}' ";

        if(isset($this->attributes["class"])){
            $builder .= "class='" . $this->attributes['class'] . "'";
        } else {
            $builder .= "class='form-control'";
        }

        foreach ($this->attributes as $attr => $value){
            $builder .= "{$attr}= '{$value}'";
        }

        $builder .= ">{$this->value}</textarea>";

        $builder .= "</div>";

        return $builder;

    }

    public function __toString()
    {
        return $this->build();
    }


}