<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VotesAggregate extends Model
{
    protected $table = 'votes_aggregate';
    protected $fillable = [
        'entity_id',
        'entity_type',
        'likes_count',
        'dislikes_count',
    ];
}
