<?php
class PDGithubAPI {
    private string $cache_prefix = 'ppd_github_';

    public function get_profile_data($username) {
        $cache_key = $this->cache_prefix . $username;
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        $response = wp_remote_get("https://api.github.com/users/{$username}", [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json'
            ]
        ]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        set_transient($cache_key, $data, PD_CACHE_EXPIRE);

        return $data;
    }
}