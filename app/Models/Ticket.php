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

    /**
     * Tell Eloquent to stop managing timestamps automatically.
     * We will manage them ourselves in the booted method.
     *
     * @var bool
     */
    public $timestamps = false; // <-- STEP 1: Add this line

    protected $fillable = [
        'user_id',
        'client_id',
        'subject',
        'description',
        'solution',
        'status',
        'resolved_at',
        'resolved_by',
        'is_weekend_unit',
        'is_excess_unit',
        'unit_value',
        'ticket_weight',
        'holiday_multiplier',
        'timeslot_multiplier',
        'unit_discount',
        'created_at', // <-- STEP 2: Add created_at and updated_at to fillable
        'updated_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'client_id' => 'integer',
        'status' => TicketStatus::class,
        'resolved_by' => 'integer',
        'resolved_at' => 'datetime',
        'is_weekend_unit' => 'boolean',
        'is_excess_unit' => 'boolean',
        'unit_value' => 'integer',
        'ticket_weight' => 'integer',
        'holiday_multiplier' => 'integer',
        'timeslot_multiplier' => 'integer',
        'unit_discount' => 'integer',
        'created_at' => 'datetime', // <-- STEP 3: Ensure timestamps are cast to datetime
        'updated_at' => 'datetime',
    ];
    // Relationships
    public function user() { return $this->belongsTo(User::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }


    // Boot method for auto-filling fields
    protected static function booted()
    {
        // STEP 4: Modify the booted method to handle timestamps manually
        static::creating(function ($ticket) {
            $user = auth()->user();

            // Validate role
            if (!$user->hasAnyRole(['client', 'super_admin', 'support'])) {
                throw new \Exception('Only clients, admins, or support can create tickets.');
            }

            // Always set user_id
            $ticket->user_id = $user->id;

            if ($user->hasRole('client')) {
                if (!$user->client_id) {
                    throw new \Exception('User does not belong to any client.');
                }
                $ticket->client_id = $user->client_id;
            } elseif (empty($ticket->client_id)) {
                throw new \Exception('Admin/support must choose a client.');
            }

            // Set timestamps manually. If created_at is not already set by the admin form, default to now().
            $ticket->created_at ??= now();
            $ticket->updated_at = now();


            // Auto-calculate unit fields
            // This now correctly uses the created_at value from the form
            $ticket->calculateAndSetUnitFields();
        });

        static::updating(function ($ticket) {
            // Always set updated_at on update
            $ticket->updated_at = now();

            if ($ticket->isDirty('ticket_weight', 'created_at')) { // Also recalculate if created_at changes on edit
                $ticket->calculateAndSetUnitFields();
            }
        });
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
        // This method is now correct, because $this->created_at will have the right value
        $package = $this->client?->package->versionedInstance();
        $service = new TicketUnitService($package);
        $data = $service->calculateTicketData(
            $this->ticket_weight ?? 1,
            $this->created_at, // No need for the `?? now()` fallback anymore
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

    public function getUnitMessage(): string
    {
        if ($this->is_excess_unit) {
            return "Exceeds quota. Units: {$this->unit_value}.";
        }

        if ($this->unit_discount > 0) {
           return  "Total units: " . ($this->ticket_weight * $this->holiday_multiplier * $this->timeslot_multiplier -$this->unit_discount) ." (Base: {$this->ticket_weight} × Holiday: {$this->holiday_multiplier} × Timeslot: {$this->timeslot_multiplier} - Discount: {$this->unit_discount})";
        }

        return "Total units: {$this->unit_value} (Base: {$this->ticket_weight} × Holiday: {$this->holiday_multiplier} × Timeslot: {$this->timeslot_multiplier})";
    }

}
