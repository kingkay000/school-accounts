<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'target_type',
        'target_id',
        'approver_id',
        'action',
        'reason',
        'evidence_attachment_id',
    ];

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function evidenceAttachment()
    {
        return $this->belongsTo(Attachment::class, 'evidence_attachment_id');
    }
}
