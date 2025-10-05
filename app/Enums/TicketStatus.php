<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;

enum TicketStatus: string implements HasColor
{
    case OPEN = 'open';
    case CLOSED = 'closed';
    case PENDING = 'pending';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CLOSED => 'success',
            self::PENDING => 'warning',
            self::OPEN => 'danger',
        };
    }
}
