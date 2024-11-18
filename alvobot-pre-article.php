<?php
/**
 * Plugin Name: Alvobot Pré-artigo
 * Description: Gera páginas "Pré-artigo" dinâmicas para cada post existente.
 * Version: 1.2.0
 * Author: Alvobot - Cris Franklin
 * Author URI: https://github.com/alvobot
 * Plugin URI: https://github.com/alvobot/alvobot-pre-article
 * Text Domain: alvobot-pre-artigo
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit; // Evita acesso direto
}

// Inclui as classes do plugin
require_once plugin_dir_path(__FILE__) . 'includes/class-alvobot-pre-article.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-alvobot-pre-article-updater.php';

// Inicializa o plugin
function run_alvobot_pre_artigo() {
    // Inicializa o plugin principal
    $plugin = new Alvobot_Pre_Artigo();
    $plugin->run();

    // Inicializa o atualizador
    if (is_admin()) {
        new Alvobot_Pre_Article_Updater(__FILE__);
    }
}

add_action('plugins_loaded', 'run_alvobot_pre_artigo');
