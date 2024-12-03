<?php
/**
 * Plugin Name: Alvobot Pre Article
 * Plugin URI: https://github.com/alvobot/alvobot-pre-article
 * Description: Gere páginas de pré-artigo automaticamente para seus posts existentes.
 * Version: 1.4.14
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
define('ALVOBOT_PRE_ARTICLE_VERSION', '1.4.12');
define('ALVOBOT_PRE_ARTICLE_FILE', __FILE__);
define('ALVOBOT_PRE_ARTICLE_PATH', plugin_dir_path(__FILE__));
define('ALVOBOT_PRE_ARTICLE_GITHUB_REPO', 'alvobot/alvobot-pre-article');

// Carrega as classes do plugin
require_once ALVOBOT_PRE_ARTICLE_PATH . 'includes/class-alvobot-pre-article.php';

/**
 * Inicializa o plugin
 */
function run_alvobot_pre_artigo(): void {
    // Inicializa a classe principal
    $plugin = new Alvobot_Pre_Article();
    $plugin->run();

    // Adiciona os hooks de admin
    if (is_admin()) {
        add_action('admin_init', 'alvobot_check_for_plugin_update');
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'alvobot_add_plugin_action_links');
        add_action('wp_ajax_alvobot_check_update', 'alvobot_ajax_check_update');
    }
}

// Inicia o plugin quando o WordPress carregar
add_action('plugins_loaded', 'run_alvobot_pre_artigo');

/**
 * Verifica se há uma nova versão do plugin disponível
 */
function alvobot_check_for_plugin_update(): void {
    // Verifica se já checou atualizações nas últimas 6 horas
    $last_check = get_transient('alvobot_update_check');
    if ($last_check !== false) {
        return;
    }

    $current_version = ALVOBOT_PRE_ARTICLE_VERSION;
    $repo_url = sprintf('https://api.github.com/repos/%s/releases/latest', ALVOBOT_PRE_ARTICLE_GITHUB_REPO);

    $args = array(
        'headers' => array(
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'WordPress/' . get_bloginfo('version')
        )
    );

    $response = wp_remote_get($repo_url, $args);
    if (is_wp_error($response)) {
        return;
    }

    $release_info = json_decode(wp_remote_retrieve_body($response), true);
    if (!empty($release_info['tag_name'])) {
        $latest_version = ltrim($release_info['tag_name'], 'v');
        if (version_compare($current_version, $latest_version, '<')) {
            update_option('alvobot_update_available', $release_info);
            add_action('admin_notices', 'alvobot_update_notice');
        } else {
            delete_option('alvobot_update_available');
        }
    }

    // Salva o timestamp da última verificação
    set_transient('alvobot_update_check', time(), 6 * HOUR_IN_SECONDS);
}

/**
 * Exibe notificação de atualização disponível
 */
function alvobot_update_notice(): void {
    $update_info = get_option('alvobot_update_available');
    if (empty($update_info)) {
        return;
    }

    $download_url = $update_info['zipball_url'];
    $version = ltrim($update_info['tag_name'], 'v');
    
    printf(
        '<div class="notice notice-warning is-dismissible"><p>%s <strong>%s</strong> %s. <a href="%s" target="_blank">%s</a> | <a href="%s" target="_blank">%s</a></p></div>',
        esc_html__('Nova versão do Alvobot Pre Article disponível:', 'alvobot-pre-artigo'),
        esc_html($version),
        esc_html__('está disponível', 'alvobot-pre-artigo'),
        esc_url($update_info['html_url']),
        esc_html__('Ver detalhes', 'alvobot-pre-artigo'),
        esc_url($download_url),
        esc_html__('Baixar atualização', 'alvobot-pre-artigo')
    );
}

/**
 * Adiciona links de ação do plugin
 */
function alvobot_add_plugin_action_links($links): array {
    $check_update_link = sprintf(
        '<a href="#" class="alvobot-check-update" data-nonce="%s">%s</a>',
        wp_create_nonce('alvobot_check_update'),
        __('Verificar Atualizações', 'alvobot-pre-artigo')
    );
    array_unshift($links, $check_update_link);
    return $links;
}

/**
 * Handler para verificação manual de atualizações via AJAX
 */
function alvobot_ajax_check_update(): void {
    check_ajax_referer('alvobot_check_update');
    
    delete_transient('alvobot_update_check');
    alvobot_check_for_plugin_update();
    
    wp_send_json_success([
        'message' => __('Verificação de atualizações concluída!', 'alvobot-pre-artigo')
    ]);
}

// Adiciona o JavaScript necessário para o botão de verificação de atualização
add_action('admin_footer', function(): void {
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
                    action: 'alvobot_check_update',
                    _ajax_nonce: button.data('nonce')
                },
                success: function(response) {
                    location.reload();
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
});
