<?php
namespace hooks\Errors;


use hooks\Storage\FileSystem;
use hooks\Utils\Etc;
use hooks\Utils\GeoLocation;

class Error
{

    public $error, $level, $reference;
    public $file, $line, $function, $class, $debugErrors;

    public function __construct(string $error, int $level = 0)
    {
        $this->error = $error;
        $this->level = $level;
        $this->reference = Etc::getRandomId();
        $this->tryDebugBacktrace();
        $this->handle();
    }

    private function tryDebugBacktrace(){

        if(count(debug_backtrace()) > 1){
            $trace = debug_backtrace()[1];
            $this->file = $trace["file"];
            $this->line = $trace["line"];
            $this->class = $trace["class"];
            $this->function = $trace["function"];
            $this->debugErrors = implode("\n", $trace["args"]);
        }
        if(count(debug_backtrace()) > 2){
            $trace = debug_backtrace()[2];
            $this->file .= "|" . $trace["file"];
            $this->line .= "|" .$trace["line"];
            $this->class .= "|" . $trace["class"];
            $this->function .= "|" . $trace["function"];
            //$this->debugErrors .= "|" . implode("\n", $trace["args"]);
        }

    }

    private function handle(){
        $this->errorLog();
        if($this->level >= 1){
            die($this->generateMessage());
        }
    }

    private function generateMessage(){
        return "<h2>Ooops!</h2>
                We encountered some error with our service. If this was important,please let us know.
                <h4>Your Reference: " . $this->reference . "</h4>";
    }

    public function errorLog(){

        $now = new \DateTime('now');
        $file = "log/" . $now->format("Y-m-d") . ".txt";

        try{
            $logs = FileSystem::get($file);

            $logs .= "Ref# : " . $this->reference . " || \t";
            $logs .= "Level : " . $this->level . "\n";
            $logs .= $now->format("Y-m-d h:i:s A") . " || \t";
            $logs .= "IP: " . GeoLocation::getIP(). " || \t";
            $logs .= "Country: " . GeoLocation::getCountry(). "\n";
            $logs .= GeoLocation::userAgent(). "\n";
            $logs .= "Error: " . $this->error . "\n";
            $logs .= "Debug Errors: " . $this->debugErrors . "\n";
            $logs .= "Error Location: " . $this->file . " in line " . $this->line . "\n";
            $logs .= "--------------------------------------------------------------------------------------------------------------------------\n";

            FileSystem::put($file, $logs);

        } catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }

    }

}