<?php

namespace App\Models;

use App\Events\FileUsed;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Ramsey\Uuid\Uuid;

class File extends Model
{

    use HasUuids;

    protected $dispatchesEvents = [
        'deleted' => FileUsed::class,
    ];

    /**
     * Generate a new UUID for the model.
     *
     * @return string
     */
    public function newUniqueId()
    {
        return (string) Uuid::uuid4();
    }

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array
     */
    public function uniqueIds()
    {
        return ['public_link'];
    }

    protected static function booted()
    {
        static::deleting(function ($category) {
            info('deleted');
        });
    }

}
