<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

function input(): array {
    $d = json_decode(file_get_contents('php://input') ?: '{}', true);
    return is_array($d) ? $d : [];
}
function out(bool $ok, string $message, array $data=[], int $code=200): never {
    http_response_code($code);
    echo json_encode(['success'=>$ok,'message'=>$message] + $data);
    exit;
}
function bearer(): ?string {
    $h = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    return preg_match('/Bearer\s+(.+)/i', $h, $m) ? trim($m[1]) : null;
}
function require_user(): array {
    $token = bearer();
    if (!$token) out(false,'Authentication required',[],401);
    $p = explode('.', $token, 2);
    if (count($p)!==2) out(false,'Invalid token',[],401);
    [$encoded,$sig]=$p;
    $secret=getenv('JWT_SECRET') ?: 'CHANGE_ME';
    $expected=hash_hmac('sha256',$encoded,$secret);
    if (!hash_equals($expected,$sig)) out(false,'Invalid token',[],401);
    $payload = json_decode(base64_decode(strtr($encoded,'-_','+/')) ?: '', true);
    if (!$payload || ($payload['exp'] ?? 0) < time()) out(false,'Session expired',[],401);
    $stmt=db()->prepare('SELECT id,phone,username,role FROM users WHERE id=?');
    $stmt->execute([$payload['uid']]);
    $u=$stmt->fetch();
    if (!$u) out(false,'User not found',[],401);
    return $u;
}

function require_role(array $roles): array {
    $u=require_user();
    if (!in_array($u['role'],$roles,true)) out(false,'Forbidden',[],403);
    return $u;
}
function notify_user(int $uid,string $title,string $body): void {
    $s=db()->prepare('INSERT INTO notifications(user_id,title,body) VALUES(?,?,?)');
    $s->execute([$uid,$title,$body]);
}

function audit_log(int $actor, string $action, string $entityType, ?int $entityId, array $details=[]): void {
    try {
        $s=db()->prepare('INSERT INTO audit_logs(actor_user_id,action,entity_type,entity_id,details) VALUES(?,?,?,?,?)');
        $s->execute([$actor,$action,$entityType,$entityId,json_encode($details)]);
    } catch(Throwable $e) { /* audit failure must not expose internals */ }
}
