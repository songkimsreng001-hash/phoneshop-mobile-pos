<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('quantity'); // pending | approved | rejected
            $table->text('reason')->nullable()->after('status');
            $table->date('warranty_expires_at')->nullable()->after('reason');
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropColumn(['status', 'reason', 'warranty_expires_at']);
        });
    }
};
