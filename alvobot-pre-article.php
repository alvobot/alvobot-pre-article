<?php
/**
 * Plugin Name: Alvobot Pre Article
 * Plugin URI: https://github.com/alvobot/alvobot-pre-article
 * Description: Gere páginas de pré-artigo automaticamente para seus posts existentes.
 * Version: 1.4.12
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

// Carrega as classes do plugin
require_once ALVOBOT_PRE_ARTICLE_PATH . 'includes/class-alvobot-pre-article.php';
require_once ALVOBOT_PRE_ARTICLE_PATH . 'includes/class-alvobot-pre-article-github-updater.php';

/**
 * Inicializa o plugin
 */
function run_alvobot_pre_artigo(): void {
    // Inicializa a classe principal
    $plugin = new Alvobot_Pre_Article();
    $plugin->run();

    // Adiciona verificação de atualização
    if (is_admin()) {
        add_action('admin_init', 'check_for_plugin_update');
    }
}

// Inicia o plugin quando o WordPress carregar
add_action('plugins_loaded', 'run_alvobot_pre_artigo');

/**
 * Verifica se há uma nova versão do plugin disponível
 */
function check_for_plugin_update() {
    $current_version = ALVOBOT_PRE_ARTICLE_VERSION;
    $repo_url = 'https://api.github.com/repos/alvobot/alvobot-pre-article/releases/latest';

    $response = wp_remote_get($repo_url);
    if (is_wp_error($response)) {
        return;
    }

    $release_info = json_decode(wp_remote_retrieve_body($response), true);
    if (version_compare($current_version, $release_info['tag_name'], '<')) {
        // Nova versão disponível
        add_action('admin_notices', function() use ($release_info) {
            echo '<div class="notice notice-warning is-dismissible">
                <p>Uma nova versão do Alvobot Pre Article está disponível. <a href="' . esc_url($release_info['html_url']) . '">Clique aqui para atualizar</a>.</p>
            </div>';
        });
    }
}
