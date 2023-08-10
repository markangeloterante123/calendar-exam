<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\UuidTrait;

class Image extends Model
{
    use HasFactory, SoftDeletes, UuidTrait;

    protected $guarded = [
        'created_at'
    ];

    protected $hidden  = [
        'updated_at',
        'deleted_at'
    ];

    public function getFilenameAttribute ()
    {
        return $this->attributes['path'];
    }

    public function getFileresizedAttribute () {
        return $this->attributes['path_resized'];
    }

    public function getPathAttribute ($value)
    {
         // return url('/') . '/' . $value;
        return url('/') . '/storage/' . $value;
    }

    public function getPathResizedAttribute ($value)
    {
         // return url('/') . '/' . $value;
        return url('/') . '/storage/' . $value;
    }
}
