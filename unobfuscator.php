<?php

// Get file
$filename = $argv[1];
$file_content = file_get_contents($filename);
$result = decodeContent($file_content);

$code = eval($result);

echo "$code\n";

function decodeContent($content) {
    $instructions = explode(";", $content);
    
    $stage = str_replace("eval(", "", $instructions[3]);
    $stage = str_replace("))", ");", $stage);
    $stage = str_replace("\$_D", "return base64_decode", $stage);

    $result = eval($stage);
    $result = str_replace("\$_R=str_replace('__FILE__',\"'\".\$_F.\"'\",\$_X);eval(\$_R);\$_R=0;\$_X=0;", "return \$_X;", $result);
    
    $result = "$instructions[1];$result";
    
    return $result;
}
