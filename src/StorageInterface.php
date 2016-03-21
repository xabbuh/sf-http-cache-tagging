<?php

/*
 * This file is part of the Glob package.
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DTL\Symfony\HttpCacheTagging;

/**
 * Implementors of this interface associate cache entry identifiers
 * with tags, retrieve cache entry identifiers for tags and remove tags.
 */
interface StorageInterface
{
    /**
     * Associate a list of tags with the given cache identifier.
     *
     * The identifier can be any scalar value which can be associated with a
     * unique HTTP cache entry.
     *
     * @param string[] $tags
     * @param mixed $identifier
     *
     * @return void
     */
    public function tagContentDigest(array $tags, $identifier);

    /**
     * Remove the given list of tags from the store.
     *
     * If any of the given tags do not exist, they should be ignored.
     *
     * @param string[] $tags
     *
     * @return void
     */
    public function removeTags(array $tags);

    /**
     * Return the cache identifiers for the given list of tags.
     *
     * @param string[] $tags
     *
     * @return mixed[]
     */
    public function getCacheIds(array $tags);
}
