<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kirschbaum\Commentions\Contracts\Commentable;
use Kirschbaum\Commentions\HasComments;
use App\Enums\TicketStatus;
use App\Services\TicketUnitService;
use Carbon\Carbon;

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
        'ticket_weight',
        'holiday_multiplier',
        'timeslot_multiplier',
        'is_extra',
        'unit_discount',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'client_id' => 'integer',
        'status' => TicketStatus::class,
        'resolved_at' => 'datetime',
        'is_weekend_unit' => 'boolean',
        'is_excess_unit' => 'boolean',
        'unit_value' => 'integer',
        'ticket_weight' => 'integer',
        'holiday_multiplier' => 'integer',
        'timeslot_multiplier' => 'integer',
        'is_extra' => 'boolean',
        'unit_discount' => 'integer',
    ];

    // Relationships
    public function user() { return $this->belongsTo(User::class); }
    public function client() { return $this->belongsTo(Client::class); }

    // Boot method
    protected static function booted()
    {
        static::creating(fn ($ticket) => $ticket->initializeTicket());
        static::updating(fn ($ticket) => $ticket->updateUnitFieldsIfWeightChanged());
    }

    protected function initializeTicket(): void
    {
        $user = auth()->user();
        if (!$user->hasRole('client') || !$user->client_id) {
            throw new \Exception('Only clients can create tickets.');
        }

        $this->user_id = $user->id;
        $this->client_id = $user->client_id;
        $this->calculateAndSetUnitFields();
    }

    protected function updateUnitFieldsIfWeightChanged(): void
    {
        if ($this->isDirty('ticket_weight')) {
            $this->calculateAndSetUnitFields();
        }
    }

    public function calculateAndSetUnitFields(): void
    {
        $service = new TicketUnitService($this->client?->package);
        $data = $service->calculateTicketData(
            $this->ticket_weight ?? 1,
            $this->created_at ?? Carbon::now('Asia/Dhaka'),
            $this->client_id
        );

        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function getEffectiveUnitValueAttribute(): int
    {
        return max(0, $this->unit_value - $this->unit_discount);
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('created_at', $year)->whereMonth('created_at', $month);
    }

    public function scopeCurrentMonth($query)
    {
        $now = Carbon::now();
        return $query->forMonth($now->year, $now->month);
    }
}
