<?php

declare(strict_types=1);

class Alvobot_Pre_Article_Updater {
    private string $file;
    private string $plugin;
    private string $basename;
    private string $active;
    private string $username;
    private string $repository;
    private string $github_response;
    private string $authorize_token;
    private string $github_url;

    public function __construct($file) {
        $this->file = $file;
        add_action('admin_init', [$this, 'set_plugin_properties']);

        $this->basename = plugin_basename($file);
        $this->active = is_plugin_active($this->basename);
        $this->username = 'alvobot';
        $this->repository = 'alvobot-pre-article';
        $this->github_url = 'https://api.github.com/repos/' . $this->username . '/' . $this->repository . '/releases/latest';

        add_filter('pre_set_site_transient_update_plugins', [$this, 'modify_transient'], 10, 1);
        add_filter('plugins_api', [$this, 'plugin_popup'], 10, 3);
        add_filter('upgrader_post_install', [$this, 'after_install'], 10, 3);
    }

    public function set_plugin_properties() {
        $this->plugin = get_plugin_data($this->file);
    }

    private function get_repository_info() {
        if (!empty($this->github_response)) {
            return;
        }

        $request_uri = $this->github_url;
        
        $response = wp_remote_get($request_uri);

        if (is_wp_error($response)) {
            return;
        }

        $response_body = wp_remote_retrieve_body($response);
        $response = json_decode($response_body);

        if (is_array($response)) {
            $response = current($response);
        }

        if ($response) {
            $this->github_response = $response;
        }
    }

    public function modify_transient($transient) {
        if (!isset($transient->checked)) {
            return $transient;
        }

        $this->get_repository_info();

        if (!$this->github_response) {
            return $transient;
        }

        $doUpdate = version_compare($this->github_response->tag_name, $transient->checked[$this->basename], 'gt');

        if ($doUpdate) {
            $package = $this->github_response->zipball_url;

            $slug = current(explode('/', $this->basename));

            $plugin = [
                'url' => $this->plugin["PluginURI"],
                'slug' => $slug,
                'package' => $package,
                'new_version' => $this->github_response->tag_name
            ];

            $transient->response[$this->basename] = (object) $plugin;
        }

        return $transient;
    }

    public function plugin_popup($result, $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }

        if (!isset($args->slug) || $args->slug !== current(explode('/', $this->basename))) {
            return $result;
        }

        $this->get_repository_info();

        if (!$this->github_response) {
            return $result;
        }

        $plugin = [
            'name'              => $this->plugin["Name"],
            'slug'              => $this->basename,
            'version'           => $this->github_response->tag_name,
            'author'            => $this->plugin["AuthorName"],
            'author_profile'    => $this->plugin["AuthorURI"],
            'last_updated'      => $this->github_response->published_at,
            'homepage'          => $this->plugin["PluginURI"],
            'short_description' => $this->plugin["Description"],
            'sections'          => [
                'Description'   => $this->plugin["Description"],
                'Updates'       => $this->github_response->body,
                'Changelog'     => $this->get_changelog(),
            ],
            'download_link'     => $this->github_response->zipball_url
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
