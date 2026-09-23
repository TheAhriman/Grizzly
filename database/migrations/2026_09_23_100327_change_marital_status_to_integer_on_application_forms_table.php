<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['single' => 1, 'married' => 2, 'divorced' => 3, 'widowed' => 4] as $status => $value) {
            DB::table('application_forms')->where('marital_status', $status)->update(['marital_status' => $value]);
        }

        Schema::table('application_forms', function (Blueprint $table) {
            $table->unsignedTinyInteger('marital_status')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_forms', function (Blueprint $table) {
            $table->string('marital_status', 30)->change();
        });

        foreach ([1 => 'single', 2 => 'married', 3 => 'divorced', 4 => 'widowed'] as $value => $status) {
            DB::table('application_forms')->where('marital_status', (string) $value)->update(['marital_status' => $status]);
        }
    }
};
