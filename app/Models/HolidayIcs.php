<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HolidayIcs extends Model
{
    protected $table = 'holiday_ics';
    protected $fillable = ['year', 'ics_file'];
}
