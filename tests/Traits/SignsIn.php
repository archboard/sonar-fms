<?php

namespace Tests\Traits;

trait SignsIn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }
}
