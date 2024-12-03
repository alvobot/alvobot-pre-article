<?php
/**
 * Plugin Name: Alvobot Pre Article
 * Plugin URI: https://github.com/alvobot/alvobot-pre-article
 * Description: Gere páginas de pré-artigo automaticamente para seus posts existentes.
 * Version: 1.4.6
 * Author: Alvobot - Cris Franklin
 * Author URI: https://github.com/alvobot
 * Text Domain: alvobot-pre-artigo
 */

if (!defined('WPINC')) {
    die;
}

require_once plugin_dir_path(__FILE__) . 'includes/class-alvobot-pre-article.php';

// Carrega o Plugin Update Checker
require_once plugin_dir_path(__FILE__) . 'vendor/plugin-update-checker/plugin-update-checker.php';

function run_alvobot_pre_artigo() {
    $plugin = new Alvobot_Pre_Article();
    $plugin->run();

    // Configura o atualizador
    if (class_exists('Puc_v4_Factory')) {
        $updateChecker = Puc_v4_Factory::buildUpdateChecker(
            'https://api.github.com/repos/alvobot/alvobot-pre-article/releases',
            __FILE__,
            'alvobot-pre-article'
        );

        // Configura para usar releases do GitHub
        $updateChecker->getVcsApi()->enableReleaseAssets();
        $updateChecker->setAuthentication(''); // Remove autenticação para repos públicos
    }
}

run_alvobot_pre_artigo();
