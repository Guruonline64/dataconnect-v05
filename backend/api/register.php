<?php
require __DIR__.'/_bootstrap.php';
$d=input();
$phone=trim((string)($d['phone']??'')); $username=trim((string)($d['username']??'')); $password=(string)($d['password']??'');
if($phone===''||$username===''||strlen($password)<6) out(false,'Phone, username and a password of at least 6 characters are required',[],422);
$pdo=db();
try {
  $pdo->beginTransaction();
  $s=$pdo->prepare('SELECT id FROM users WHERE phone=? OR username=?'); $s->execute([$phone,$username]);
  if($s->fetch()){ $pdo->rollBack(); out(false,'Phone or username already exists',[],409); }
  $s=$pdo->prepare('INSERT INTO users(phone,username,password_hash) VALUES(?,?,?)');
  $s->execute([$phone,$username,password_hash($password,PASSWORD_DEFAULT)]);
  $uid=(int)$pdo->lastInsertId();
  $pdo->prepare('INSERT INTO wallets(user_id,balance) VALUES(?,0)')->execute([$uid]);
  $pdo->commit();
  out(true,'Account created',['user_id'=>$uid],201);
} catch(Throwable $e){ if($pdo->inTransaction())$pdo->rollBack(); out(false,'Unable to create account',[],500); }
