<?php

namespace App\Models;

use App\Exceptions\PaymentMethodDriverNotFound;
use App\PaymentMethods\BankTransfer;
use App\PaymentMethods\Cash;
use App\PaymentMethods\PaymentMethodDriver;
use App\Traits\BelongsToSchool;
use App\Traits\BelongsToTenant;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperPaymentMethod
 */
class PaymentMethod extends Model
{
    use HasFactory;
    use HasResource;
    use BelongsToTenant;
    use BelongsToSchool;

    protected $guarded = [];

    public bool $includeDriverWithResource = true;

    protected $casts = [
        'options' => 'json',
        'active' => 'boolean',
        'show_on_invoice' => 'boolean',
    ];

    public static function drivers(): array
    {
        return [
            'bank_transfer' => BankTransfer::class,
            'cash' => Cash::class,
        ];
    }

    public static function getAllDrivers(): array
    {
        return array_map(
            fn ($class) => new $class,
            static::drivers()
        );
    }

    public static function options(): array
    {
        return collect(static::drivers())
            ->mapWithKeys(function ($driver) {
                /** @var PaymentMethodDriver $instance */
                $instance = new $driver;

                return [$instance->key() => $instance->label()];
            })
            ->toArray();
    }

    public static function makeDriver(string $driverName): PaymentMethodDriver
    {
        $drivers = static::drivers();

        $driver = $drivers[$driverName] ?? null;

        if (!$driver || !class_exists($driver)) {
            throw new PaymentMethodDriverNotFound("The {$driverName} payment method driver could not be found.");
        }

        return new $driver();
    }

    public function getDriver(): PaymentMethodDriver
    {
        return static::makeDriver($this->driver)
            ->setPaymentMethod($this);
    }

    public static function getListForSchool(School $school): array
    {
        $paymentMethods = $school
            ->paymentMethods
            ->keyBy('driver');

        return array_map(
            function (PaymentMethodDriver $driver) use ($paymentMethods) {
                /** @var static $paymentMethod */
                $paymentMethod = $paymentMethods->get($driver->key(), new PaymentMethod());
                $paymentMethod->includeDriverWithResource = false;

                $driver->setPaymentMethod($paymentMethod)
                    ->setIncludePaymentMethodInResource(true);

                return $driver;
            },
            PaymentMethod::getAllDrivers()
        );
    }
}
