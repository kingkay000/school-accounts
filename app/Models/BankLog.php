<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankLog extends Model
{
    use HasFactory;

    protected $fillable = ['transaction_date', 'description', 'amount', 'type', 'status'];

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }
}
