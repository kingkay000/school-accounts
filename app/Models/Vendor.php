<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'legal_name',
        'tin',
        'vat_registered',
        'category',
        'bank_details',
    ];

    protected $casts = [
        'vat_registered' => 'boolean',
        'bank_details' => 'array',
    ];

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
