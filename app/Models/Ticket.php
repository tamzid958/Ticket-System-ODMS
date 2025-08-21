<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kirschbaum\Commentions\Contracts\Commentable;
use Kirschbaum\Commentions\HasComments;
use App\Enums\TicketStatus;

class Ticket extends Model implements Commentable
{
    use HasFactory, SoftDeletes, HasComments;

    protected $fillable = [
        'user_id',
        'client_id',
        'subject',
        'description',
        'status',
        'resolved_at',
        'is_weekend_unit',
        'is_excess_unit',
        'unit_value',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'client_id' => 'integer',
        'status' => TicketStatus::class,
        'resolved_at' => 'datetime',
        'is_weekend_unit' => 'boolean',
        'is_excess_unit' => 'boolean',
        'unit_value' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    protected static function booted()
    {
        static::creating(function ($ticket) {
            $user = auth()->user();

            if (!$user->hasRole('client')) {
                throw new \Exception('Only clients can create tickets.');
            }
            if (!$user->client_id) {
                throw new \Exception('User does not belong to any client.');
            }

            $ticket->user_id = $user->id;
            $ticket->client_id = $user->client_id;
        });
    }

}
