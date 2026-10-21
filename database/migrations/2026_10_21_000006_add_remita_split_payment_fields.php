<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $columns = [];
        foreach (['base_amount', 'surcharge_amount', 'total_amount', 'split_enabled', 'split_details'] as $column) {
            if (! Schema::hasColumn('payments', $column)) $columns[] = $column;
        }
        if ($columns !== []) {
            Schema::table('payments', function (Blueprint $table) use ($columns): void {
                if (in_array('base_amount', $columns, true)) $table->decimal('base_amount', 12, 2)->nullable()->after('amount');
                if (in_array('surcharge_amount', $columns, true)) $table->decimal('surcharge_amount', 12, 2)->default(0)->after('base_amount');
                if (in_array('total_amount', $columns, true)) $table->decimal('total_amount', 12, 2)->nullable()->after('surcharge_amount');
                if (in_array('split_enabled', $columns, true)) $table->boolean('split_enabled')->default(false)->after('total_amount');
                if (in_array('split_details', $columns, true)) $table->text('split_details')->nullable()->after('split_enabled');
            });
        }
        if (! Schema::hasTable('payment_split_items')) {
            Schema::create('payment_split_items', function (Blueprint $table): void {
                $table->id();
                $table->uuid('payment_id');
                $table->string('line_item_id', 80);
                $table->string('beneficiary_name', 180);
                $table->string('beneficiary_account', 30)->nullable();
                $table->string('bank_code', 20)->nullable();
                $table->decimal('beneficiary_amount', 12, 2);
                $table->boolean('is_primary')->default(false);
                $table->timestamps();
                $table->foreign('payment_id')->references('id')->on('payments')->cascadeOnDelete();
                $table->unique(['payment_id', 'line_item_id']);
            });
        }
        $settings = [
            ['key'=>'remita_split_settings','value'=>json_encode(['enabled'=>false,'beneficiaries'=>[
                ['key'=>'primary','label'=>'e-Revenue collection','account_number'=>'','bank_code'=>'','percentage'=>40],
                ['key'=>'additional_1','label'=>'Additional account 1','account_number'=>'','bank_code'=>'','percentage'=>30],
                ['key'=>'additional_2','label'=>'Additional account 2','account_number'=>'','bank_code'=>'','percentage'=>30],
            ]]),'type'=>'json','group'=>'remita','label'=>'Remita split-payment settings','description'=>'Optional charges and beneficiary allocation for online Remita payments.'],
        ];
        foreach ($settings as $row) {
            \DB::table('settings')->updateOrInsert(['key'=>$row['key']], array_merge($row,['is_public'=>false,'created_at'=>now(),'updated_at'=>now()]));
        }
    }
    public function down(): void { Schema::dropIfExists('payment_split_items'); }
};
