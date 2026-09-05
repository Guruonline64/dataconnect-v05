<?php
require __DIR__.'/_bootstrap.php';
$staff=require_role(['staff','admin']);
$d=input(); $id=(int)($d['withdrawal_id']??0); $decision=(string)($d['decision']??''); $reason=trim((string)($d['reason']??''));
if($id<=0 || !in_array($decision,['approve','reject'],true)) out(false,'Valid withdrawal_id and decision are required',[],422);
$pdo=db();
try {
 $pdo->beginTransaction();
 $s=$pdo->prepare('SELECT * FROM withdrawal_requests WHERE id=? FOR UPDATE'); $s->execute([$id]); $w=$s->fetch();
 if(!$w){$pdo->rollBack();out(false,'Withdrawal not found',[],404);}
 if($w['status']!=='pending'){$pdo->rollBack();out(false,'Withdrawal is no longer pending',[],409);}
 if($decision==='reject'){
   $ref='WDREF-'.date('YmdHis').'-'.bin2hex(random_bytes(4));
   $pdo->prepare('UPDATE wallets SET balance=balance+? WHERE user_id=?')->execute([$w['amount'],$w['user_id']]);
   $pdo->prepare('INSERT INTO wallet_ledger(user_id,type,amount,reference,description) VALUES(?,?,?,?,?)')->execute([$w['user_id'],'refund',$w['amount'],$ref,'Withdrawal reserve released #'.$id]);
   $pdo->prepare('UPDATE withdrawal_requests SET status="rejected",reviewed_by=?,reviewed_at=NOW(),reason=? WHERE id=?')->execute([$staff['id'],$reason,$id]);
   notify_user((int)$w['user_id'],'Withdrawal rejected',$reason ?: 'Your withdrawal request was rejected. The reserved amount was returned to your wallet.');
   audit_log((int)$staff['id'],'reject','withdrawal',$id,['reason'=>$reason,'refund_reference'=>$ref]);
   $pdo->commit(); out(true,'Withdrawal rejected and funds returned',['status'=>'rejected','refund_reference'=>$ref]);
 }
 $ref=$w['reference'] ?: ('WD-'.date('YmdHis').'-'.bin2hex(random_bytes(4)));
 $pdo->prepare('INSERT INTO wallet_ledger(user_id,type,amount,reference,description) VALUES(?,?,?,?,?)')->execute([$w['user_id'],'withdrawal',$w['amount'],$ref,'Approved shareholder withdrawal #'.$id]);
 $pdo->prepare('UPDATE withdrawal_requests SET status="approved",reviewed_by=?,reviewed_at=NOW(),reference=? WHERE id=?')->execute([$staff['id'],$ref,$id]);
 notify_user((int)$w['user_id'],'Withdrawal approved','Your withdrawal has been approved and is ready for payout.');
 audit_log((int)$staff['id'],'approve','withdrawal',$id,['reference'=>$ref]);
 $pdo->commit();
 out(true,'Withdrawal approved',['status'=>'approved','reference'=>$ref]);
} catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();out(false,'Unable to process withdrawal',[],500);}
