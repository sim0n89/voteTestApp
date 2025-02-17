<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $table = 'votes';
    protected $fillable = [
        'entity_id',
        'entity_type',
        'user_id',
        'vote_type',
    ];
}
