<?php

namespace App\Autocompleter;

use App\Entity\Trick;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\Autocomplete\EntityAutocompleterInterface;

#[AutoconfigureTag('ux.entity_autocompleter', ['alias' => 'trick_search'])]
class TrickSearchAutocompleter implements EntityAutocompleterInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getEntityClass(): string
    {
        return Trick::class;
    }

    public function createFilteredQueryBuilder(
        EntityRepository $repository,
        string $query
    ): QueryBuilder {
        return $repository
            ->createQueryBuilder('t')
            ->leftJoin('t.category', 'c')
            ->andWhere('t.name LIKE :search OR c.name LIKE :search')
            ->addOrderBy('t.name', 'ASC')
            ->setParameter('search', '%' . $query . '%');
    }

    /**
     * @param Trick $trick 
     */
    public function getLabel(object $trick): string
    {
        return $trick->getName();
    }

    /**
     * @param Trick $trick 
     */
    public function getValue(object $trick): string
    {
        $url = $this->urlGenerator->generate('trick.single', [
            'id' => $trick->getId(),
            'slug' => $trick->getSlug(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        return $url;
    }

    public function isGranted(Security $security): bool
    {
        return true;
    }

    public function getGroupBy(): mixed
    {
        return null;
    }
}
