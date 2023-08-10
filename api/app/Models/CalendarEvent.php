<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\{
    GlobalTrait,
    UuidTrait
};

class CalendarEvent extends Model
{
    use HasFactory, SoftDeletes, UuidTrait, GlobalTrait;

    protected $modelName = 'calendar_event';
    
    protected $guarded = [
        'created_at'
    ];

    protected $hidden  = [
        'updated_at',
        'deleted_at'
    ];
}
