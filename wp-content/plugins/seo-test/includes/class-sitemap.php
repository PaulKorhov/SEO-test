<?php

use JetBrains\PhpStorm\NoReturn;

class PDSitemap
{
    private $loader;

    public function __construct($loader)
    {
        $this->loader = $loader;
        add_action('init', [$this, 'init_sitemap']);
    }

    public function init_sitemap(): void {
        add_feed('pd_profiles', [$this, 'generate_sitemap']);

        add_rewrite_rule(
            '^sitemap-people\.xml$',
            'index.php?feed=pd_profiles',
            'top'
        );

        add_action('wp_head', function() {
            error_log('Current feed query: ' . get_query_var('feed'));
        });
    }

    public function generate_sitemap(): void {
        if (get_query_var('feed') !== 'pd_profiles') {
            status_header(404);
            wp_die('Invalid feed request');
        }

        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/xml; charset=UTF-8');
        header('X-Robots-Tag: noindex, follow');

        $profiles = $this->loader->get_all_profiles();
        error_log('Profiles for sitemap: ' . print_r($profiles, true));

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($profiles as $profile) {
            if (!empty($profile['slug'])) {
                echo '<url>';
                echo '<loc>' . esc_url(home_url('/people/' . $profile['slug'] . '/')) . '</loc>';
                echo '<lastmod>' . date('Y-m-d') . '</lastmod>';
                echo '<changefreq>weekly</changefreq>';
                echo '<priority>0.8</priority>';
                echo '</url>';
            }
        }

        echo '</urlset>';
        exit;
    }
}