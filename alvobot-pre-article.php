<?php
/**
 * Plugin Name: Alvobot Pre Article
 * Plugin URI: https://github.com/alvobot/alvobot-pre-article
 * Description: Gere páginas de pré-artigo automaticamente para seus posts existentes.
 * Version: 1.4.17
 * Author: Alvobot - Cris Franklin
 * Author URI: https://github.com/alvobot
 * Text Domain: alvobot-pre-artigo
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

declare(strict_types=1);

// Se este arquivo for chamado diretamente, aborta
if (!defined('WPINC')) {
    die;
}

// Define constantes do plugin
define('ALVOBOT_PRE_ARTICLE_VERSION', '1.4.16');
define('ALVOBOT_PRE_ARTICLE_FILE', __FILE__);
define('ALVOBOT_PRE_ARTICLE_PATH', plugin_dir_path(__FILE__));
define('ALVOBOT_PRE_ARTICLE_URL', plugin_dir_url(__FILE__));
define('ALVOBOT_PRE_ARTICLE_BASENAME', plugin_basename(__FILE__));

// Carrega as classes do plugin
require_once ALVOBOT_PRE_ARTICLE_PATH . 'includes/class-alvobot-pre-article.php';

/**
 * Inicializa o plugin
 */
function run_alvobot_pre_artigo(): void {
    // Inicializa a classe principal
    $plugin = new Alvobot_Pre_Article();
    $plugin->run();
}

// Inicia o plugin quando o WordPress carregar
add_action('plugins_loaded', 'run_alvobot_pre_artigo');

/**
 * Função para logging (apenas quando WP_DEBUG está ativo)
 */
function alvobot_log($message, $data = null) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[Alvobot Pre Article] ' . $message);
        if ($data !== null) {
            error_log('[Alvobot Pre Article Data] ' . print_r($data, true));
        }
    }
}

// Adiciona o verificador de atualizações
if (!class_exists('Alvobot_Plugin_Updater')) {
    class Alvobot_Plugin_Updater {
        private $file;
        private $plugin;
        private $basename;
        private $active;
        private $github_response;
        private $github_repo = 'alvobot/alvobot-pre-article';
        private $authorize_token;
        private $github_api = 'https://api.github.com/repos/';

        public function __construct($file) {
            $this->file = $file;
            $this->basename = plugin_basename($file);
            
            add_action('admin_init', array($this, 'init'));
            add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'));
            add_filter('plugins_api', array($this, 'plugin_popup'), 10, 3);
            add_filter('upgrader_post_install', array($this, 'after_install'), 10, 3);
            add_filter('plugin_action_links_' . $this->basename, array($this, 'add_action_links'));
            add_action('wp_ajax_alvobot_manual_check_update', array($this, 'handle_manual_check'));
            add_action('admin_footer', array($this, 'add_manual_check_script'));
        }

        public function init() {
            if (!function_exists('is_plugin_active')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $this->active = is_plugin_active($this->basename);
        }

        public function add_action_links($links) {
            $check_link = sprintf(
                '<a href="#" class="alvobot-check-update" data-nonce="%s">%s</a>',
                wp_create_nonce('alvobot_check_update'),
                __('Verificar Atualizações', 'alvobot-pre-artigo')
            );
            array_unshift($links, $check_link);
            return $links;
        }

        public function handle_manual_check() {
            check_ajax_referer('alvobot_check_update');
            
            if (!current_user_can('update_plugins')) {
                wp_send_json_error('Permissão negada');
            }

            delete_site_transient('update_plugins');
            wp_clean_plugins_cache(true);
            wp_update_plugins();

            wp_send_json_success([
                'message' => __('Verificação de atualizações concluída!', 'alvobot-pre-artigo')
            ]);
        }

        public function add_manual_check_script() {
            ?>
            <script>
            jQuery(document).ready(function($) {
                $('.alvobot-check-update').on('click', function(e) {
                    e.preventDefault();
                    const button = $(this);
                    const originalText = button.text();
                    
                    button.text('<?php echo esc_js(__('Verificando...', 'alvobot-pre-artigo')); ?>').addClass('disabled');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'alvobot_manual_check_update',
                            _ajax_nonce: button.data('nonce')
                        },
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                alert('<?php echo esc_js(__('Erro ao verificar atualizações.', 'alvobot-pre-artigo')); ?>');
                                button.text(originalText).removeClass('disabled');
                            }
                        },
                        error: function() {
                            alert('<?php echo esc_js(__('Erro ao verificar atualizações.', 'alvobot-pre-artigo')); ?>');
                            button.text(originalText).removeClass('disabled');
                        }
                    });
                });
            });
            </script>
            <?php
        }

        private function get_repository_info() {
            if (is_null($this->github_response)) {
                $request_uri = $this->github_api . $this->github_repo . '/releases/latest';
                $response = wp_remote_get($request_uri, array(
                    'headers' => array(
                        'Accept' => 'application/vnd.github.v3+json',
                        'User-Agent' => 'WordPress/' . get_bloginfo('version')
                    )
                ));

                if (is_wp_error($response)) {
                    return false;
                }

                $response_code = wp_remote_retrieve_response_code($response);
                if ($response_code !== 200) {
                    return false;
                }

                $response = json_decode(wp_remote_retrieve_body($response));
                if (empty($response)) {
                    return false;
                }

                $this->github_response = $response;
            }
        }

        public function check_update($transient) {
            if (empty($transient->checked)) {
                return $transient;
            }

            $this->get_repository_info();
            if (is_null($this->github_response)) {
                return $transient;
            }

            $doUpdate = version_compare($this->github_response->tag_name, ALVOBOT_PRE_ARTICLE_VERSION, 'gt');
            if ($doUpdate) {
                $package = $this->github_response->zipball_url;

                $plugin = array(
                    'slug' => dirname($this->basename),
                    'plugin' => $this->basename,
                    'new_version' => ltrim($this->github_response->tag_name, 'v'),
                    'url' => 'https://github.com/' . $this->github_repo,
                    'package' => $package,
                    'icons' => array(),
                    'tested' => '6.4',
                    'requires_php' => '7.4'
                );

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
            if (is_null($this->github_response)) {
                return $result;
            }

            $plugin_data = get_plugin_data($this->file);

            $plugin_info = array(
                'name' => $plugin_data['Name'],
                'slug' => dirname($this->basename),
                'version' => ltrim($this->github_response->tag_name, 'v'),
                'author' => $plugin_data['Author'],
                'author_profile' => $plugin_data['AuthorURI'],
                'last_updated' => $this->github_response->published_at,
                'homepage' => $plugin_data['PluginURI'],
                'short_description' => $plugin_data['Description'],
                'sections' => array(
                    'description' => $plugin_data['Description'],
                    'changelog' => nl2br($this->github_response->body)
                ),
                'download_link' => $this->github_response->zipball_url,
                'requires' => '5.8',
                'tested' => '6.4',
                'requires_php' => '7.4'
            );

            return (object) $plugin_info;
        }

        public function after_install($response, $hook_extra, $result) {
            global $wp_filesystem;

            $install_directory = plugin_dir_path($this->file);
            $wp_filesystem->move($result['destination'], $install_directory);
            $result['destination'] = $install_directory;

            if ($this->active) {
                activate_plugin($this->basename);
            }

            // Limpa os caches após a atualização
            delete_site_transient('update_plugins');
            wp_clean_plugins_cache(true);
            wp_update_plugins();

            return $result;
        }
    }
}

// Remove hooks antigos se existirem
remove_filter('plugins_api', 'alvobot_plugin_info', 20);

// Inicializa o atualizador
if (is_admin()) {
    new Alvobot_Plugin_Updater(__FILE__);
}
