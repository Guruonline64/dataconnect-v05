<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\DataConnectService;

class PostDailyReturns extends Command
{
    protected $signature = 'dataconnect:post-daily-returns';
    protected $description = 'Post one daily share return per active holding';

    public function handle(DataConnectService $svc): int
    {
        $today=now()->toDateString(); $count=0;
        DB::transaction(function() use($svc,$today,&$count) {
            $rows=DB::table('share_holdings as h')->join('share_packages as p','p.id','=','h.package_id')
                ->where('h.status','active')->lockForUpdate()
                ->select('h.id','h.user_id','h.purchased_at','p.daily_return','p.duration_days')->get();
            foreach($rows as $h) {
                $days=floor((now()->timestamp-strtotime($h->purchased_at))/86400);
                if ($days<0 || $days >= $h->duration_days) continue;
                if (DB::table('share_return_ledger')->where('holding_id',$h->id)->where('return_date',$today)->exists()) continue;
                $ref=$svc->reference('SHRET');
                $svc->creditWallet($h->user_id,$h->daily_return,'share_return',$ref,'Daily share return for holding #'.$h->id);
                DB::table('share_return_ledger')->insert(['holding_id'=>$h->id,'user_id'=>$h->user_id,'return_date'=>$today,'amount'=>$h->daily_return,'reference'=>$ref,'created_at'=>now()]);
                $svc->notify($h->user_id,'Share return credited','Your daily share return has been credited to your Data Connect wallet.');
                $count++;
            }
        });
        $this->info("Credited {$count} daily returns.");
        return self::SUCCESS;
    }
}
