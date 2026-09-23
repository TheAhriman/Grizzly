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
        DB::table('application_forms')
            ->select(['id', 'country_code', 'phone_numbers'])
            ->orderBy('id')
            ->each(function (object $application): void {
                $numbers = json_decode($application->phone_numbers ?? '[]', true) ?: [];
                $phones = array_map(fn (mixed $number): array => [
                    'country_code' => $application->country_code,
                    'number' => (string) $number,
                ], $numbers);

                DB::table('application_forms')
                    ->where('id', $application->id)
                    ->update(['phone_numbers' => $phones === [] ? null : json_encode($phones)]);
            });

        Schema::table('application_forms', function (Blueprint $table) {
            $table->dropColumn('country_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_forms', function (Blueprint $table) {
            $table->string('country_code', 4)->nullable()->after('email');
        });

        DB::table('application_forms')
            ->select(['id', 'phone_numbers'])
            ->orderBy('id')
            ->each(function (object $application): void {
                $phones = json_decode($application->phone_numbers ?? '[]', true) ?: [];
                $countryCode = $phones[0]['country_code'] ?? null;
                $numbers = array_values(array_filter(array_map(
                    fn (mixed $phone): ?string => is_array($phone) ? ($phone['number'] ?? null) : null,
                    $phones,
                )));

                DB::table('application_forms')
                    ->where('id', $application->id)
                    ->update([
                        'country_code' => $countryCode,
                        'phone_numbers' => $numbers === [] ? null : json_encode($numbers),
                    ]);
            });
    }
};
