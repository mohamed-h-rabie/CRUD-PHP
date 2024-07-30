<?php
definE('DB_HOST', 'localhost');
definE('DB_USER', 'mohamed');
definE('DB_PASS', '123456');
definE('DB_NAME', 'todolist');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if($conn->connect_error){
    die ('connextion faile' .$conn->connect_error) ;
};

?>