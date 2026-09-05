<?php
require __DIR__.'/_bootstrap.php';
$staff=require_role(['dispenser','staff','admin']);
$d=input(); $id=(int)($d['request_id']??0); $reason=trim((string)($d['reason']??'Request rejected by staff'));
if($id<=0) out(false,'Valid request_id required',[],422);
$pdo=db();
try {
 $pdo->beginTransaction();
 $s=$pdo->prepare('SELECT * FROM airtime_requests WHERE id=? FOR UPDATE'); $s->execute([$id]); $r=$s->fetch();
 if(!$r){$pdo->rollBack();out(false,'Request not found',[],404);}
 if($r['status']!=='pending'){$pdo->rollBack();out(false,'Request is no longer pending',[],409);}
 $pdo->prepare('UPDATE airtime_requests SET status="rejected",dispenser_id=? WHERE id=?')->execute([$staff['id'],$id]);
 notify_user((int)$r['user_id'],'Airtime request rejected',$reason);
 $pdo->commit();
 out(true,'Request rejected',['request_id'=>$id,'status'=>'rejected']);
} catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();out(false,'Unable to reject request',[],500);}
