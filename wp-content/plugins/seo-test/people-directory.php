<?php
/**
 * Plugin Name: SEO People Directory
 * Description: Simulate a “phantom” programmatic SEO setup by generating profile pages from a CSV and enriching them with external data — without creating real posts or pages in WordPress.
 * Version: 1.0
 */

defined('ABSPATH') or die('Direct access not allowed');

if (version_compare(PHP_VERSION, '7.0', '<')) {
    add_action('admin_notices', function () {
        echo '<div class="error"><p>People Directory requires PHP 7.0 or higher.</p></div>';
    });
    return;
}

define('PD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PD_CACHE_EXPIRE', 24 * HOUR_IN_SECONDS);

require_once PD_PLUGIN_DIR . 'includes/class-loader.php';
require_once PD_PLUGIN_DIR . 'includes/class-router.php';
require_once PD_PLUGIN_DIR . 'includes/class-github-api.php';
require_once PD_PLUGIN_DIR . 'includes/class-sitemap.php';

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'pd-main-styles',
        PD_PLUGIN_URL . 'assets/css/style.css',
        [],
        filemtime(PD_PLUGIN_DIR . 'assets/css/style.css')
    );
});

add_action('plugins_loaded', function () {
    if (!function_exists('register_rest_route')) {
        add_action('admin_notices', function () {
            echo '<div class="error"><p>REST API is not available. People Directory plugin requires WordPress 4.7+.</p></div>';
        });
        return;
    }

    $pd_loader = new PDLoader(PD_PLUGIN_DIR . 'assets/profiles.csv');
    $pd_github = new PDGithubAPI();
    new PDRouter($pd_loader, $pd_github);
    $pd_sitemap = new PDSitemap($pd_loader);

    add_action('do_feed_pd_profiles', function() use ($pd_sitemap) {
        if (get_query_var('feed') === 'pd_profiles') {
            $pd_sitemap->generate_sitemap();
        }
    }, 10, 1);

    add_shortcode('pd_people_list', function () use ($pd_loader) {
        $profiles = $pd_loader->get_all_profiles();
        if (empty($profiles)) return '<p>No profiles found</p>';

        ob_start(); ?>
        <div class="pd-people-list">
            <h1>Our Team</h1>
            <?php foreach ($profiles as $slug => $profile): ?>
                <a href="<?php echo esc_url(home_url("/people/{$slug}/")); ?>">
                    <div class="pd-person">
                        <h3>
                            <?php echo esc_html($profile['name']); ?>
                        </h3>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="pd-sitemap-link">
            <a href="<?php echo esc_url(home_url('/sitemap-people.xml')); ?>">View Sitemap</a>
        </div>
        <?php
        return ob_get_clean();
    });
});