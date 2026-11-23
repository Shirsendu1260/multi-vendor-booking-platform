<?php

namespace App;

enum UserRoleEnum: string
{
    case CUSTOMER = 'customer';
    case VENDOR = 'vendor';
    case ADMIN = 'admin';
    case VIEW_ONLY_ADMIN = 'view_only_admin';
}