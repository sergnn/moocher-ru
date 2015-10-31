<?php

$host = 'localhost';
$dbname = '*';
$user = '*';
$pass = '*';

$pdo = new PDO('mysql:host=' . $host . ';dbname=' . $dbname, $user, $pass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));