<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'amount',
    ];

    /**
     * Compte expéditeur de la transaction.
     */
    public function sender()
    {
        return $this->belongsTo(Account::class, 'sender_id');
    }

    /**
     * Compte destinataire de la transaction.
     */
    public function receiver()
    {
        return $this->belongsTo(Account::class, 'receiver_id');
    }

    //
}
