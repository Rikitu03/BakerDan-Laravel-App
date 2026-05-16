<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('otps')
            ->select('email', 'purpose')
            ->groupBy('email', 'purpose')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $ids = DB::table('otps')
                ->where('email', $duplicate->email)
                ->where('purpose', $duplicate->purpose)
                ->orderByDesc('otp_id')
                ->pluck('otp_id')
                ->all();

            $idsToDelete = array_slice($ids, 1);

            if ($idsToDelete !== []) {
                DB::table('otps')->whereIn('otp_id', $idsToDelete)->delete();
            }
        }

        Schema::table('otps', function (Blueprint $table): void {
            $table->unique(['email', 'purpose'], 'otps_email_purpose_unique');
        });
    }

    public function down(): void
    {
        Schema::table('otps', function (Blueprint $table): void {
            $table->dropUnique('otps_email_purpose_unique');
        });
    }
};
