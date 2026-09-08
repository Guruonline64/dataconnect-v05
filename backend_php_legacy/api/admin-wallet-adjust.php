<?php
require __DIR__.'/_bootstrap.php';
$admin=require_role(['admin']);
$d=input();
$userId=(int)($d['user_id']??0); $type=(string)($d['type']??''); $amount=(float)($d['amount']??0); $reason=trim((string)($d['reason']??''));
if($userId<=0 || !in_array($type,['credit','debit'],true) || $amount<=0 || $reason==='') out(false,'User, type, positive amount and reason are required',[],422);
$pdo=db();
try{$pdo->beginTransaction();
 $u=$pdo->prepare('SELECT id FROM users WHERE id=? FOR UPDATE');$u->execute([$userId]);if(!$u->fetch()){$pdo->rollBack();out(false,'User not found',[],404);}
 $w=$pdo->prepare('SELECT balance FROM wallets WHERE user_id=? FOR UPDATE');$w->execute([$userId]);$row=$w->fetch();$balance=(float)($row['balance']??0);
 if($type==='debit' && $balance<$amount){$pdo->rollBack();out(false,'Insufficient wallet balance',[],409);}
 if($row){$pdo->prepare('UPDATE wallets SET balance=balance'.($type==='credit'?'+':'-').' ? WHERE user_id=?')->execute([$amount,$userId]);}
 else {$pdo->prepare('INSERT INTO wallets(user_id,balance) VALUES(?,?)')->execute([$userId,$type==='credit'?$amount:-$amount]);}
 $ref='ADJ-'.date('YmdHis').'-'.bin2hex(random_bytes(4));
 $pdo->prepare('INSERT INTO wallet_ledger(user_id,type,amount,reference,description) VALUES(?,?,?,?,?)')->execute([$userId,$type,$amount,$ref,'Admin adjustment: '.$reason]);
 notify_user($userId,'Wallet updated',($type==='credit'?'₦'.number_format($amount,2).' was credited to':'₦'.number_format($amount,2).' was debited from').' your Data Connect wallet.');
 audit_log((int)$admin['id'],'wallet_'.$type,'user',$userId,['amount'=>$amount,'reason'=>$reason,'reference'=>$ref]);
 $pdo->commit();out(true,'Wallet updated',['reference'=>$ref]);
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();out(false,'Unable to update wallet',[],500);}
