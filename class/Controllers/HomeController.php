<?php

    namespace Controllers;


    use Framework\MVC\Controller;


    class HomeController extends Controller{


        public function index(){
            return view();
        }

        public function image(){
            return view("home/image");
        }

        public function test(){
            return view();
        }

        public function error404(){
            return view("errors/404")->title("Oops!!! 404 Error");
        }

        public function error500(){
            return view("errors/500")->title("Oops!!! 500 Error");
        }


    }