<?php

namespace App;

enum SlotStatusEnum: string
{
    case AVAILABLE = 'available';
    case BOOKED = 'booked';
    case BLOCKED = 'blocked';
}
