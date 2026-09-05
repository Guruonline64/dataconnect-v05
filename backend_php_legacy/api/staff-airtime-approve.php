<?php
require __DIR__.'/_bootstrap.php';
$staff=require_role(['dispenser','staff','admin']);
$d=input(); $id=(int)($d['request_id']??0);
if($id<=0) out(false,'Valid request_id required',[],422);
$pdo=db();
try {
 $pdo->beginTransaction();
 $s=$pdo->prepare('SELECT * FROM airtime_requests WHERE id=? FOR UPDATE'); $s->execute([$id]); $r=$s->fetch();
 if(!$r){$pdo->rollBack();out(false,'Request not found',[],404);}
 if($r['status']!=='pending'){$pdo->rollBack();out(false,'Request is no longer pending',[],409);}
 $pdo->prepare('UPDATE airtime_requests SET status="approved",dispenser_id=?,approved_at=NOW() WHERE id=?')->execute([$staff['id'],$id]);
 notify_user((int)$r['user_id'],'Airtime request approved','Your airtime request has been approved and is being processed.');
 $pdo->commit();
 out(true,'Request approved',['request_id'=>$id,'status'=>'approved']);
} catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();out(false,'Unable to approve request',[],500);}
