<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('multi_country_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->index();
            $table->string('instance'); // eg, uae, sa
            $table->string('action'); // create, update, delete
            $table->string('status'); // success, failed, pending
            $table->text('error_message')->nullable();
            $table->json('response_data')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('instance');
            $table->index(['product_id', 'instance']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('multi_country_sync_logs');
    }
};

