<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    use HasFactory;

    protected $fillable = ['entry_date', 'description', 'amount', 'gl_code', 'bank_log_id'];

    public function bankLog()
    {
        return $this->belongsTo(BankLog::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }
}
