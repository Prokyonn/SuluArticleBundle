<?php

namespace Sulu\Article\Infrastructure\Sulu\Content;

use Sulu\Article\Domain\Repository\ArticleRepositoryInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\WebsiteBundle\ReferenceStore\ReferenceStoreInterface;
use Sulu\Component\Content\Compat\PropertyParameter;
use Sulu\Component\SmartContent\Configuration\Builder;
use Sulu\Component\SmartContent\Configuration\BuilderInterface;
use Sulu\Component\SmartContent\Configuration\ProviderConfigurationInterface;
use Sulu\Component\SmartContent\DataProviderAliasInterface;
use Sulu\Component\SmartContent\DataProviderInterface;
use Sulu\Component\SmartContent\DataProviderResult;

class ArticleDataProvider implements DataProviderInterface, DataProviderAliasInterface
{

    public function __construct(
        private ArticleRepositoryInterface $articleRepository,
        private ContentManagerInterface $contentManager,
        private ReferenceStoreInterface $articleReferenceStore
    )
    {
    }

    public function getConfiguration(): ProviderConfigurationInterface
    {
        return $this->getConfigurationBuilder()->getConfiguration();
    }

    /**
     * Create new configuration-builder.
     */
    protected function getConfigurationBuilder(): BuilderInterface
    {
        $builder = Builder::create()
            ->enableTags()
            ->enableCategories()
            ->enableLimit()
            ->enablePagination()
            ->enablePresentAs()
            ->enableSorting(
                [
                    ['column' => 'published', 'title' => 'sulu_admin.published'],
                    ['column' => 'authored', 'title' => 'sulu_admin.authored'],
                    ['column' => 'created', 'title' => 'sulu_admin.created'],
                    ['column' => 'title.raw', 'title' => 'sulu_admin.title'],
                    ['column' => 'author_full_name.raw', 'title' => 'sulu_admin.author'],
                ]
            );

//        if (\method_exists($builder, 'enableTypes')) {
//            $builder->enableTypes($this->getTypes());
//        }

        return $builder;
    }

    public function getDefaultPropertyParameter(): array
    {
        return [
            'type' => new PropertyParameter('type', null),
            'ignoreWebspaces' => new PropertyParameter('ignoreWebspaces', false),
        ];
    }

    public function resolveDataItems(array $filters, array $propertyParameter, array $options = [], $limit = null, $page = 1, $pageSize = null)
    {
        [$filter, $sortBy] = $this->resolveFilters($filters, $page, $options['locale']);
        $articles = $this->articleRepository->findBy(
            $filter,
            $sortBy,
            [
                ArticleRepositoryInterface::GROUP_SELECT_ARTICLE_ADMIN
            ]
        );
        $articles = iterator_to_array($articles);

        return new DataProviderResult($articles, false);
    }

    public function resolveResourceItems(array $filters, array $propertyParameter, array $options = [], $limit = null, $page = 1, $pageSize = null): DataProviderResult
    {
        [$filters, $sortBy] = $this->resolveFilters($filters, $page, $options['locale']);

        $dimensionAttributes = [
            'locale' => $options['locale'],
            'stage' => DimensionContentInterface::STAGE_LIVE,
        ];

        $limit = $filters['limit'] ?? 0;

        $articles = $this->articleRepository->findBy(
            filters: array_merge($dimensionAttributes, $filters),
            sortBy: $sortBy,
            selects: [ArticleRepositoryInterface::GROUP_SELECT_ARTICLE_WEBSITE => true]
        );

        $result = [];
        foreach ($articles as $article) {
            $dimensionContent = $this->contentManager->resolve($article, $dimensionAttributes);
            $result[] = $this->contentManager->normalize($dimensionContent);
        }
        $hasNextPage = count($result) > ($pageSize ?? $limit);
        return new DataProviderResult($result, $hasNextPage);
    }

    protected function resolveFilters(array $filters, int $page, string $locale): array
    {
        $filter = [
            'locale' => $locale,
        ];
        $sortBy = [];
        if (isset($filters['categories'])) {
            $filter['categoryIds'] = $filters['categories'];
        }
        if (isset($filters['categoryOperator'])) {
            $filter['categoryOperator'] = $filters['categoryOperator'];
        }
        if (isset($filters['tags'])) {
            $filter['tagIds'] = $filters['tags'];
        }
        if (isset($filters['tagOperator'])) {
            $filter['tagOperator'] = $filters['tagOperator'];
        }
        if (isset($filters['limitResult'])) {
            $filter['limit'] = (int)$filters['limitResult'];
        }
        $filter['page'] = $page;

        if (isset($filters['sortBy']) && isset($filters['sortMethod'])) {
            $sortBy[$filters['sortBy']] = $filters['sortMethod'];
        }

        return [$filter, $sortBy];
    }

    public function resolveDatasource($datasource, array $propertyParameter, array $options)
    {
        $test = 123;
    }

    public function getAlias()
    {
        return 'article';
    }
}
