<?php

declare(strict_types=1);

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Article\Infrastructure\Sulu\Headless\Resolver;

use Sulu\Article\Domain\Model\ArticleDimensionContent;
use Sulu\Article\Domain\Model\ArticleDimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Structure\ContentStructureBridgeFactory;
use Sulu\Bundle\HeadlessBundle\Content\DataProviderResolver\DataProviderResolverInterface;
use Sulu\Bundle\HeadlessBundle\Content\DataProviderResolver\DataProviderResult;
use Sulu\Bundle\HeadlessBundle\Content\StructureResolverInterface;
use Sulu\Component\Content\Compat\PropertyParameter;
use Sulu\Component\Content\Compat\StructureInterface;
use Sulu\Component\SmartContent\ArrayAccessItem;
use Sulu\Component\SmartContent\Configuration\ProviderConfigurationInterface;
use Sulu\Component\SmartContent\DataProviderInterface;

abstract class AbstractArticleDataProviderResolver implements DataProviderResolverInterface
{
    public function __construct(
        private DataProviderInterface $articleDataProvider,
        private ContentStructureBridgeFactory $contentStructureBridgeFactory,
        private StructureResolverInterface $structureResolver
//        bool $showDrafts
    )
    {
    }

    public function getProviderConfiguration(): ProviderConfigurationInterface
    {
        return $this->articleDataProvider->getConfiguration();
    }

    /**
     * @return PropertyParameter[]
     */
    public function getProviderDefaultParams(): array
    {
        return $this->articleDataProvider->getDefaultPropertyParameter();
    }

    public function resolve(
        array $filters,
        array $propertyParameters,
        array $options = [],
        ?int $limit = null,
        int $article = 1,
        ?int $pageSize = null
    ): DataProviderResult
    {
        $providerResult = $this->articleDataProvider->resolveResourceItems(
            $filters,
            $propertyParameters,
            $options,
            $limit,
            $article,
            $pageSize,
        );

        /** @var ArticleDimensionContent[] $articles */
        $articles = array_map(
            fn(ArrayAccessItem $item) => $item->getResource(),
            $providerResult->getItems()
        );

        return new DataProviderResult($this->resolveArticles($articles), $providerResult->getHasNextPage());
    }

    /**
     * @param ArticleDimensionContentInterface[] $articles
     *
     * @return mixed[]
     */
    private function resolveArticles(array $articles): array
    {
        $structures = array_map(
            fn(ArticleDimensionContentInterface $article) => $this->contentStructureBridgeFactory->getBridge($article, $article->getResourceId(), $article->getLocale()),
            $articles
        );

        return array_map(
            fn(StructureInterface $structure) => $this->structureResolver->resolve($structure, $structure->getLanguageCode()),
            $structures
        );
    }
}
