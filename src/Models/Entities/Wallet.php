<?php

namespace WalkerChiu\Point\Models\Entities;

use WalkerChiu\Core\Models\Entities\Entity;

class Wallet extends Entity
{
    /**
     * Create a new instance.
     *
     * @param Array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('wk-core.table.point.wallets');

        $this->fillable = array_merge($this->fillable, [
            'setting_id', 'user_id',
            'value'
        ]);

        parent::__construct($attributes);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function setting()
    {
        return $this->belongsTo(config('wk-core.class.point.setting'), 'setting_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function records()
    {
        return $this->hasMany(config('wk-core.class.point.record'), 'wallet_id', 'id');
    }

    /**
     * Check if it belongs to the user.
     * 
     * @param User  $user
     * @return Bool
     */
    public function isOwnedBy($user): bool
    {
        if (empty($user))
            return false;

        return $this->user_id == $user->id;
    }
}
