<?php

/**
 * @package Duplicator
 */

namespace Duplicator\Installer\Utils;

/**
 * Link manager class
 */
class LinkManager
{
    /** @var string Duplicator website url */
    const DUPLICATOR_URL = 'https://duplicator.com/';

    /** @var string Duplicator docs sub path */
    const L1_DOCS_PATH = 'knowledge-base';

    /** @var string Path to doc categories */
    const L1_CATEGORY_PATH = 'knowledge-base-article-categories';

    /** @var string Trouble shooting category */
    const TROUBLESHOOTING_CAT = 'troubleshooting';

    /** @var string Quick start category */
    const QUICK_START_CAT = 'quick-start';

    /** @var string Resources category */
    const RESOURCES_CAT = 'resources';

    /**
     * @param string $slug    The slug of the article
     * @param string $medium  The utm medium
     * @param string $content The utm content
     *
     * @return string The blog post url with utm params
     */
    public static function getPostUrl($slug, $medium = '', $content = '')
    {
        return self::buildUrl($slug, $medium, $content);
    }

    /**
     * @param string $slug    The slug of the article
     * @param string $medium  The utm medium
     * @param string $content The utm content
     *
     * @return string The url with path and utm params
     */
    public static function getDocUrl($slug = '', $medium = '', $content = '')
    {
        $paths = array(self::L1_DOCS_PATH);
        if ($slug !== '') {
            $paths[] = $slug;
        }
        return self::buildUrl($paths, $medium, $content);
    }

    /**
     * @param string $slug    The slug of the category
     * @param string $medium  The utm medium
     * @param string $content The utm content
     *
     * @return string The url with path and utm params
     */
    public static function getCategoryUrl($slug, $medium = '', $content = '')
    {
        $paths = array(self::L1_CATEGORY_PATH);
        if ($slug !== '') {
            $paths[] = $slug;
        }
        return self::buildUrl($paths, $medium, $content);
    }

    /**
     * @param string|string[] $paths   The path to the article
     * @param string          $medium  The utm medium
     * @param string          $content The utm content
     *
     * @return string The url with path and utm params
     */
    private static function buildUrl($paths, $medium, $content)
    {
        $utmData = array(
            'utm_source'   => 'WordPress',
            'utm_campaign' => 'liteplugin'
        );

        if ($medium !== '') {
            $utmData['utm_medium'] = $medium;
        }

        if ($content !== '') {
            $utmData['utm_content'] = $content;
        }

        if (is_array($paths)) {
            $paths = implode('/', $paths);
        }
        $paths = trim($paths, '/') . '/';

        return self::DUPLICATOR_URL . $paths . '?' . http_build_query($utmData);
    }
}
