<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function(Blueprint $t) {
            $t->id();
            $t->string('phone',20)->unique();
            $t->string('username',80)->unique();
            $t->string('password_hash');
            $t->enum('role',['customer','marketer','staff','admin','dispenser'])->default('customer');
            $t->string('api_token',64)->nullable()->unique();
            $t->timestamp('token_expires_at')->nullable();
            $t->timestamps();
        });
        Schema::create('wallets', function(Blueprint $t) {
            $t->unsignedBigInteger('user_id')->primary();
            $t->decimal('balance',14,2)->default(0);
            $t->timestamp('updated_at')->useCurrent();
            $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
        Schema::create('wallet_ledger', function(Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->enum('type',['credit','debit','refund','share_return','withdrawal']);
            $t->decimal('amount',14,2); $t->string('reference',100)->unique();
            $t->string('description',255); $t->timestamp('created_at')->useCurrent();
            $t->index(['user_id','created_at']);
        });
        Schema::create('data_plans', function(Blueprint $t) {
            $t->id(); $t->string('network',30); $t->string('name',80);
            $t->decimal('price',14,2); $t->string('data_amount',40);
            $t->integer('validity_days'); $t->boolean('active')->default(true);
            $t->timestamps();
        });
        Schema::create('data_orders', function(Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('network',30); $t->string('plan_name',80); $t->decimal('amount',14,2);
            $t->string('recipient_phone',20); $t->string('provider_reference',120)->nullable();
            $t->enum('status',['pending','processing','successful','failed','refunded'])->default('pending');
            $t->timestamp('created_at')->useCurrent(); $t->index(['user_id','created_at']);
        });
        Schema::create('airtime_requests', function(Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('network',30); $t->decimal('amount',14,2); $t->string('recipient_phone',20);
            $t->enum('status',['pending','approved','rejected','credited'])->default('pending');
            $t->unsignedBigInteger('dispenser_id')->nullable(); $t->timestamp('created_at')->useCurrent();
            $t->timestamp('approved_at')->nullable();
        });
        Schema::create('share_packages', function(Blueprint $t) {
            $t->id(); $t->string('name',80); $t->decimal('investment_amount',14,2);
            $t->decimal('daily_return',14,2); $t->integer('duration_days'); $t->boolean('active')->default(true);
        });
        Schema::create('share_holdings', function(Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->unsignedBigInteger('package_id'); $t->enum('status',['pending','active','completed','cancelled'])->default('pending');
            $t->timestamp('purchased_at')->nullable(); $t->foreign('package_id')->references('id')->on('share_packages');
        });
        Schema::create('withdrawal_requests', function(Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->decimal('amount',14,2); $t->enum('status',['pending','approved','rejected','paid'])->default('pending');
            $t->timestamp('created_at')->useCurrent(); $t->string('reference',100)->nullable()->unique();
            $t->unsignedBigInteger('reviewed_by')->nullable(); $t->timestamp('reviewed_at')->nullable();
            $t->string('reason',255)->nullable();
        });
        Schema::create('marketers', function(Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $t->string('marketer_id',40)->unique(); $t->string('name',120); $t->string('location',120);
            $t->decimal('monthly_pay',14,2)->default(0); $t->string('picture_path',255)->nullable();
            $t->string('guarantor_name',120); $t->string('guarantor_phone',20);
            $t->decimal('account_requirement',14,2)->default(3250); $t->decimal('minimum_gb',8,2)->default(12);
            $t->enum('approval_status',['pending','approved','rejected'])->default('pending');
            $t->unsignedBigInteger('reviewed_by')->nullable(); $t->timestamp('reviewed_at')->nullable();
        });
        Schema::create('notifications', function(Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('title',150); $t->string('body',500); $t->boolean('is_read')->default(false);
            $t->timestamp('created_at')->useCurrent();
        });
        Schema::create('staff_messages', function(Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('staff_id'); $t->unsignedBigInteger('customer_id')->nullable();
            $t->text('message'); $t->timestamp('created_at')->useCurrent();
        });
        Schema::create('audit_logs', function(Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('actor_user_id'); $t->string('action',100);
            $t->string('entity_type',50); $t->unsignedBigInteger('entity_id')->nullable(); $t->json('details')->nullable();
            $t->timestamp('created_at')->useCurrent(); $t->index(['actor_user_id','created_at']);
        });
        Schema::create('share_return_ledger', function(Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('holding_id'); $t->unsignedBigInteger('user_id');
            $t->date('return_date'); $t->decimal('amount',12,2); $t->string('reference',100)->unique();
            $t->timestamp('created_at')->useCurrent(); $t->unique(['holding_id','return_date']);
        });

        DB::table('share_packages')->insert([
            ['name'=>'₦10,000 Share','investment_amount'=>10000,'daily_return'=>250,'duration_days'=>90,'active'=>true],
            ['name'=>'₦20,000 Share','investment_amount'=>20000,'daily_return'=>500,'duration_days'=>90,'active'=>true],
            ['name'=>'₦30,000 Share','investment_amount'=>30000,'daily_return'=>750,'duration_days'=>90,'active'=>true],
            ['name'=>'₦40,000 Share','investment_amount'=>40000,'daily_return'=>1000,'duration_days'=>90,'active'=>true],
            ['name'=>'₦50,000 Share','investment_amount'=>50000,'daily_return'=>1500,'duration_days'=>90,'active'=>true],
            ['name'=>'₦60,000 Share','investment_amount'=>60000,'daily_return'=>1800,'duration_days'=>92,'active'=>true],
        ]);
        DB::table('data_plans')->insert([
            ['network'=>'MTN','name'=>'500MB','price'=>700,'data_amount'=>'500MB','validity_days'=>7,'active'=>true],
            ['network'=>'MTN','name'=>'1GB','price'=>1350,'data_amount'=>'1GB','validity_days'=>30,'active'=>true],
            ['network'=>'MTN','name'=>'2GB','price'=>2700,'data_amount'=>'2GB','validity_days'=>30,'active'=>true],
            ['network'=>'MTN','name'=>'3GB','price'=>4050,'data_amount'=>'3GB','validity_days'=>30,'active'=>true],
            ['network'=>'MTN','name'=>'5GB','price'=>6750,'data_amount'=>'5GB','validity_days'=>30,'active'=>true],
            ['network'=>'Airtel','name'=>'500MB','price'=>700,'data_amount'=>'500MB','validity_days'=>7,'active'=>true],
            ['network'=>'Airtel','name'=>'1GB','price'=>1350,'data_amount'=>'1GB','validity_days'=>30,'active'=>true],
            ['network'=>'Airtel','name'=>'2GB','price'=>2700,'data_amount'=>'2GB','validity_days'=>30,'active'=>true],
            ['network'=>'Airtel','name'=>'3GB','price'=>4050,'data_amount'=>'3GB','validity_days'=>30,'active'=>true],
            ['network'=>'Airtel','name'=>'5GB','price'=>6750,'data_amount'=>'5GB','validity_days'=>30,'active'=>true],
            ['network'=>'Glo','name'=>'500MB','price'=>650,'data_amount'=>'500MB','validity_days'=>7,'active'=>true],
            ['network'=>'Glo','name'=>'1GB','price'=>1300,'data_amount'=>'1GB','validity_days'=>30,'active'=>true],
            ['network'=>'Glo','name'=>'2GB','price'=>2600,'data_amount'=>'2GB','validity_days'=>30,'active'=>true],
            ['network'=>'Glo','name'=>'3GB','price'=>3900,'data_amount'=>'3GB','validity_days'=>30,'active'=>true],
            ['network'=>'Glo','name'=>'5GB','price'=>6500,'data_amount'=>'5GB','validity_days'=>30,'active'=>true],
            ['network'=>'9mobile','name'=>'500MB','price'=>700,'data_amount'=>'500MB','validity_days'=>7,'active'=>true],
            ['network'=>'9mobile','name'=>'1GB','price'=>1300,'data_amount'=>'1GB','validity_days'=>30,'active'=>true],
            ['network'=>'9mobile','name'=>'2GB','price'=>2600,'data_amount'=>'2GB','validity_days'=>30,'active'=>true],
            ['network'=>'9mobile','name'=>'3GB','price'=>3900,'data_amount'=>'3GB','validity_days'=>30,'active'=>true],
            ['network'=>'9mobile','name'=>'5GB','price'=>6500,'data_amount'=>'5GB','validity_days'=>30,'active'=>true],
        ]);
    }

    public function down(): void
    {
        foreach (['share_return_ledger','audit_logs','staff_messages','notifications','marketers','withdrawal_requests','share_holdings','share_packages','airtime_requests','data_orders','data_plans','wallet_ledger','wallets','users'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
