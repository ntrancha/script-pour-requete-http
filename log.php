<?php
$arr = get_defined_vars();
$ip=$_SERVER["REMOTE_ADDR"];
$log = "";
foreach ($arr["_SERVER"] as $key1 => $value1) {$log .= "SERVER:$key1=$value1\n";}
foreach ($arr["_POST"]   as $key2 => $value2) {$log .=   "POST:$key2=$value2\n";}
foreach ($arr["_GET"]    as $key3 => $value3) {$log .=    "GET:$key3=$value3\n";}
foreach ($arr["_COOKIE"] as $key4 => $value4) {$log .= "COOKIE:$key4=$value4\n";}
$monfichier = fopen('log.txt', 'w+');
fputs($monfichier, $log);
fclose($monfichier);
?>
