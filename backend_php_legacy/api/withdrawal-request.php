<?php
require __DIR__.'/_bootstrap.php';
$u=require_user(); $d=input(); $amount=(float)($d['amount']??0);
$allowed=[500,1000,2000,5000,10000];
if(!in_array($amount,$allowed,true)) out(false,'Invalid withdrawal amount',[],422);
$pdo=db();
try {
  $pdo->beginTransaction();
  $s=$pdo->prepare("SELECT COUNT(*) FROM share_holdings WHERE user_id=? AND status='active'"); $s->execute([$u['id']]);
  if((int)$s->fetchColumn()===0){$pdo->rollBack();out(false,'Withdrawal is available only to active shareholders',[],403);}
  $s=$pdo->prepare('SELECT balance FROM wallets WHERE user_id=? FOR UPDATE'); $s->execute([$u['id']]); $w=$s->fetch();
  if(!$w || (float)$w['balance']<$amount){$pdo->rollBack();out(false,'Insufficient available balance',[],422);}
  $s=$pdo->prepare("SELECT COUNT(*) FROM withdrawal_requests WHERE user_id=? AND status='pending'"); $s->execute([$u['id']]);
  if((int)$s->fetchColumn()>0){$pdo->rollBack();out(false,'A withdrawal request is already pending',[],409);}
  $ref='WDREQ-'.date('YmdHis').'-'.bin2hex(random_bytes(4));
  $pdo->prepare('UPDATE wallets SET balance=balance-? WHERE user_id=?')->execute([$amount,$u['id']]);
  $pdo->prepare('INSERT INTO wallet_ledger(user_id,type,amount,reference,description) VALUES(?,?,?,?,?)')->execute([$u['id'],'debit',$amount,$ref,'Withdrawal reserve']);
  $pdo->prepare('INSERT INTO withdrawal_requests(user_id,amount,status,reference) VALUES(?,?,?,?)')->execute([$u['id'],$amount,'pending',$ref]);
  notify_user((int)$u['id'],'Withdrawal submitted','Your withdrawal request is pending staff approval.');
  $id=(int)$pdo->lastInsertId(); $pdo->commit();
  out(true,'Withdrawal request submitted',['withdrawal_id'=>$id,'reference'=>$ref,'status'=>'pending']);
} catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();out(false,'Unable to submit withdrawal request',[],500);}
