<?php

namespace App\Modules\Client\Models;

use App\Modules\Sale\Models\Sale;
use App\Modules\User\Models\User;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;
use MongoDB\Laravel\Relations\HasOne;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'name', 'document_number', 'phone', 'phone1', 'contact1', 'phone2', 'contact2'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function newFactory(): ClientFactory
    {
        return ClientFactory::new();
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function address(): HasOne { return $this->hasOne(Address::class); }
    public function sales(): HasMany { return $this->hasMany(Sale::class); }

    // MongoDB specific indexes
    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($client) {
            if (empty($client->_id)) {
                $client->_id = (string) new \MongoDB\BSON\ObjectId();
            }
        });
    }
}
