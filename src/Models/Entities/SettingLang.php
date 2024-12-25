<?php

namespace WalkerChiu\Point\Models\Entities;

use WalkerChiu\Core\Models\Entities\Lang;

class SettingLang extends Lang
{
    /**
     * Create a new instance.
     *
     * @param Array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('wk-core.table.point.settings_lang');

        parent::__construct($attributes);
    }
}
