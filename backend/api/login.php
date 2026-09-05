<?php
require __DIR__.'/_bootstrap.php';
$d=input(); $phone=trim((string)($d['phone']??'')); $password=(string)($d['password']??'');
$s=db()->prepare('SELECT id,phone,username,password_hash,role FROM users WHERE phone=?'); $s->execute([$phone]); $u=$s->fetch();
if(!$u || !password_verify($password,$u['password_hash'])) out(false,'Invalid phone number or password',[],401);
$payload=['uid'=>(int)$u['id'],'exp'=>time()+86400*7];
$encoded=rtrim(strtr(base64_encode(json_encode($payload)), '+/','-_'),'=');
$secret=getenv('JWT_SECRET') ?: 'CHANGE_ME';
$token=$encoded.'.'.hash_hmac('sha256',$encoded,$secret);
unset($u['password_hash']);
out(true,'Login successful',['token'=>$token,'user'=>$u]);
