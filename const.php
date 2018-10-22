<?php

$dir = "../const";
$handler = opendir($dir);
while ((($filename = readdir($handler)) !== false)) {
    if (substr($filename, -4) == '.php') {
        require $dir . '/' . $filename;
    }
}
closedir($handler);
