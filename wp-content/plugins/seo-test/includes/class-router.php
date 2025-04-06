<?php

class PDRouter
{
    private $loader;
    private $github;

    public function __construct($loader, $github)
    {
        $this->loader = $loader;
        $this->github = $github;

        add_action('init', [$this, 'add_rewrite_rules']);
        add_action('template_redirect', [$this, 'handle_profile_request']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }

    public function add_rewrite_rules(): void
    {
        add_rewrite_rule(
            '^people/([^/]+)/?$',
            'index.php?pd_profile=$matches[1]',
            'top'
        );

        add_rewrite_rule(
            '^feed/ppd_profiles/?$',
            'index.php?feed=ppd_profiles',
            'top'
        );
    }

    public function register_rest_routes(): void
    {
        register_rest_route('pd/v1', '/people/', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_all_people_rest'],
            'permission_callback' => '__return_true'
        ]);

        register_rest_route('pd/v1', '/people/(?P<slug>[a-zA-Z0-9-]+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_single_person_rest'],
            'permission_callback' => '__return_true',
            'args' => [
                'slug' => [
                    'validate_callback' => function ($param) {
                        return is_string($param);
                    }
                ]
            ]
        ]);
    }

    public function add_query_vars($vars)
    {
        $vars[] = 'pd_profile';
        return $vars;
    }

    public function handle_profile_request(): void
    {
        $slug = get_query_var('pd_profile');
        if (!$slug) return;

        if (!$this->loader->profile_exists($slug)) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            return;
        }

        $profile = $this->loader->get_profile($slug);
        $github_data = $this->github->get_profile_data($profile['github_username']);

        $this->render_profile($profile, $github_data);
        exit;
    }

    private function render_profile($profile, $github_data): void
    {
        status_header(200);
        get_header();
        ?>
        <div class="pd-profile">
            <h1><?php echo esc_html($profile['name']); ?></h1>

            <?php if ($github_data): ?>
                <div class="github-info">
                    <div class="github-info-left">
                        <img src="<?php echo esc_url($github_data['avatar_url']); ?>" alt="GitHub Avatar" width="100">
                    </div>
                    <div class="github-info-right">
                        <?php if ($github_data['bio']) : ?>
                            <p><?php echo esc_html($github_data['bio']); ?></p>
                        <?php endif; ?>
                        <ul>
                            <li>Repositories: <?php echo esc_html($github_data['public_repos']); ?></li>
                            <li>Followers: <?php echo esc_html($github_data['followers']); ?></li>
                            <li>Following: <?php echo esc_html($github_data['following']); ?></li>
                        </ul>
                        <a href="<?php echo esc_url($github_data['html_url']); ?>" target="_blank">View on GitHub</a>
                    </div>
                </div>
            <?php else: ?>
                <p>GitHub data not available</p>
            <?php endif; ?>
        </div>
        <?php
        get_footer();
    }
}