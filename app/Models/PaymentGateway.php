<?php

namespace App\Models;

use App\Database\Database;

class PaymentGateway extends Model
{
    protected static string $table = 'payment_gateways';

    public static function allActive(): array
    {
        return Database::query("SELECT * FROM `payment_gateways` WHERE `status` = 'active' ORDER BY `type` ASC, `id` ASC");
    }
}
