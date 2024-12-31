<?php

namespace WalkerChiu\Point;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use WalkerChiu\Point\Models\Entities\Setting;
use WalkerChiu\Point\Models\Entities\Wallet;
use WalkerChiu\Point\Models\Entities\WalletLang;

class WalletTest extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ .'/../migrations');
        $this->withFactories(__DIR__ .'/../../src/database/factories');
    }

    /**
     * To load your package service provider, override the getPackageProviders.
     *
     * @param \Illuminate\Foundation\Application  $app
     * @return Array
     */
    protected function getPackageProviders($app)
    {
        return [\WalkerChiu\Core\CoreServiceProvider::class,
                \WalkerChiu\Point\PointServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
    }

    /**
     * A basic functional test on Wallet.
     *
     * For WalkerChiu\Point\Models\Entities\Wallet
     * 
     * @return void
     */
    public function testWallet()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-point.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-point.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-point.soft_delete', 1);

        $faker = \Faker\Factory::create();

        $user_id = 1;
        DB::table(config('wk-core.table.user'))->insert([
            'id'       => $user_id,
            'name'     => $faker->username,
            'email'    => $faker->email,
            'password' => $faker->password
        ]);

        $db_setting = factory(Setting::class)->create();

        // Give
        $db_morph_1 = factory(Wallet::class)->create(['setting_id' => $db_setting->id, 'user_id' => $user_id]);
        $db_morph_2 = factory(Wallet::class)->create(['setting_id' => $db_setting->id, 'user_id' => $user_id]);
        $db_morph_3 = factory(Wallet::class)->create(['setting_id' => $db_setting->id, 'user_id' => $user_id, 'is_enabled' => 0]);

        // Get records after creation
            // When
            $records = Wallet::all();
            // Then
            $this->assertCount(3, $records);

        // Delete someone
            // When
            $db_morph_2->delete();
            $records = Wallet::all();
            // Then
            $this->assertCount(2, $records);

        // Resotre someone
            // When
            Wallet::withTrashed()
                  ->find($db_morph_2->id)
                  ->restore();
            $record_2 = Wallet::find($db_morph_2->id);
            $records = Wallet::all();
            // Then
            $this->assertNotNull($record_2);
            $this->assertCount(3, $records);

        // Scope query on enabled records
            // When
            $records = Wallet::ofEnabled()
                             ->get();
            // Then
            $this->assertCount(2, $records);

        // Scope query on disabled records
            // When
            $records = Wallet::ofDisabled()
                             ->get();
            // Then
            $this->assertCount(1, $records);
    }
}
