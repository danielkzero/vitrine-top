<?php

namespace App\Models;

use App\Enums\AddressInputMode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'user_id',
        'label',
        'is_default',
        'zip',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'reference',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function setAddressMode(AddressInputMode $mode): void
    {
        $metadata = $this->metadata ?? [];
        $metadata['input_mode'] = $mode->value;
        $this->metadata = $metadata;
    }
}
