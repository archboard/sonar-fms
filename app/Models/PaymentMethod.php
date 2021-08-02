<?php

namespace App\Models;

use App\Exceptions\PaymentMethodDriverNotFound;
use App\PaymentMethods\Cash;
use App\PaymentMethods\PaymentMethodDriver;
use App\Traits\BelongsToSchool;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;
    use BelongsToTenant;
    use BelongsToSchool;

    protected $casts = [
        'options' => 'json',
    ];

    public static function options(): array
    {
        return [
            'cash' => __('Cash/check'),
        ];
    }

    public function getDriver(): PaymentMethodDriver
    {
        $drivers = [
            'cash' => Cash::class,
        ];

        $driver = $drivers[$this->driver] ?? null;

        if (!$driver || !class_exists($driver)) {
            throw new PaymentMethodDriverNotFound("The {$this->driver} payment method driver could not be found.");
        }

        return new $driver($this);
    }
}
