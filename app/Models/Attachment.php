<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = ['bank_log_id', 'ledger_entry_id', 'google_drive_file_id', 'thumbnail_url', 'extracted_text', 'file_name'];

    public function bankLog()
    {
        return $this->belongsTo(BankLog::class);
    }
}
