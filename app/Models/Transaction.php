<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_log_id',
        'vendor_id',
        'direction',
        'amount',
        'txn_date',
        'counterparty_name',
        'narration',
        'transaction_type',
        'status',
        'risk_flags',
    ];

    protected $casts = [
        'txn_date' => 'date',
        'risk_flags' => 'array',
    ];

    public function bankLog()
    {
        return $this->belongsTo(BankLog::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function taxAssessments()
    {
        return $this->hasMany(TaxAssessment::class);
    }

    public function exceptions()
    {
        return $this->hasMany(TransactionException::class);
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class, 'target_id')->where('target_type', 'transaction');
    }

    public function purchaseRequests()
    {
        return $this->hasMany(PurchaseRequest::class);
    }

    public function goodsReceivedNotes()
    {
        return $this->hasMany(GoodsReceivedNote::class);
    }
}
