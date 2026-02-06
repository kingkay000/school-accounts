<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'vat_applicable',
        'vat_amount',
        'wht_applicable',
        'wht_rate',
        'wht_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'vat_applicable' => 'boolean',
        'wht_applicable' => 'boolean',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
