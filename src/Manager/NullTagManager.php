<?php

/*
 * This file is part of the Symfony Http Cache Tagging package.
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DTL\Symfony\HttpCacheTagging\Manager;

use DTL\Symfony\HttpCacheTagging\TagManagerInterface;

class NullTagManager implements TagManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function invalidateTags(array $tags)
    {
    }

        /**
         * {@inheritdoc}
     */
    public function tagContentDigest(array $tags, $contentDigest)
    {
    }
}
