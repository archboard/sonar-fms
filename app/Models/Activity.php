<?php

namespace App\Models;

use Illuminate\Support\Arr;
use Spatie\Activitylog\Models\Activity as BaseActivity;

/**
 * @mixin IdeHelperActivity
 */
class Activity extends BaseActivity
{
    public function getDescriptionAttribute(?string $description):? string
    {
        if (is_null($description)) {
            return null;
        }

        $properties = collect($this->properties); // @phpstan-ignore-line

        if ($this->relationLoaded('causer')) {
            $properties->put('user', $this->causer->full_name);
        }

        return __($description, $properties->except(['attributes', 'old'])->toArray());
    }

    public function getComponentAttribute():? string
    {
        return $this->properties->get('component'); // @phpstan-ignore-line
    }

    public function getChangelog(): array
    {
        $changes = $this->getChangesAttribute();

        if ($changes->isEmpty()) {
            return [];
        }

        $attributes = array_keys($changes->get('attributes'));

        return array_map(
            fn ($attribute) => [
                'attribute' => $attribute,
                'value' => $this->getChangeAttributeValue($attribute, Arr::get($changes->get('attributes'), $attribute)),
                'old' => $this->getChangeAttributeValue($attribute, Arr::get($changes->get('old'), $attribute)),
            ],
            $attributes
        );
    }

    protected function getChangeAttributeValue(string $attribute, ?string $value): ?string
    {
        if (!$value) {
            return $value;
        }

        if ($attribute === 'made_by') {
            return User::where('uuid', $value)
                ->select(['first_name', 'last_name'])
                ->first()
                ->full_name;
        }

        if ($attribute === 'payment_method_id') {
            return PaymentMethod::find($value)->name;
        }

        return $value;
    }
}
