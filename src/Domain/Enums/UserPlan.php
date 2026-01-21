<?php
namespace App\Domain\Enums;

enum UserPlan: string {
    case FREE = 'free';
    case PREMIUM = 'premium';
}
