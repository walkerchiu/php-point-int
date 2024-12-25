<?php

namespace WalkerChiu\Point\Models\Entities;

trait UserTrait
{
    /**
     * @param Int  $setting_id
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wallets($setting_id = null)
    {
        return $this->hasMany(config('wk-core.class.point.wallet'), 'user_id', 'id')
                    ->when($setting_id, function ($query, $setting_id) {
                                return $query->where('setting_id', $setting_id);
                            });
    }
}
