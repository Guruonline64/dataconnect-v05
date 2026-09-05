<?php
require __DIR__.'/_bootstrap.php';
$u=require_user(); $s=db()->prepare('SELECT balance FROM wallets WHERE user_id=?'); $s->execute([$u['id']]); $w=$s->fetch();
out(true,'OK',['balance'=>(float)($w['balance']??0)]);
