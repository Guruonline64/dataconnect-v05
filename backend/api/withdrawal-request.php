<?php
require __DIR__.'/_bootstrap.php';
$u=require_user(); $d=input(); $amount=(float)($d['amount']??0);
$allowed=[500,1000,2000,5000,10000];
if(!in_array($amount,$allowed,true)) out(false,'Invalid withdrawal amount',[],422);
$pdo=db();
$s=$pdo->prepare("SELECT COUNT(*) FROM share_holdings WHERE user_id=? AND status='active'");$s->execute([$u['id']]);
if((int)$s->fetchColumn()===0) out(false,'Withdrawal is available only to active shareholders',[],403);
$s=$pdo->prepare('SELECT balance FROM wallets WHERE user_id=?');$s->execute([$u['id']]);$w=$s->fetch();
if(!$w || (float)$w['balance']<$amount) out(false,'Insufficient available balance',[],422);
$s=$pdo->prepare("SELECT COUNT(*) FROM withdrawal_requests WHERE user_id=? AND status='pending'");$s->execute([$u['id']]);
if((int)$s->fetchColumn()>0) out(false,'A withdrawal request is already pending',[],409);
$s=$pdo->prepare('INSERT INTO withdrawal_requests(user_id,amount,status) VALUES(?,?,?)');$s->execute([$u['id'],$amount,'pending']);
out(true,'Withdrawal request submitted',['withdrawal_id'=>(int)$pdo->lastInsertId(),'status'=>'pending']);
