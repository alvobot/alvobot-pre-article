<?php
/**
 * Plugin Name: Alvobot Pre Article
 * Plugin URI: https://github.com/alvobot/alvobot-pre-article
 * Description: Gere páginas de pré-artigo automaticamente para seus posts existentes.
 * Version: 1.4.9
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
define('ALVOBOT_PRE_ARTICLE_VERSION', '1.4.9');
define('ALVOBOT_PRE_ARTICLE_FILE', __FILE__);
define('ALVOBOT_PRE_ARTICLE_PATH', plugin_dir_path(__FILE__));

// Carrega o Plugin Update Checker primeiro
require_once ALVOBOT_PRE_ARTICLE_PATH . 'includes/lib/plugin-update-checker/plugin-update-checker.php';

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

    // Inicializa o sistema de atualizações apenas no admin
    if (is_admin()) {
        new Alvobot_Pre_Article_Github_Updater(ALVOBOT_PRE_ARTICLE_FILE);
    }
}

// Inicia o plugin quando o WordPress carregar
add_action('plugins_loaded', 'run_alvobot_pre_artigo');
