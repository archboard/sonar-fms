<?php

namespace Tests\Traits;

use Illuminate\Http\UploadedFile;

trait GetsUploadedFiles
{
    protected function getUploadedFile(string $fileName = 'sonar-import.xlsx'): UploadedFile
    {
        return new UploadedFile(base_path("tests/{$fileName}"), $fileName, null, null, true);
    }
}
