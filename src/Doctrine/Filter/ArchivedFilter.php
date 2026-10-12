<?php

namespace App\Doctrine\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class ArchivedFilter extends SQLFilter
{
    public function addFilterConstraint(
        ClassMetadata $targetEntity,
        string $targetTableAlias
    ): string {
        if (!$targetEntity->hasField('dateArchived')) {
            return '';
        }

        $column = $targetEntity->getColumnName('dateArchived');

        return sprintf('%s.%s IS NULL', $targetTableAlias, $column);
    }
}
