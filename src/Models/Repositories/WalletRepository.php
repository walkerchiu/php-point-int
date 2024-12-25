<?php

namespace WalkerChiu\Point\Models\Repositories;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Forms\FormTrait;
use WalkerChiu\Core\Models\Repositories\Repository;
use WalkerChiu\Core\Models\Repositories\RepositoryTrait;
use WalkerChiu\Core\Models\Services\PackagingFactory;

class WalletRepository extends Repository
{
    use FormTrait;
    use RepositoryTrait;

    protected $instance;



    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->instance = App::make(config('wk-core.class.point.wallet'));
    }

    /**
     * @param Array  $data
     * @param Bool   $is_enabled
     * @param Bool   $auto_packing
     * @return Array|Collection|Eloquent
     */
    public function list(array $data, $is_enabled = null, $auto_packing = false)
    {
        $instance = $this->instance;
        if ($is_enabled === true)      $instance = $instance->ofEnabled();
        elseif ($is_enabled === false) $instance = $instance->ofDisabled();

        $data = array_map('trim', $data);
        $repository = $instance->when($data, function ($query, $data) {
                                    return $query->unless(empty($data['id']), function ($query) use ($data) {
                                                return $query->where('id', $data['id']);
                                            })
                                            ->unless(empty($data['setting_id']), function ($query) use ($data) {
                                                return $query->where('setting_id', $data['setting_id']);
                                            })
                                            ->unless(empty($data['user_id']), function ($query) use ($data) {
                                                return $query->where('user_id', $data['user_id']);
                                            })
                                            ->unless(empty($data['value']), function ($query) use ($data) {
                                                return $query->where('value', $data['value']);
                                            });
                                    })
                                ->orderBy('updated_at', 'DESC');

        if ($auto_packing) {
            $factory = new PackagingFactory(config('wk-point.output_format'), config('wk-point.pagination.pageName'), config('wk-point.pagination.perPage'));
            return $factory->output($repository);
        }

        return $repository;
    }

    /**
     * @param Wallet  $instance
     * @return Array
     */
    public function show($instance): array
    {
        if (empty($instance))
            return [
                'id'         => '',
                'setting_id' => '',
                'user_id'    => '',
                'value'      => '',
                'is_enabled' => '',
                'updated_at' => ''
            ];

        $this->setEntity($instance);

        return [
              'id'         => $instance->id,
              'setting_id' => $instance->setting_id,
              'user_id'    => $instance->user_id,
              'value'      => $instance->value,
              'is_enabled' => $instance->is_enabled,
              'updated_at' => $instance->updated_at
        ];
    }
}
