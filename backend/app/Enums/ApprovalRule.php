<?php

namespace App\Enums;

enum ApprovalRule: string
{
    case ANY = 'any';
    case ALL = 'all';
}
