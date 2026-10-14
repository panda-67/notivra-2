<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN_STAFF = 'admin_staff';
    case REVIEWER = 'reviewer';
    case RESEARCHER = 'researcher';
}
