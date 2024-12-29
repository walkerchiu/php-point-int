<?php

namespace WalkerChiu\Point\Models\Entities;

use WalkerChiu\Core\Models\Entities\UuidEntity;

class Record extends UuidEntity
{
    /**
     * Create a new instance.
     *
     * @param Array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('wk-core.table.point.records');

        $this->fillable = array_merge($this->fillable, [
            'wallet_id',
            'value_original', 'value',
            'end_at'
        ]);

        $this->dates = array_merge($this->dates, [
            'end_at'
        ]);

        parent::__construct($attributes);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function wallet()
    {
        return $this->belongsTo(config('wk-core.class.point.wallet'), 'wallet_id', 'id');
    }
}
