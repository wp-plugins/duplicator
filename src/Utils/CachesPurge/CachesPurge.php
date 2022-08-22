<?php

namespace Duplicator\Utils\CachesPurge;

class CachesPurge
{
    /**
     * purge all and return purge messages
     *
     * @return string[]
     */
    public static function purgeAll()
    {
        $globalMessages = array();
        $items          = array_merge(
            self::getPurgePlugins(),
            self::getPurgeHosts()
        );


        foreach ($items as $item) {
            $message = '';
            $result  = $item->purge($message);
            if (strlen($message) > 0 && $result) {
                $globalMessages[] = $message;
            }
        }

        return $globalMessages;
    }

    /**
     * get list to cache items to purge
     *
     * @return CacheItem[]
     */
    protected static function getPurgePlugins()
    {
        $items   = array();
        $items[] = new CacheItem(
            'Elementor',
            function () {
                return class_exists("\\Elementor\\Plugin");
            },
            function () {
                \Elementor\Plugin::$instance->files_manager->clear_cache();
            }
        );
        $items[] = new CacheItem(
            'W3 Total Cache',
            function () {
                return function_exists('w3tc_pgcache_flush');
            },
            'w3tc_pgcache_flush'
        );
        $items[] = new CacheItem(
            'WP Super Cache',
            function () {
                return function_exists('wp_cache_clear_cache');
            },
            'wp_cache_clear_cache'
        );
        $items[] = new CacheItem(
            'WP Rocket',
            function () {
                return function_exists('rocket_clean_domain');
            },
            'rocket_clean_domain'
        );
        $items[] = new CacheItem(
            'Fast velocity minify',
            function () {
                return function_exists('fvm_purge_static_files');
            },
            'fvm_purge_static_files'
        );
        $items[] = new CacheItem(
            'Cachify',
            function () {
                return function_exists('cachify_flush_cache');
            },
            'cachify_flush_cache'
        );
        $items[] = new CacheItem(
            'Comet Cache',
            function () {
                return class_exists('\\comet_cache');
            },
            array('\\comet_cache', 'clear')
        );
        $items[] = new CacheItem(
            'Zen Cache',
            function () {
                return class_exists('\\zencache');
            },
            array('\\zencache', 'clear')
        );
        $items[] = new CacheItem(
            'LiteSpeed Cache',
            function () {
                return has_action('litespeed_purge_all');
            },
            function () {
                return  do_action('litespeed_purge_all');
            }
        );
        $items[] = new CacheItem(
            'WP Cloudflare Super Page Cache',
            function () {
                return class_exists('\\SW_CLOUDFLARE_PAGECACHE');
            },
            function () {
                return  do_action("swcfpc_purge_everything");
            }
        );
        $items[] = new CacheItem(
            'Hyper Cache',
            function () {
                return class_exists('\\HyperCache');
            },
            function () {
                return  do_action('autoptimize_action_cachepurged');
            }
        );
        $items[] = new CacheItem(
            'Cache Enabler',
            function () {
                return has_action('ce_clear_cache');
            },
            function () {
                return  do_action('ce_clear_cache');
            }
        );
        $items[] = new CacheItem(
            'WP Fastest Cache',
            function () {
                return function_exists('wpfc_clear_all_cache');
            },
            function () {
                wpfc_clear_all_cache(true);
            }
        );
        $items[] = new CacheItem(
            'Breeze',
            function () {
                return class_exists("\\Breeze_PurgeCache");
            },
            array('\\Breeze_PurgeCache', 'breeze_cache_flush')
        );
        $items[] = new CacheItem(
            'Swift Performance',
            function () {
                return class_exists("\\Swift_Performance_Cache");
            },
            array('\\Swift_Performance_Cache', 'clear_all_cache')
        );
        $items[] = new CacheItem(
            'Hummingbird',
            function () {
                return has_action('wphb_clear_page_cache');
            },
            function () {
                return  do_action('wphb_clear_page_cache');
            }
        );
        $items[] = new CacheItem(
            'WP-Optimize',
            function () {
                return has_action('wpo_cache_flush');
            },
            function () {
                return  do_action('wpo_cache_flush');
            }
        );
        $items[] = new CacheItem(
            'Wordpress default',
            function () {
                return function_exists('wp_cache_flush');
            },
            'wp_cache_flush'
        );
        $items[] = new CacheItem(
            'Wordpress permalinks',
            function () {
                return function_exists('flush_rewrite_rules');
            },
            'flush_rewrite_rules'
        );
        return $items;
    }

    /**
     * get list to cache items to purge
     *
     * @return CacheItem[]
     */
    protected static function getPurgeHosts()
    {
        $items   = array();
        $items[] = new CacheItem(
            'Godaddy Managed WordPress Hosting',
            function () {
                return class_exists('\\WPaaS\\Plugin') && method_exists('\\WPass\\Plugin', 'vip');
            },
            function () {
                $method = 'BAN';
                $url    = home_url();
                $host   = wpraiser_get_domain();
                $url    = set_url_scheme(str_replace($host, \WPaas\Plugin::vip(), $url), 'http');
                update_option('gd_system_last_cache_flush', time(), 'no'); # purge apc
                wp_remote_request(
                    esc_url_raw($url),
                    array(
                        'method' => $method,
                        'blocking' => false,
                        'headers' =>
                        array(
                            'Host' => $host
                        )
                    )
                );
            }
        );
        $items[] = new CacheItem(
            'SG Optimizer (Siteground)',
            function () {
                return function_exists('sg_cachepress_purge_everything');
            },
            'sg_cachepress_purge_everything'
        );
        $items[] = new CacheItem(
            'WP Engine',
            function () {
                return (class_exists("\\WpeCommon") &&
                    (method_exists('\\WpeCommon', 'purge_memcached') ||
                        method_exists('\\WpeCommon', 'purge_varnish_cache')));
            },
            function () {
                if (method_exists('\\WpeCommon', 'purge_memcached')) {
                    \WpeCommon::purge_memcached();
                }
                if (method_exists('\\WpeCommon', 'purge_varnish_cache')) {
                    \WpeCommon::purge_varnish_cache();
                }
            }
        );
        $items[] = new CacheItem(
            'Kinsta',
            function () {
                global $kinsta_cache;
                return (
                    (isset($kinsta_cache) &&
                        class_exists('\\Kinsta\\CDN_Enabler')) &&
                    !empty($kinsta_cache->kinsta_cache_purge));
            },
            function () {
                global $kinsta_cache;
                $kinsta_cache->kinsta_cache_purge->purge_complete_caches();
            }
        );
        $items[] = new CacheItem(
            'Pagely',
            function () {
                return class_exists('\\PagelyCachePurge');
            },
            function () {
                $purge_pagely = new \PagelyCachePurge();
                $purge_pagely->purgeAll();
            }
        );
        $items[] = new CacheItem(
            'Pressidum',
            function () {
                return defined('WP_NINUKIS_WP_NAME') && class_exists('\\Ninukis_Plugin');
            },
            function () {
                $purge_pressidum = \Ninukis_Plugin::get_instance();
                $purge_pressidum->purgeAllCaches();
            }
        );

        $items[] = new CacheItem(
            'Pantheon Advanced Page Cache plugin',
            function () {
                return function_exists('pantheon_wp_clear_edge_all');
            },
            'pantheon_wp_clear_edge_all'
        );
        return $items;
    }
}
