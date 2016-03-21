<?php

namespace DTL\Symfony\HttpCacheTagging;

interface TagManagerInterface
{
    /**
     * Invalidate the cache entries associated with any of the given list of tags.
     *
     * @param string[] $tags
     */
    public function invalidateTags(array $tags);

    /**
     * Associate the given content digest with the given tags.
     *
     * NOTE: Often this method could simply be a proxy to
     * StorageInterface#tagContentDigest.
     *
     * @param string[] $tags
     * @param string $contentDigest
     * @param int $lifetime
     *
     * @return void
     */
    public function tagContentDigest(array $tags, $contentDigest);
}
