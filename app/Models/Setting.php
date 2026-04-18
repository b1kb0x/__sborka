<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public const TYPE_STRING = 'string';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_JSON = 'json';

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
    ];
}
