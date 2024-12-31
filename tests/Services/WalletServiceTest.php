<?php

namespace WalkerChiu\Point;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use WalkerChiu\Point\Models\Entities\Record;
use WalkerChiu\Point\Models\Entities\Setting;
use WalkerChiu\Point\Models\Entities\Wallet;
use WalkerChiu\Point\Models\Repositories\WalletRepository;
use WalkerChiu\Point\Models\Services\WalletService;

class WalletServiceTest extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    protected $repository;
    protected $service;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        //$this->loadLaravelMigrations(['--database' => 'mysql']);
        $this->loadMigrationsFrom(__DIR__ .'/../migrations');
        $this->withFactories(__DIR__ .'/../../src/database/factories');

        $this->repository = $this->app->make(WalletRepository::class);
        $this->service = $this->app->make(WalletService::class);
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
     * Test basic function.
     *
     * For WalkerChiu\Point\Models\Services\WalletService
     *
     * @return void
     */
    public function testBasicFunction()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-point.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-point.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-point.soft_delete', 1);

        $faker = \Faker\Factory::create();

        $db_setting = factory(Setting::class)->create();
        DB::table(config('wk-core.table.user'))->insert([
            'name'     => $faker->username,
            'email'    => $faker->email,
            'password' => $faker->password
        ]);

        // Give
            $db_wallet = factory(Wallet::class)->create([
                'setting_id' => $db_setting->id,
                'user_id'    => 1,
                'value'      => 10,
                'is_enabled' => 1
            ]);

        // When
            $records = $this->repository->get();
        // Then
            $this->assertCount(1, $records);

        // checkBalanceIsSufficient
            // When
                $result = $this->service->checkBalanceIsSufficient($db_wallet, 9);
            // Then
                $this->assertTrue($result);

            // When
                $result = $this->service->checkBalanceIsSufficient($db_wallet, 10);
            // Then
                $this->assertTrue($result);

            // When
                $result = $this->service->checkBalanceIsSufficient($db_wallet, 11);
            // Then
                $this->assertTrue(!$result);

        // updateValue
            // When
                $result = $this->service->updateValue($db_wallet, 9);
            // Then
                $this->assertEquals(9, $result);
    }

    /**
     * Test updateRecord function.
     *
     * For WalkerChiu\Point\Models\Services\WalletService
     *
     * @return void
     */
    public function testUpdateRecord()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-point.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-point.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-point.soft_delete', 1);

        $faker = \Faker\Factory::create();

        $db_setting = factory(Setting::class)->create();
        DB::table(config('wk-core.table.user'))->insert([
            'name'     => $faker->username,
            'email'    => $faker->email,
            'password' => $faker->password
        ]);

        // Empty
            // Give
                $db_wallet = factory(Wallet::class)->create([
                    'setting_id' => $db_setting->id,
                    'user_id'    => 1,
                    'value'      => 10,
                    'is_enabled' => 1
                ]);

            // When
                $result = $this->service->updateRecord(1, 1);
            // Then
                $this->assertTrue(!$result);

        // No Enabled
            // Give
                factory(Record::class)->create([
                    'wallet_id'      => $db_wallet->id,
                    'value_original' => 10,
                    'value'          => 10,
                    'is_enabled'     => 0
                ]);

            // When
                $result = $this->service->updateRecord($db_wallet, 1);
            // Then
                $this->assertTrue(!$result);

        // Sufficient
            // Give
                factory(Record::class)->create([
                    'wallet_id'      => $db_wallet->id,
                    'value_original' => 10,
                    'value'          => 10,
                    'is_enabled'     => 1
                ]);

            // When
                $result = $this->service->updateRecord($db_wallet, 1);
            // Then
                $this->assertTrue($result);

            // When
                $result = $this->service->updateRecord($db_wallet, 10);
            // Then
                $this->assertEquals(1, $result);
    }

    /**
     * Test processValue function.
     *
     * For WalkerChiu\Point\Models\Services\WalletService
     *
     * @return void
     */
    public function testProcessValue()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-point.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-point.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-point.soft_delete', 1);

        $faker = \Faker\Factory::create();

        $db_setting = factory(Setting::class)->create();
        DB::table(config('wk-core.table.user'))->insert([
            'name'     => $faker->username,
            'email'    => $faker->email,
            'password' => $faker->password
        ]);
        $db_wallet = factory(Wallet::class)->create([
            'setting_id' => $db_setting->id,
            'user_id'    => 1,
            'value'      => 10,
            'is_enabled' => 1
        ]);
            // Give
                factory(Record::class)->create([
                    'wallet_id'      => $db_wallet->id,
                    'value_original' => 10,
                    'value'          => 10,
                    'is_enabled'     => 1
                ]);

            // When
                $result = $this->service->processValue($db_wallet, 1);
            // Then
                $this->assertTrue($result);

            // When
                $result = $this->service->processValue($db_wallet, 11);
            // Then
                $this->assertTrue($result);

            // When
                $record = Wallet::find(1);
            // Then
                $this->assertEquals(-2, $record->value);
    }
}
