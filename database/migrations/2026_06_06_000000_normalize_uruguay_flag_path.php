<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('teams')
            ->where(function ($query) {
                $query->where('short_name', 'URU')
                    ->orWhere('name', 'Uruguay');
            })
            ->update([
                'country_code' => 'URU',
                'flag_path' => 'flags/uru.svg',
            ]);
    }

    public function down(): void
    {
        //
    }
};
