<?php

namespace WalkerChiu\Point\Models\Services;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Exceptions\NotExpectedEntityException;
use WalkerChiu\Core\Models\Exceptions\NotFoundEntityException;
use WalkerChiu\Core\Models\Services\CheckExistTrait;
use WalkerChiu\Point\Models\Services\RecordService;

class WalletService
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
        $this->repository = App::make(config('wk-core.class.point.walletRepository'));
    }

    /*
    |--------------------------------------------------------------------------
    | Get wallet
    |--------------------------------------------------------------------------
    */

    /**
     * @param Int  $wallet_id
     * @return Wallet
     *
     * @throws NotFoundEntityException
     */
    public function find(int $wallet_id)
    {
        $entity = $this->repository->find($wallet_id);

        if (empty($entity))
            throw new NotFoundEntityException($entity);

        return $entity;
    }

    /**
     * @param Wallet|Int  $source
     * @return Wallet
     *
     * @throws NotExpectedEntityException
     */
    public function findBySource($source)
    {
        if (is_integer($source))
            $entity = $this->find($source);
        elseif (is_a($source, config('wk-core.class.point.wallet')))
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
     * Check if the balance is sufficient.
     *
     * @param Wallet|Int  $source
     * @param Float       $value
     * @return Bool
     */
    public function checkBalanceIsSufficient($source, float $value): bool
    {
        $entity = $this->findBySource($source);

        return $entity->value >= $value;
    }

    /**
     * Update value.
     *
     * @param Wallet|Int  $source
     * @param Float       $value
     * @return Bool
     */
    public function updateValue($source, float $value): bool
    {
        $entity = $this->findBySource($source);

        return (bool) $entity->update(['value' => $value]);
    }

    /**
     * Update records.
     *
     * @param Wallet|Int  $source
     * @param Float       $value
     * @return Bool|Float
     */
    public function updateRecord($source, float $value)
    {
        $entity = $this->findBySource($source);

        $service = new RecordService();
        $list = $service->listEnabeld($entity->id);
        if ($list->isEmpty())
            return false;

        $value_remaining = $value;
        foreach ($list as $item) {
            $value_remaining = $service->processValue($item, $value_remaining);
            if ($value_remaining === true)
                return true;
        }

        return $value_remaining;
    }

    /**
     * Process value.
     *
     * @param Wallet|Int  $source
     * @param Float       $value
     * @return Bool
     */
    public function processValue($source, float $value): bool
    {
        $value_remaining = $this->updateRecord($source, $value);

        $entity = $this->findBySource($source);

        return (bool) $entity->update(['value' => ($entity->value - $value)]);
    }
}
