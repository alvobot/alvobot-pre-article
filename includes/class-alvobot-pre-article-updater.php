<?php

declare(strict_types=1);

class Alvobot_Pre_Article_Updater {
    private string $file;
    private string $plugin;
    private string $basename;
    private bool $active;
    private array $github_data;
    private bool $cache_allowed;

    public function __construct($file) {
        $this->file = $file;
        $this->basename = plugin_basename($file);
        
        // Inclui o arquivo necessário para is_plugin_active
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        $this->active = is_plugin_active($this->basename);
        
        // Define se podemos usar cache
        $this->cache_allowed = (bool) apply_filters('alvobot_pre_article_allow_cache', true);

        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'));
        add_filter('plugins_api', array($this, 'plugins_api_filter'), 10, 3);
    }

    public function check_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        // Pega informações do GitHub
        $remote_version = $this->get_remote_version();
        if (!$remote_version) {
            return $transient;
        }

        // Pega a versão atual do plugin
        $plugin_data = get_plugin_data($this->file);
        $current_version = $plugin_data['Version'];

        // Compara versões
        if (version_compare($current_version, $remote_version, '<')) {
            $plugin_info = array(
                'slug' => dirname($this->basename),
                'plugin' => $this->basename,
                'new_version' => $remote_version,
                'package' => $this->get_download_url($remote_version)
            );
            $transient->response[$this->basename] = (object) $plugin_info;
        }

        return $transient;
    }

    public function plugins_api_filter($result, $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }

        if (!isset($args->slug) || $args->slug !== dirname($this->basename)) {
            return $result;
        }

        $remote_data = $this->get_remote_data();
        if (!$remote_data) {
            return $result;
        }

        $plugin_data = get_plugin_data($this->file);
        
        return (object) array(
            'name' => $plugin_data['Name'],
            'slug' => $args->slug,
            'version' => $remote_data['tag_name'],
            'requires' => '5.8',
            'requires_php' => '7.4',
            'author' => $plugin_data['Author'],
            'sections' => array(
                'description' => $plugin_data['Description'],
                'changelog' => $remote_data['body']
            ),
            'download_link' => $this->get_download_url($remote_data['tag_name'])
        );
    }

    private function get_remote_data() {
        if ($this->cache_allowed) {
            $cached = get_transient('alvobot_pre_article_github_data');
            if ($cached) {
                return $cached;
            }
        }

        $response = wp_remote_get('https://api.github.com/repos/alvobot/alvobot-pre-article/releases');
        
        if (is_wp_error($response)) {
            return false;
        }

        $releases = json_decode(wp_remote_retrieve_body($response), true);
        
        if (empty($releases) || !is_array($releases)) {
            return false;
        }

        // Pega o release mais recente que não seja um pre-release
        $latest_release = null;
        foreach ($releases as $release) {
            if (!$release['prerelease']) {
                $latest_release = $release;
                break;
            }
        }

        if (!$latest_release) {
            return false;
        }

        if ($this->cache_allowed) {
            set_transient('alvobot_pre_article_github_data', $latest_release, 12 * HOUR_IN_SECONDS);
        }

        return $latest_release;
    }

    private function get_remote_version() {
        $data = $this->get_remote_data();
        if (!$data) {
            return false;
        }
        return ltrim($data['tag_name'], 'v');
    }

    private function get_download_url($version) {
        return sprintf(
            'https://github.com/alvobot/alvobot-pre-article/archive/refs/tags/v%s.zip',
            ltrim($version, 'v')
        );
    }

    public function clear_cache() {
        delete_transient('alvobot_pre_article_github_data');
    }
}
