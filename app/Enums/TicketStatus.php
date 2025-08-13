<?php

namespace App\Enums;

enum TicketStatus: string
{
    case OPEN = 'open';
    case CLOSED = 'closed';
    case PENDING = 'pending';
}
