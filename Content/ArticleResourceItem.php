<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ArticleBundle\Content;

use Sulu\Bundle\ArticleBundle\Document\ArticleDocument;
use Sulu\Bundle\ArticleBundle\Document\ArticleOngrDocument;
use Sulu\Bundle\ArticleBundle\Document\ExcerptOngrObject;
use Sulu\Bundle\ArticleBundle\Document\SeoOngrObject;
use Sulu\Component\SmartContent\ResourceItemInterface;

/**
 * Represents a resource-item for smart-content data-provider.
 */
class ArticleResourceItem implements ResourceItemInterface
{
    /**
     * @var ArticleOngrDocument
     */
    private $article;

    /**
     * @var ArticleDocument
     */
    private $resource;

    /**
     * @param ArticleOngrDocument $article
     * @param ArticleDocument $resource
     */
    public function __construct(ArticleOngrDocument $article, ArticleDocument $resource)
    {
        $this->article = $article;
        $this->resource = $resource;
    }

    /**
     * Returns uuid.
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->article->getUuid();
    }

    /**
     * Returns locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->article->getLocale();
    }

    /**
     * Returns title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->article->getTitle();
    }

    /**
     * Returns type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->article->getType();
    }

    /**
     * Returns changer.
     *
     * @return string
     */
    public function getChanger()
    {
        return $this->article->getChanger();
    }

    /**
     * Returns creator.
     *
     * @return string
     */
    public function getCreator()
    {
        return $this->article->getCreator();
    }

    /**
     * Return changed.
     *
     * @return \DateTime
     */
    public function getChanged()
    {
        return $this->article->getChanged();
    }

    /**
     * Returns created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->article->getCreated();
    }

    /**
     * Returns excerpt.
     *
     * @return ExcerptOngrObject
     */
    public function getExcerpt()
    {
        return $this->article->getExcerpt();
    }

    /**
     * Returns seo.
     *
     * @return SeoOngrObject
     */
    public function getSeo()
    {
        return $this->article->getSeo();
    }

    /**
     * {@inheritdoc}
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getUuid();
    }
}
