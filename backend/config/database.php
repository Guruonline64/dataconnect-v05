<?php
declare(strict_types=1);
function db(): PDO {
 static $pdo=null; if($pdo instanceof PDO)return $pdo;
 $host=getenv('DB_HOST')?:'127.0.0.1';$name=getenv('DB_NAME')?:'dataconnect';$user=getenv('DB_USER')?:'dataconnect_user';$pass=getenv('DB_PASSWORD')?:'';
 return $pdo=new PDO("mysql:host={$host};dbname={$name};charset=utf8mb4",$user,$pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
}
