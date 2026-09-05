<?php
namespace App\Services;

use App\Contracts\DataProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DataConnectService
{
    public function __construct(public DataProvider $provider) {}

    public function register(string $phone, string $username, string $password): array
    {
        return DB::transaction(function () use ($phone, $username, $password) {
            if (DB::table('users')->where('phone', $phone)->orWhere('username', $username)->exists()) {
                abort(response()->json(['success'=>false,'message'=>'Phone or username already exists'], 409));
            }
            $id = DB::table('users')->insertGetId([
                'phone'=>$phone, 'username'=>$username,
                'password_hash'=>password_hash($password, PASSWORD_DEFAULT),
                'role'=>'customer', 'created_at'=>now(),
            ]);
            DB::table('wallets')->insert(['user_id'=>$id,'balance'=>0,'updated_at'=>now()]);
            return ['id'=>$id,'phone'=>$phone,'username'=>$username,'role'=>'customer'];
        });
    }

    public function reference(string $prefix): string
    {
        return $prefix . '-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(8));
    }

    public function notify(int $uid, string $title, string $body): void
    {
        DB::table('notifications')->insert([
            'user_id'=>$uid,'title'=>$title,'body'=>$body,'is_read'=>false,'created_at'=>now(),
        ]);
    }

    public function audit(int $actor, string $action, string $type, ?int $id, array $details=[]): void
    {
        try {
            DB::table('audit_logs')->insert([
                'actor_user_id'=>$actor,'action'=>$action,'entity_type'=>$type,
                'entity_id'=>$id,'details'=>json_encode($details),'created_at'=>now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function debitWallet(int $uid, float $amount, string $reference, string $description): void
    {
        $wallet = DB::table('wallets')->where('user_id',$uid)->lockForUpdate()->first();
        if (!$wallet || (float)$wallet->balance < $amount) {
            abort(response()->json(['success'=>false,'message'=>'Insufficient wallet balance'], 422));
        }
        DB::table('wallets')->where('user_id',$uid)->update([
            'balance'=>DB::raw('balance-'.$amount),'updated_at'=>now()
        ]);
        DB::table('wallet_ledger')->insert([
            'user_id'=>$uid,'type'=>'debit','amount'=>$amount,'reference'=>$reference,
            'description'=>$description,'created_at'=>now()
        ]);
    }

    public function creditWallet(int $uid, float $amount, string $type, string $reference, string $description): void
    {
        DB::table('wallets')->where('user_id',$uid)->update([
            'balance'=>DB::raw('balance+'.$amount),'updated_at'=>now()
        ]);
        DB::table('wallet_ledger')->insert([
            'user_id'=>$uid,'type'=>$type,'amount'=>$amount,'reference'=>$reference,
            'description'=>$description,'created_at'=>now()
        ]);
    }
}
