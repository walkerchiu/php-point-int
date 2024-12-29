<?php

namespace WalkerChiu\Point\Models\Services;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Exceptions\NotExpectedEntityException;
use WalkerChiu\Core\Models\Exceptions\NotFoundEntityException;
use WalkerChiu\Core\Models\Exceptions\NotInScopeException;
use WalkerChiu\Core\Models\Services\CheckExistTrait;

class RecordService
{
    use CheckExistTrait;

    protected $repository;



    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->repository = App::make(config('wk-core.class.point.recordRepository'));
    }

    /*
    |--------------------------------------------------------------------------
    | Get record
    |--------------------------------------------------------------------------
    */

    /**
     * @param String  $record_id
     * @return Wallet
     *
     * @throws NotFoundEntityException
     */
    public function find(string $record_id)
    {
        $entity = $this->repository->find($record_id);

        if (empty($entity))
            throw new NotFoundEntityException($entity);

        return $entity;
    }

    /**
     * @param Record|String  $source
     * @return Wallet
     *
     * @throws NotExpectedEntityException
     */
    public function findBySource($source)
    {
        if (is_string($source))
            $entity = $this->find($source);
        elseif (is_a($source, config('wk-core.class.point.record')))
            $entity = $source;
        else
            throw new NotExpectedEntityException($source);

        return $entity;
    }



    /*
    |--------------------------------------------------------------------------
    | Operation
    |--------------------------------------------------------------------------
    */

    /**
     * @param Int  $wallet_id
     * @return Collection
     */
    public function listEnabeld(int $wallet_id)
    {
        return $this->repository->listEnabled($wallet_id);
    }

    /**
     * @param Int       $wallet_id
     * @param Float     $value
     * @param Datetime  $end_at
     * @param Bool      $is_enabled
     * @return Record
     */
    public function add(int $wallet_id, float $value, $end_at = null, $is_enabled = true)
    {
        return $this->repository->save([
            'wallet_id'      => $wallet_id,
            'value_original' => $value,
            'value'          => $value,
            'end_at'         => $end_at,
            'is_enabled'     => $is_enabled
        ]);
    }

    /**
     * Update end_at.
     *
     * @param Record|String  $source
     * @param Datetime       $end_at
     * @return Bool
     */
    public function updateEndAt($source, $end_at = null): bool
    {
        $entity = $this->findBySource($source);

        return (bool) $entity->update(['end_at' => $end_at]);
    }

    /**
     * Check if the value is sufficient.
     *
     * @param Record|String  $source
     * @param Float          $value
     * @return Bool
     */
    public function checkValueIsSufficient($source, float $value): bool
    {
        $entity = $this->findBySource($source);

        return $entity->value >= $value;
    }

    /**
     * Update value.
     *
     * @param Record|String  $source
     * @param Float          $value
     * @return Bool
     *
     * @throws NotInScopeException
     */
    public function updateValue($source, float $value): bool
    {
        $entity = $this->findBySource($source);

        if ($value < 0 || $value > $entity->value_original)
            throw new NotInScopeException($entity);

        return (bool) $entity->update(['value' => $value]);
    }

    /**
     * Process value.
     *
     * @param Record|String  $source
     * @param Float          $value
     * @return Bool|Float
     *
     * @throws NotInScopeException
     */
    public function processValue($source, float $value, $force = false)
    {
        $entity = $this->findBySource($source);

        if ($value < 0) {
            throw new NotInScopeException($entity);
        } elseif (
            $force
            && $value > $entity->value
        ) {
            throw new NotInScopeException($entity);
        }

        $result = $entity->value - $value;
        $value_new = max($result, 0);

        $entity->update(['value' => $value_new]);

        return ($result >= 0) ? true : abs($result);
    }
}
