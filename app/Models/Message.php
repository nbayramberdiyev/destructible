<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Message extends Model
{
    protected $fillable = [
        'uuid',
        'message',
        'file',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function (self $message) {
            $message->uuid = Uuid::uuid4();
        });
    }
}