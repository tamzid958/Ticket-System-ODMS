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
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Boot method for auto-filling fields
protected static function booted()
{
    static::creating(function ($ticket) {
        $user = auth()->user();

        // Validate role
        if (!$user->hasAnyRole(['client', 'super_admin', 'support'])) {
            throw new \Exception('Only clients, admins, or support can create tickets.');
        }

        // Always set user_id to the creator
        $ticket->user_id = $user->id;

        if ($user->hasRole('client')) {
            // Force client users to their own client_id
            if (!$user->client_id) {
                throw new \Exception('User does not belong to any client.');
            }
            $ticket->client_id = $user->client_id;
        } else {
            // For admin/support, client_id must come from the form
            if (empty($ticket->client_id)) {
                throw new \Exception('Admin must choose a client.');
            }
        }

        // Auto-calculate unit-related fields
        $ticket->calculateAndSetUnitFields();
    });

    static::updating(function ($ticket) {
        // Recalculate unit fields if ticket_weight changes
        if ($ticket->isDirty('ticket_weight')) {
            $ticket->calculateAndSetUnitFields();
        }
    });
}


    /**
     * Calculate and set all unit-related fields
     */
    public function calculateAndSetUnitFields()
    {
        $ticketWeight = $this->ticket_weight ?? 1;
        $ticketTime = $this->created_at ?? Carbon::now('Asia/Dhaka');
        
        // Get client's package (assuming client has a package relationship)
        $package = $this->client->package ?? Package::first();
        
        $unitService = new TicketUnitService($package);
        $calculatedData = $unitService->calculateTicketData(
            $ticketWeight, 
            $ticketTime, 
            $this->client_id
        );

        // Set all the calculated fields
        $this->unit_value = $calculatedData['unit_value'];
        $this->holiday_multiplier = $calculatedData['holiday_multiplier'];
        $this->timeslot_multiplier = $calculatedData['timeslot_multiplier'];
        $this->is_weekend_unit = $calculatedData['is_weekend_unit'];
        $this->is_excess_unit = $calculatedData['is_excess_unit'];
        $this->unit_discount = $calculatedData['unit_discount'];
    }

    /**
     * Get the effective unit value (after discount)
     */
    public function getEffectiveUnitValueAttribute(): int
    {
        return max(0, $this->unit_value - $this->unit_discount);
    }

    /**
     * Scope to get tickets for a specific month
     */
    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month);
    }

    /**
     * Scope to get tickets for current month
     */
    public function scopeCurrentMonth($query)
    {
        $now = Carbon::now();
        return $query->forMonth($now->year, $now->month);
    }
}