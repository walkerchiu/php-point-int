<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateWkPointTable extends Migration
{
    public function up()
    {
        Schema::create(config('wk-core.table.point.settings'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->nullableMorphs('host');
            $table->string('serial')->nullable();
            $table->string('identifier');
            $table->unsignedDecimal('exchange_rate')->default(1);
            $table->boolean('is_enabled')->default(0);

            $table->timestampsTz();
            $table->softDeletes();

            $table->index('identifier');
            $table->index('is_enabled');
        });
        if (!config('wk-point.onoff.core-lang_core')) {
            Schema::create(config('wk-core.table.point.settings_lang'), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->morphs('morph');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('code');
                $table->string('key');
                $table->text('value')->nullable();
                $table->boolean('is_current')->default(1);

                $table->timestampsTz();
                $table->softDeletes();

                $table->foreign('user_id')->references('id')
                    ->on(config('wk-core.table.user'))
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            });
        }

        Schema::create(config('wk-core.table.point.wallets'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('setting_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('value');
            $table->boolean('is_enabled')->default(1);

            $table->timestampsTz();
            $table->softDeletes();

            $table->foreign('setting_id')->references('id')
                  ->on(config('wk-core.table.point.settings'))
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('user_id')->references('id')
                  ->on(config('wk-core.table.user'))
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->index('is_enabled');
        });

        Schema::create(config('wk-core.table.point.records'), function (Blueprint $table) {
            $table->uuid('id');
            $table->unsignedBigInteger('wallet_id');
            $table->unsignedDecimal('value_original', config('wk-point.unsigned_decimal.precision'), config('wk-point.unsigned_decimal.scale'));
            $table->unsignedDecimal('value', config('wk-point.unsigned_decimal.precision'), config('wk-point.unsigned_decimal.scale'));
            $table->timestampTz('end_at')->nullable();
            $table->boolean('is_enabled')->default(1);

            $table->timestampsTz();
            $table->softDeletes();

            $table->foreign('wallet_id')->references('id')
                  ->on(config('wk-core.table.point.wallets'))
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->primary('id');
            $table->index('end_at');
            $table->index('is_enabled');
        });
    }

    public function down() {
        Schema::dropIfExists(config('wk-core.table.point.records'));
        Schema::dropIfExists(config('wk-core.table.point.wallets'));
        Schema::dropIfExists(config('wk-core.table.point.settings_lang'));
        Schema::dropIfExists(config('wk-core.table.point.settings'));
    }
}
