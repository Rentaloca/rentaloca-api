<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'picture_path',
        'price',
        'size',
        'bust',
        'waist',
        'hips',
        'length',
        'suitable_for_body_shape',
    ];

    public function toArray()
    {
        $toArray = parent::toArray();
        $toArray['picture_path'] = $this->picture_path;
        return $toArray;
    }

    public function getPicturePathAttribute()
    {
        return config('app.url') . Storage::url($this->attributes['picture_path']);
    }
}
