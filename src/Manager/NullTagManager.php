<?php

namespace DTL\Symfony\HttpCacheTagging\Manager;

use DTL\Symfony\HttpCacheTagging\TagManagerInterface;

class NullTagManager implements TagManagerInterface
{
    /**
     * {@inheritDoc}
     */
    public function invalidateTags(array $tags)
    {
    }

        /**
         * {@inheritDoc}
     */
    public function tagContentDigest(array $tags, $contentDigest)
    {
    }
}
