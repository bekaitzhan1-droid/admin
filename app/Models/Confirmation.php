<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Confirmation extends Model
{
    protected $fillable = [
        'type',
        'iin',
        'is_confirmed',
        'text',
    ];
}
