<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostcodeZone extends Model
{
    protected $fillable = [
        'created_at',
        'creator_id',
        'updated_at',
        'zone_id',
        'postcode',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function postcode(): BelongsTo
    {
        return $this->belongsTo(Postcode::class);
    } 
}
