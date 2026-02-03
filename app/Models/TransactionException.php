<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionException extends Model
{
    use HasFactory;

    protected $table = 'exceptions';

    protected $fillable = [
        'transaction_id',
        'type',
        'severity',
        'status',
        'resolved_by',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
