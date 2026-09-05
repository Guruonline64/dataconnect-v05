<?php
require __DIR__.'/_bootstrap.php';
$staff=require_role(['staff','admin']);
$d=input(); $id=(int)($d['marketer_id']??0); $decision=(string)($d['decision']??'');
if($id<=0 || !in_array($decision,['approve','reject'],true)) out(false,'Valid marketer_id and decision are required',[],422);
$status=$decision==='approve'?'approved':'rejected';
$pdo=db(); $s=$pdo->prepare('UPDATE marketers SET status=?,reviewed_by=?,reviewed_at=NOW() WHERE id=? AND status="pending"');
$s->execute([$status,$staff['id'],$id]);
if($s->rowCount()===0) out(false,'Marketer application not found or already reviewed',[],409);
$s=$pdo->prepare('SELECT user_id FROM marketers WHERE id=?');$s->execute([$id]);$m=$s->fetch();
notify_user((int)$m['user_id'],'Marketer application '.$status,'Your marketer application has been '.$status.'.');
audit_log((int)$staff['id'],$status,'marketer',$id);
out(true,'Marketer updated',['status'=>$status]);
