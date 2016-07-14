<?php


namespace hooks\MVC;


use hooks\MVC\TemplateEngine\Engine;
use hooks\MVC\TemplateEngine\FileSystemLoader;
use hooks\Storage\FileSystem;
use hooks\Storage\Globals;

class View extends MinifyHelper
{

    private $title = BASE_URL,
            $metaContent = "",
            $author = "",
            $keywords = "",
            $registeredVariables = [];

    public $view;

    public function __construct($view = null, $vars = [])
    {
        $this->view = $view;
        $this->pass($vars);
    }

    public function prepare()
    {

    }

    public function render(){
        if($this->view === null){
            self::includeMVCView();
        } else {
            $filePath = BASE_DIR . "/Views/" .  $this->view . ".php";
            self::includeRequestedView($filePath);
        }

    }

    private function includeRequestedView($filePath){
        if(FileSystem::exists($filePath)){
            self::printView($filePath);
        }
    }

    private function includeMVCView(){

        $controller = strtolower(Globals::getItem("controller"));
        $method = strtolower(Globals::getItem("method"));

        $filePath = BASE_DIR . "/Views/" .  $controller . "/" . $method . ".php";
        $filePath = str_replace("\\", DIRECTORY_SEPARATOR, $filePath);

        if(!FileSystem::exists($filePath)){
            errorLog("View not found for " . $controller . "/" . $method , 2);
        }

        self::includeRequestedView($filePath);
    }

    public function printView($filePath){


        if(!FileSystem::exists(BASE_DIR . "/Views/.cache")){
            FileSystem::makeDirectory(BASE_DIR . "/Views/.cache");
        }

        $razr = new Engine(new FileSystemLoader(), BASE_DIR . "/Views/.cache" );

        $page = new \stdClass();
        $page->title = $this->title;
        $page->author = $this->author;
        $page->metaContent = $this->metaContent;

        $this->registeredVariables["MetaPageDetails"] = $page;

        echo $razr->render($filePath, $this->registeredVariables);
    }


    public function title($title)
    {
        $this->title = $title;
        return $this;
    }


    public function meta($metaContent)
    {
        $this->metaContent = $metaContent;
        return $this;
    }


    public function author($author)
    {
        $this->author = $author;
        return $this;
    }


    public function keywords($keywords)
    {
        $this->keywords = $keywords;
        return $this;
    }


    public function pass($vars)
    {
        foreach($vars as $key => $val){
            $this->registeredVariables[$key] = $val;
        }
        return $this;
    }






}