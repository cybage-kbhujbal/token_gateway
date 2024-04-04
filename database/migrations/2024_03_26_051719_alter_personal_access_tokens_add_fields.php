<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('personal_access_tokens', function($table){
        $table->text('access_token')->nullable();
        $table->text('refresh_token')->nullable();
        $table->string('state_id')->nullable();
        // $table->timestamp('expires_at')->nullable();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_access_tokens', function($table){
            $table->dropColumn(['access_token','refresh_token','state_id']);//,'expires_at'
        });
    }
};
