<?php

declare(strict_types=1);

class Alvobot_Pre_Article_Updater {
    private string $file;
    private array $plugin;
    private string $basename;
    private bool $active;
    private string $username;
    private string $repository;
    private ?object $github_response = null;
    private string $github_url;

    public function __construct($file) {
        $this->file = $file;
        
        // Carrega as funções de administração do plugin
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        
        $this->basename = plugin_basename($file);
        $this->active = is_plugin_active($this->basename);
        $this->username = 'alvobot';
        $this->repository = 'alvobot-pre-article';
        $this->github_url = 'https://api.github.com/repos/' . $this->username . '/' . $this->repository . '/releases/latest';
        $this->plugin = get_plugin_data($this->file);

        add_filter('pre_set_site_transient_update_plugins', [$this, 'modify_transient'], 10, 1);
        add_filter('plugins_api', [$this, 'plugin_popup'], 10, 3);
        add_filter('upgrader_post_install', [$this, 'after_install'], 10, 3);
    }

    private function get_repository_info() {
        if ($this->github_response !== null) {
            return;
        }

        $response = wp_remote_get($this->github_url, [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json'
            ]
        ]);

        if (is_wp_error($response)) {
            return;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        if (empty($data)) {
            return;
        }

        // Verifica se a resposta contém os campos necessários
        if (!isset($data->tag_name) || !isset($data->zipball_url)) {
            return;
        }

        $this->github_response = $data;
    }

    public function modify_transient($transient) {
        if (!is_object($transient)) {
            $transient = new stdClass;
        }

        if (!isset($transient->checked)) {
            return $transient;
        }

        $this->get_repository_info();

        if ($this->github_response === null) {
            return $transient;
        }

        $current_version = $this->plugin['Version'] ?? '0.0.0';
        $remote_version = ltrim($this->github_response->tag_name, 'v');

        if (version_compare($remote_version, $current_version, '>')) {
            $plugin = [
                'url' => $this->plugin['PluginURI'] ?? '',
                'slug' => dirname($this->basename),
                'package' => $this->github_response->zipball_url,
                'new_version' => $remote_version,
                'tested' => '6.4.2',
                'requires' => '5.8',
                'requires_php' => '7.4'
            ];

            $transient->response[$this->basename] = (object) $plugin;
        }

        return $transient;
    }

    public function plugin_popup($result, $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }

        if (!isset($args->slug) || $args->slug !== dirname($this->basename)) {
            return $result;
        }

        $this->get_repository_info();

        if ($this->github_response === null) {
            return $result;
        }

        $plugin = [
            'name'              => $this->plugin['Name'],
            'slug'              => $this->basename,
            'version'           => ltrim($this->github_response->tag_name, 'v'),
            'author'            => $this->plugin['Author'],
            'last_updated'      => $this->github_response->published_at,
            'homepage'          => $this->plugin['PluginURI'] ?? '',
            'short_description' => $this->plugin['Description'],
            'sections'          => [
                'Description'   => $this->plugin['Description'],
                'Updates'       => $this->github_response->body ?? 'No update notes available.',
                'Changelog'     => $this->get_changelog(),
            ],
            'download_link'     => $this->github_response->zipball_url,
            'requires'          => '5.8',
            'tested'            => '6.4.2',
            'requires_php'      => '7.4',
            'compatibility'     => [],
        ];

        return (object) $plugin;
    }

    private function get_changelog(): string {
        $changelog_path = plugin_dir_path($this->file) . 'CHANGELOG.md';
        
        if (file_exists($changelog_path)) {
            return file_get_contents($changelog_path);
        }
        
        return 'No changelog available.';
    }

    public function after_install($response, $hook_extra, $result) {
        global $wp_filesystem;

        $install_directory = plugin_dir_path($this->file);
        $wp_filesystem->move($result['destination'], $install_directory);
        $result['destination'] = $install_directory;

        if ($this->active) {
            activate_plugin($this->basename);
        }

        return $result;
    }
}
