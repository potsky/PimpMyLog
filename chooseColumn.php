<?php
    
    $str_json = file_get_contents('php://input');
    $jsonFile = fopen('myJson.json','w+');
    fwrite($jsonFile,$str_json);
    fclose($jsonFile);

?>