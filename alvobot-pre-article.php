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
define('ALVOBOT_PRE_ARTICLE_VERSION', '1.4.12');
define('ALVOBOT_PRE_ARTICLE_FILE', __FILE__);
define('ALVOBOT_PRE_ARTICLE_PATH', plugin_dir_path(__FILE__));
define('ALVOBOT_PRE_ARTICLE_GITHUB_REPO', 'alvobot/alvobot-pre-article');
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

    // Adiciona os hooks de atualização
    if (is_admin()) {
        // Verifica atualizações
        add_filter('pre_set_site_transient_update_plugins', 'alvobot_check_for_update');
        // Mostra informações da versão
        add_filter('plugins_api', 'alvobot_plugin_info', 20, 3);
        // Limpa o cache após a atualização
        add_action('upgrader_process_complete', 'alvobot_after_update', 10, 2);
        // Adiciona botão de verificação manual
        add_filter('plugin_action_links_' . ALVOBOT_PRE_ARTICLE_BASENAME, 'alvobot_add_action_links');
        // Handler do AJAX para verificação manual
        add_action('wp_ajax_alvobot_manual_check_update', 'alvobot_handle_manual_check');
    }
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

/**
 * Verifica se há atualizações disponíveis
 */
function alvobot_check_for_update($transient) {
    if (empty($transient->checked)) {
        return $transient;
    }

    alvobot_log('Verificando atualizações');

    // Verifica a versão mais recente no GitHub
    $response = wp_remote_get(sprintf(
        'https://api.github.com/repos/%s/releases/latest',
        ALVOBOT_PRE_ARTICLE_GITHUB_REPO
    ), array(
        'headers' => array(
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'WordPress/' . get_bloginfo('version')
        ),
        'sslverify' => true,
        'timeout' => 10
    ));

    if (is_wp_error($response)) {
        alvobot_log('Erro na requisição GitHub:', $response->get_error_message());
        return $transient;
    }

    $release_info = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($release_info['tag_name'])) {
        alvobot_log('Informações do release inválidas');
        return $transient;
    }

    $latest_version = ltrim($release_info['tag_name'], 'v');
    alvobot_log('Versão atual:', ALVOBOT_PRE_ARTICLE_VERSION);
    alvobot_log('Última versão:', $latest_version);

    if (version_compare(ALVOBOT_PRE_ARTICLE_VERSION, $latest_version, '<')) {
        $plugin_data = get_plugin_data(ALVOBOT_PRE_ARTICLE_FILE);
        
        // Gera URL para download direto do repositório
        $download_url = sprintf(
            'https://github.com/%s/archive/refs/tags/%s.zip',
            ALVOBOT_PRE_ARTICLE_GITHUB_REPO,
            $release_info['tag_name']
        );
        
        alvobot_log('URL de download:', $download_url);

        $transient->response[ALVOBOT_PRE_ARTICLE_BASENAME] = (object) array(
            'slug' => dirname(ALVOBOT_PRE_ARTICLE_BASENAME),
            'plugin' => ALVOBOT_PRE_ARTICLE_BASENAME,
            'new_version' => $latest_version,
            'url' => $plugin_data['PluginURI'],
            'package' => $download_url,
            'icons' => array(),
            'banners' => array(),
            'banners_rtl' => array(),
            'tested' => '6.4',
            'requires_php' => '7.4',
            'compatibility' => new stdClass(),
        );

        alvobot_log('Atualização encontrada e configurada');
    }

    return $transient;
}

/**
 * Ajusta o diretório após o download do GitHub
 */
function alvobot_upgrader_source_selection($source, $remote_source, $upgrader, $hook_extra) {
    global $wp_filesystem;

    alvobot_log('Iniciando source_selection');
    alvobot_log('Source:', $source);
    alvobot_log('Remote Source:', $remote_source);
    
    if (!isset($hook_extra['plugin']) || $hook_extra['plugin'] !== ALVOBOT_PRE_ARTICLE_BASENAME) {
        return $source;
    }

    // Define o nome do diretório de destino
    $target_directory = trailingslashit($remote_source) . 'alvobot-pre-article';
    
    // Se o diretório de destino já existe, remove-o
    if ($wp_filesystem->exists($target_directory)) {
        $wp_filesystem->delete($target_directory, true);
    }
    
    // Cria o novo diretório
    $wp_filesystem->mkdir($target_directory);
    
    // Lista todos os arquivos no diretório fonte
    $files = $wp_filesystem->dirlist($source);
    
    // Se houver apenas uma pasta (que é o caso do GitHub), use-a como fonte
    $subdirs = array_filter($files, function($file) {
        return $file['type'] === 'd';
    });
    
    if (count($subdirs) === 1) {
        $github_dir = trailingslashit($source) . key($subdirs);
        $source = $github_dir;
    }
    
    // Move todos os arquivos para o novo diretório
    $upgrader->move_files($source, $target_directory);
    
    // Se o diretório original ainda existe e está vazio, remove-o
    if ($wp_filesystem->exists($source)) {
        $remaining_files = $wp_filesystem->dirlist($source);
        if (empty($remaining_files)) {
            $wp_filesystem->delete($source, true);
        }
    }
    
    alvobot_log('Arquivos movidos para:', $target_directory);
    return $target_directory;
}

/**
 * Limpa o cache após a atualização
 */
function alvobot_after_update($upgrader_object, $options) {
    if ($options['action'] === 'update' && $options['type'] === 'plugin') {
        if (isset($options['plugins'])) {
            foreach ($options['plugins'] as $plugin) {
                if ($plugin === ALVOBOT_PRE_ARTICLE_BASENAME) {
                    // Força recarregamento dos plugins
                    wp_clean_plugins_cache(true);
                    // Limpa o cache de atualizações
                    delete_site_transient('update_plugins');
                    // Recarrega a lista de plugins
                    wp_update_plugins();
                    break;
                }
            }
        }
    }
}

/**
 * Adiciona links de ação ao plugin
 */
function alvobot_add_action_links($links) {
    $check_link = sprintf(
        '<a href="#" class="alvobot-check-update" data-nonce="%s">%s</a>',
        wp_create_nonce('alvobot_check_update'),
        __('Verificar Atualizações', 'alvobot-pre-artigo')
    );
    array_unshift($links, $check_link);
    return $links;
}

/**
 * Handler para verificação manual de atualizações
 */
function alvobot_handle_manual_check() {
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

// Remove hooks antigos
remove_filter('upgrader_source_selection', 'alvobot_upgrader_source_selection', 10);
remove_filter('upgrader_pre_download', 'alvobot_pre_download', 10);

// Adiciona os hooks necessários
add_filter('upgrader_source_selection', 'alvobot_upgrader_source_selection', 10, 4);
add_action('upgrader_process_complete', 'alvobot_after_update', 10, 2);
add_filter('plugin_action_links_' . ALVOBOT_PRE_ARTICLE_BASENAME, 'alvobot_add_action_links');
add_action('wp_ajax_alvobot_manual_check_update', 'alvobot_handle_manual_check');

// Adiciona o JavaScript para o botão de verificação manual
add_action('admin_footer', function() {
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
});
