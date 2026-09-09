<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Provider credentials move from plaintext JSON into an encrypted blob
     * (the column type changes because MySQL JSON columns reject encrypted
     * payloads), and the unused is_default flag is dropped.
     */
    public function up(): void
    {
        Schema::table('email_providers', function (Blueprint $table) {
            $table->dropColumn('is_default');
            $table->text('config')->nullable(false)->change();
        });

        DB::table('email_providers')->orderBy('id')->select(['id', 'config'])
            ->chunkById(100, function ($providers): void {
                foreach ($providers as $provider) {
                    if ($provider->config === null || $provider->config === '') {
                        continue;
                    }

                    DB::table('email_providers')
                        ->where('id', $provider->id)
                        ->update(['config' => Crypt::encryptString($provider->config)]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('email_providers')->orderBy('id')->select(['id', 'config'])
            ->chunkById(100, function ($providers): void {
                foreach ($providers as $provider) {
                    if ($provider->config === null || $provider->config === '') {
                        continue;
                    }

                    DB::table('email_providers')
                        ->where('id', $provider->id)
                        ->update(['config' => Crypt::decryptString($provider->config)]);
                }
            });

        Schema::table('email_providers', function (Blueprint $table) {
            $table->json('config')->change();
            $table->boolean('is_default')->default(false);
        });
    }
};
