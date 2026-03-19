<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\DomainException\DomainRecordNotFoundException;

class LineNotFoundException extends DomainRecordNotFoundException
{
    public $message = 'The line you requested does not exist.';
}
