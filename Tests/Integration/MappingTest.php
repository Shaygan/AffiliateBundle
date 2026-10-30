<?php

namespace Shaygan\AffiliateBundle\Tests\Integration;

use Doctrine\ORM\Tools\SchemaValidator;

final class MappingTest extends DatabaseTestCase
{
    public function testAllEntityAssociationsHaveConsistentTargets(): void
    {
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        self::assertCount(5, $metadata);
        foreach ($metadata as $entity) {
            foreach ($entity->associationMappings as $association) {
                if (empty($association['inversedBy'])) {
                    continue;
                }
                $target = $this->em->getClassMetadata($association['targetEntity']);
                self::assertSame(
                    $entity->name,
                    $target->getAssociationTargetClass($association['inversedBy']),
                    $entity->name.'#'.$association['fieldName'].' must point back to its own entity type'
                );
            }
        }
        self::assertSame([], (new SchemaValidator($this->em))->validateMapping());
    }
}
