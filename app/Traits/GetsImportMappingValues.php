<?php

namespace App\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait GetsImportMappingValues
{
    protected function getMapField(string $key, $default = null): mixed
    {
        return Arr::get($this->import->mapping, $key, $default) ?? null;
    }

    /**
     * This gets a value from the mapping
     * or from the row if the value is mapped to
     * the spreadsheet
     *
     * @param string $key
     * @param string|null $conversion
     * @param mixed $default
     * @return mixed
     */
    protected function getMapValue(string $key, string $conversion = null, mixed $default = null): mixed
    {
        $mapField = $this->getMapField($key, $default);

        // Check that the resulting field is an array
        // that has all the column mapping keys
        if (
            !is_array($mapField) ||
            !Arr::has($mapField, ['isManual', 'column', 'value'])
        ) {
            return $mapField;
        }

        if ($mapField['isManual']) {
            return $mapField['value'];
        }

        $value = $this->currentRow->get($mapField['column']);

        if ($conversion) {
            $method = Str::camel('convert ' . $conversion);

            if (method_exists($this, $method)) {
                return $this->{$method}($value) ?? $default;
            }
        }

        return $value;
    }
}
