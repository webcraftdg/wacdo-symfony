<?php

namespace App\Migration;

use Doctrine\Migrations\AbstractMigration;

abstract class BaseMigration extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }
}
