<?php

namespace App;

enum PaymentStatusEnum: string
{
    case AUTHORIZED = 'authorized';
    case CAPTURED = 'captured';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';
    case FAILED = 'failed';
}
