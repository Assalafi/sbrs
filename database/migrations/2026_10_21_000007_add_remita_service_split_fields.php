<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::table('payment_types', function(Blueprint $t){ if(!Schema::hasColumn('payment_types','remita_split_enabled'))$t->boolean('remita_split_enabled')->default(false); if(!Schema::hasColumn('payment_types','remita_surcharge'))$t->decimal('remita_surcharge',12,2)->default(0); }); } public function down(): void { Schema::table('payment_types',fn(Blueprint $t)=>$t->dropColumn(['remita_split_enabled','remita_surcharge'])); } };
