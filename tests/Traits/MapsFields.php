<?php

namespace Tests\Traits;

use JetBrains\PhpStorm\ArrayShape;

trait MapsFields
{
    #[ArrayShape(['id' => 'string', 'column' => 'null|string', 'value' => 'null|string', 'isManual' => 'bool'])]
    protected function makeMapField(?string $column = null, ?string $value = null, bool $isManual = false): array
    {
        return [
            'id' => $this->uuid(),
            'column' => $column,
            'value' => $value,
            'isManual' => $isManual,
        ];
    }
}
