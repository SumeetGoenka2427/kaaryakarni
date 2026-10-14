<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
| Feature tests boot the full app against the kaaryakarni_test MySQL database
| (see phpunit.xml) and run inside a transaction that is rolled back.
*/
pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');
