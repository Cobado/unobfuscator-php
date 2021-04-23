<?php

namespace App;

use Lib\CliPrinter;

class Unobfuscator
{
    protected $printer;

    protected $registry = [];

    public function __construct()
    {
        $this->printer = new CliPrinter();
    }

    public function getPrinter()
    {
        return $this->printer;
    }

    public function registerCommand($name, $callable)
    {
        $this->registry[$name] = $callable;
    }

    public function getCommand($command)
    {
        return isset($this->registry[$command]) ? $this->registry[$command] : null;
    }
    
    public function runCommand(array $argv)
    {
        $command_name = "help";

        if (isset($argv[1])) {
            $command_name = $argv[1];
        }

        $command = $this->getCommand($command_name);

        if ($command === null) {
            $this->getPrinter()->display("ERROR: Command \"$command_name\" not found.");
            exit;
        }

        call_user_func($command, $argv);
    }

    public function decode($filename)
    {
        if (isset($filename)) {
            $file_content = file_get_contents($filename);
            $result = $this->decodeContent($file_content);

            $code = eval($result);

            $this->getPrinter()->display($code);
        }
    }

    public function decodeContent(string $content)
    {
        $instructions = explode(";", $content);
    
        $stage = str_replace("eval(", "", $instructions[3]);
        $stage = str_replace("))", ");", $stage);
        $stage = str_replace("\$_D", "return base64_decode", $stage);

        $result = eval($stage);
        $result = str_replace("\$_R=str_replace('__FILE__',\"'\".\$_F.\"'\",\$_X);eval(\$_R);\$_R=0;\$_X=0;", "return \$_X;", $result);
    
        $result = "$instructions[1];$result";
    
        return $result;
    }
}
