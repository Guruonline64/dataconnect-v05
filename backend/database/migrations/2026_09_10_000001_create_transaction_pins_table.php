<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('transaction_pins', function(Blueprint $t){ $t->id(); $t->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete(); $t->string('pin_hash'); $t->unsignedTinyInteger('failed_attempts')->default(0); $t->timestamp('locked_until')->nullable(); $t->timestamps(); }); }
 public function down(): void { Schema::dropIfExists('transaction_pins'); }
};
