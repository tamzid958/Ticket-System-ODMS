<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\MailStatus;

class Mail extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'user_id',
        'ticket_id',
        'status',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'status' => MailStatus::class,
    ];

    /**
     * Belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Belongs to Ticket
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
