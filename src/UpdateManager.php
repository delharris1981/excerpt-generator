<?php

declare(strict_types=1);

namespace LuhnSummarizer;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class UpdateManager
 * Handles checking for updates from a public GitHub repository.
 */
class UpdateManager
{

    private string $slug;
    private string $plugin_file;
    private string $username = 'delharris1981'; // Placeholder
    private string $repo = 'excerpt-generator'; // Placeholder

    public function __construct(string $plugin_file)
    {
        $this->plugin_file = $plugin_file;
        $this->slug = plugin_basename($plugin_file);

        add_filter('site_transient_update_plugins', [$this, 'check_for_update']);
        add_filter('plugins_api', [$this, 'plugin_popup_info'], 20, 3);
        add_action('wp_login', [$this, 'force_check_on_login'], 10, 2);
    }

    /**
     * Check for updates in the GitHub repository.
     */
    public function check_for_update($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        $remote_data = $this->get_remote_release();
        if (!$remote_data) {
            return $transient;
        }

        $current_version = $transient->checked[$this->slug] ?? '0.0.0';
        $remote_version = ltrim($remote_data['tag_name'], 'v');

        if (version_compare($current_version, $remote_version, '<')) {
            $obj = new \stdClass();
            $obj->slug = $this->slug;
            $obj->new_version = $remote_version;
            $obj->url = $remote_data['html_url'];
            $obj->package = $remote_data['zipball_url']; // GitHub provides zipball_url for releases

            $transient->response[$this->slug] = $obj;
        }

        return $transient;
    }

    /**
     * Fetch the latest release from GitHub API.
     */
    private function get_remote_release(): ?array
    {
        $cache_key = 'luhn_summarizer_update_check';
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached === 'none' ? null : $cached;
        }

        $url = "https://api.github.com/repos/{$this->username}/{$this->repo}/releases/latest";

        $response = wp_remote_get($url, [
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress/LuhnSummarizer-Plugin'
            ],
        ]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            set_transient($cache_key, 'none', 12 * HOUR_IN_SECONDS);
            return null;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($data) || !isset($data['tag_name'])) {
            set_transient($cache_key, 'none', 12 * HOUR_IN_SECONDS);
            return null;
        }

        set_transient($cache_key, $data, 12 * HOUR_IN_SECONDS);
        return $data;
    }

    /**
     * Provide information for the "View Details" popup.
     */
    public function plugin_popup_info($result, $action, $args)
    {
        if ($action !== 'plugin_information') {
            return $result;
        }

        if ($args->slug !== $this->slug) {
            return $result;
        }

        $remote_data = $this->get_remote_release();
        if (!$remote_data) {
            return $result;
        }

        $res = new \stdClass();
        $res->name = 'Luhn Excerpt Generator';
        $res->slug = $this->slug;
        $res->version = ltrim($remote_data['tag_name'], 'v');
        $res->author = 'Antigravity AI';
        $res->homepage = $remote_data['html_url'];
        $res->download_link = $remote_data['zipball_url'];
        $res->sections = [
            'description' => __('A powerful WordPress plugin for automated excerpt generation using the Luhn algorithm.', 'luhn-summarizer'),
            'changelog' => isset($remote_data['body']) ? wp_kses_post($remote_data['body']) : __('No changelog provided.', 'luhn-summarizer'),
        ];

        return $res;
    }

    /**
     * Forces an update check when an administrator logs in.
     */
    public function force_check_on_login(string $user_login, \WP_User $user): void
    {
        if (user_can($user, 'manage_options')) {
            // Clear our local 12-hour cache
            delete_transient('luhn_summarizer_update_check');

            // Force WordPress to refresh plugin update data
            delete_site_transient('update_plugins');
        }
    }
}
