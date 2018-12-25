<?php

$json_string = file_get_contents('php://input');

echo $json_string;
$obj = json_decode($json_string);
var_dump($obj);
