<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_log_id',
        'ledger_entry_id',
        'transaction_id',
        'vendor_id',
        'document_type',
        'metadata',
        'file_hash',
        'google_drive_file_id',
        'thumbnail_url',
        'extracted_text',
        'file_name',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function bankLog()
    {
        return $this->belongsTo(BankLog::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function ledgerEntry()
    {
        return $this->belongsTo(LedgerEntry::class);
    }
}
