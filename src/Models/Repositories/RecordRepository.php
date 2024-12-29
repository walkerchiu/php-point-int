<?php

namespace WalkerChiu\Point\Models\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Forms\FormTrait;
use WalkerChiu\Core\Models\Repositories\Repository;
use WalkerChiu\Core\Models\Repositories\RepositoryTrait;
use WalkerChiu\Core\Models\Services\PackagingFactory;

class RecordRepository extends Repository
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
        $this->instance = App::make(config('wk-core.class.point.record'));
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
                                            ->unless(empty($data['wallet_id']), function ($query) use ($data) {
                                                return $query->where('wallet_id', $data['wallet_id']);
                                            })
                                            ->unless(empty($data['serial']), function ($query) use ($data) {
                                                return $query->where('serial', $data['serial']);
                                            })
                                            ->unless(empty($data['style']), function ($query) use ($data) {
                                                return $query->where('style', 'LIKE', "%".$data['style']."%");
                                            })
                                            ->unless(empty($data['subject']), function ($query) use ($data) {
                                                return $query->where('subject', 'LIKE', "%".$data['subject']."%");
                                            })
                                            ->unless(empty($data['content']), function ($query) use ($data) {
                                                return $query->where('content', 'LIKE', "%".$data['content']."%");
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
     * @param Record  $instance
     * @return Array
     */
    public function show($instance): array
    {
        if (empty($instance))
            return [
                'id'             => '',
                'wallet_id'      => '',
                'value_original' => '',
                'value'          => '',
                'end_at'         => '',
                'is_enabled'     => '',
                'updated_at'     => ''
            ];

        $this->setEntity($instance);

        return [
              'id'             => $instance->id,
              'wallet_id'      => $instance->wallet_id,
              'value_original' => $instance->value_original,
              'value'          => $instance->value,
              'end_at'         => $instance->end_at,
              'is_enabled'     => $instance->is_enabled,
              'updated_at'     => $instance->updated_at
        ];
    }

    /**
     * @param Int  $wallet_id
     * @return Collection
     */
    public function listEnabled(int $wallet_id)
    {
        $records1 = $this->instance->ofEnabled()
                                 ->where('wallet_id', $wallet_id)
                                 ->where('value', '>', 0)
                                 ->whereNotNull('end_at')
                                 ->where('end_at', '>', Carbon::now())
                                 ->orderBy('end_at', 'ASC')
                                 ->get();

        $records2 = $this->instance->ofEnabled()
                                 ->where('wallet_id', $wallet_id)
                                 ->where('value', '>', 0)
                                 ->whereNull('end_at')
                                 ->orderBy('created_at', 'ASC')
                                 ->get();

        return $records1->merge($records2);
    }
}
