<?php

namespace App\Support;

use Carbon\Carbon;

class VersionHelper
{

    /**
     * Returns the date which will be used for cost calculation
     * @return Carbon
     */
    public static function getCostingDate()
    {
        return Carbon::now()->startOfMonth();
    }
}
