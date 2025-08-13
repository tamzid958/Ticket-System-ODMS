<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kirschbaum\Commentions\Contracts\Commentable;
use Kirschbaum\Commentions\HasComments;

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
    ];

    protected $casts = [
        'user_id' => 'integer',
        'client_id' => 'integer',
        'status' => TicketStatus::class,
        'resolved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

}
