<?php

namespace WalkerChiu\Point;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use WalkerChiu\Core\Models\Exceptions\NotInScopeException;
use WalkerChiu\Point\Models\Entities\Setting;
use WalkerChiu\Point\Models\Entities\Wallet;
use WalkerChiu\Point\Models\Repositories\RecordRepository;
use WalkerChiu\Point\Models\Services\RecordService;

class RecordServiceTest extends \Orchestra\Testbench\TestCase
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

        $this->repository = $this->app->make(RecordRepository::class);
        $this->service = $this->app->make(RecordService::class);
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
     * For WalkerChiu\Point\Models\Services\RecordService
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
        $user_id = 1;
        DB::table(config('wk-core.table.user'))->insert([
            'id'       => $user_id,
            'name'     => $faker->username,
            'email'    => $faker->email,
            'password' => $faker->password
        ]);
        $db_wallet = factory(Wallet::class)->create([
            'setting_id' => $db_setting->id,
            'user_id'    => $user_id
        ]);

        // Give
            $this->repository->save([
                'wallet_id'      => $db_wallet->id,
                'value_original' => 10,
                'value'          => 10,
                'is_enabled'     => 1
            ]);
            $this->repository->save([
                'wallet_id'      => $db_wallet->id,
                'value_original' => 10,
                'value'          => 10,
                'is_enabled'     => 0
            ]);

        // listEnabeld
            // When
                $records_1 = $this->repository->get();
                $records_2 = $this->service->listEnabeld($db_wallet->id);
            // Then
                $this->assertCount(2, $records_1);
                $this->assertCount(1, $records_2);

        // add
            // When
                $this->service->add($db_wallet->id, 10);
                $records_1 = $this->repository->get();
                $records_2 = $this->service->listEnabeld($db_wallet->id);
            // Then
                $this->assertCount(3, $records_1);
                $this->assertCount(2, $records_2);

        // updateEndAt
            // When
                $this->service->updateEndAt($records_1[0]->id, Carbon::now()->subDays(1)->format('Y-m-d'));
                $records_2 = $this->service->listEnabeld($db_wallet->id);
            // Then
                $this->assertCount(1, $records_2);

            // When
                $this->service->updateEndAt($records_1[0]->id, Carbon::now()->addDays(1)->format('Y-m-d'));
                $records_2 = $this->service->listEnabeld($db_wallet->id);
            // Then
                $this->assertCount(2, $records_2);

        // checkValueIsSufficient
            // When
                $result = $this->service->checkValueIsSufficient($records_1[0]->id, 10);
            // Then
                $this->assertTrue($result);

            // When
                $result = $this->service->checkValueIsSufficient($records_1[1]->id, 11);
            // Then
                $this->assertTrue(!$result);
    }

    /**
     * Test updateValue function.
     *
     * For WalkerChiu\Point\Models\Services\RecordService
     *
     * @return void
     *
     * @throws NotInScopeException
     */
    public function testUpdateValue()
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
        $user_id = 1;
        DB::table(config('wk-core.table.user'))->insert([
            'id'       => $user_id,
            'name'     => $faker->username,
            'email'    => $faker->email,
            'password' => $faker->password
        ]);
        $db_wallet = factory(Wallet::class)->create([
            'setting_id' => $db_setting->id,
            'user_id'    => $user_id
        ]);

        // Give
            $this->repository->save([
                'wallet_id'      => $db_wallet->id,
                'value_original' => 10,
                'value'          => 10,
                'is_enabled'     => 1
            ]);
            $record = $this->repository->save([
                'wallet_id'      => $db_wallet->id,
                'value_original' => 10,
                'value'          => 10,
                'is_enabled'     => 0
            ]);

        // updateValue
            // When
                $result = $this->service->updateValue($record->id, 9);
            // Then
                $this->assertTrue($result);

            // When
                $result = $this->service->updateValue($record->id, 10);
            // Then
                $this->assertTrue($result);

            // When
                // $this->service->updateValue($record->id, 11);
            // Then
                // NotInScopeException
    }

    /**
     * Test processValue function.
     *
     * For WalkerChiu\Point\Models\Services\RecordService
     *
     * @return void
     *
     * @throws NotInScopeException
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
        $user_id = 1;
        DB::table(config('wk-core.table.user'))->insert([
            'id'       => $user_id,
            'name'     => $faker->username,
            'email'    => $faker->email,
            'password' => $faker->password
        ]);
        $db_wallet = factory(Wallet::class)->create([
            'setting_id' => $db_setting->id,
            'user_id'    => $user_id
        ]);

        // Give
            $this->repository->save([
                'wallet_id'      => $db_wallet->id,
                'value_original' => 10,
                'value'          => 10,
                'is_enabled'     => 1
            ]);
            $record = $this->repository->save([
                'wallet_id'      => $db_wallet->id,
                'value_original' => 10,
                'value'          => 10,
                'is_enabled'     => 0
            ]);

        // processValue
            // When
                $result = $this->service->processValue($record, 9);
            // Then
                $this->assertEquals(1, $result);

            // When
                $result = $this->service->processValue($record, 10);
            // Then
                $this->assertEquals(9, $result);

            // When
                // $this->service->processValue($record, 10, true);
            // Then
                // NotInScopeException
    }
}
