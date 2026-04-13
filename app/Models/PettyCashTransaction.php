<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PettyCashTransaction extends Model
{
    protected $fillable = [
        'transaction_at', 'type', 'amount', 'description', 'reference', 'recorded_by',
    ];

    protected $casts = [
        'transaction_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by')->withTrashed();
    }

    public function signedAmount(): float
    {
        return $this->type === 'credit' ? (float) $this->amount : -(float) $this->amount;
    }
}
