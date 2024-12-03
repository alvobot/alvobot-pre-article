<?php
/**
 * Gerenciador de atualizações via GitHub
 *
 * @package Alvobot_Pre_Article
 */

declare(strict_types=1);

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

class Alvobot_Pre_Article_Github_Updater {
    /**
     * O caminho completo do arquivo principal do plugin
     *
     * @var string
     */
    private string $plugin_file;

    /**
     * O nome base do plugin (diretório/arquivo)
     *
     * @var string
     */
    private string $plugin_basename;

    /**
     * O slug do plugin
     *
     * @var string
     */
    private string $plugin_slug;

    /**
     * Instância do Plugin Update Checker
     *
     * @var \YahnisElsts\PluginUpdateChecker\v5\PucFactory
     */
    private $update_checker;

    /**
     * Constructor
     *
     * @param string $plugin_file Caminho completo do arquivo principal do plugin
     */
    public function __construct(string $plugin_file) {
        $this->plugin_file = $plugin_file;
        $this->plugin_basename = plugin_basename($plugin_file);
        $this->plugin_slug = dirname($this->plugin_basename);
        
        $this->init();
    }

    /**
     * Inicializa o sistema de atualizações
     */
    private function init(): void {
        // Configura o atualizador
        $this->update_checker = PucFactory::buildUpdateChecker(
            'https://github.com/alvobot/alvobot-pre-article/',
            $this->plugin_file,
            'alvobot-pre-article'
        );

        // Configura para usar releases do GitHub
        $this->update_checker->getVcsApi()->enableReleaseAssets();
        
        // Remove autenticação para repos públicos
        $this->update_checker->setAuthentication('');

        // Desabilita o botão padrão de verificação
        $this->update_checker->setOption('enable-auto-updates', false);
        $this->update_checker->setOption('manual-check-link', false);

        // Adiciona hooks do WordPress
        $this->add_hooks();
    }

    /**
     * Adiciona os hooks necessários
     */
    private function add_hooks(): void {
        // Adiciona link de verificação manual
        add_filter(
            'plugin_action_links_' . $this->plugin_basename,
            [$this, 'add_check_update_link']
        );

        // Adiciona JavaScript necessário
        add_action('admin_footer', [$this, 'add_check_update_script']);

        // Adiciona endpoint AJAX
        add_action('wp_ajax_alvobot_check_updates', [$this, 'handle_manual_check']);
    }

    /**
     * Adiciona link para verificação manual de atualizações
     *
     * @param array $links Links existentes
     * @return array Links modificados
     */
    public function add_check_update_link(array $links): array {
        if (current_user_can('update_plugins')) {
            $check_update_link = sprintf(
                '<a href="#" class="alvobot-check-updates" data-plugin="%s">%s</a>',
                esc_attr($this->plugin_basename),
                esc_html__('Verificar atualização', 'alvobot-pre-artigo')
            );
            array_unshift($links, $check_update_link);
        }
        return $links;
    }

    /**
     * Adiciona o JavaScript necessário para a verificação manual
     */
    public function add_check_update_script(): void {
        // Só adiciona na página de plugins
        $screen = get_current_screen();
        if (!$screen || $screen->base !== 'plugins') {
            return;
        }
        ?>
        <script>
        jQuery(document).ready(function($) {
            $('.alvobot-check-updates').on('click', function(e) {
                e.preventDefault();
                
                var $button = $(this);
                var originalText = $button.text();
                
                // Desativa o botão e mostra loading
                $button.text('<?php echo esc_js(__('Verificando...', 'alvobot-pre-artigo')); ?>').addClass('disabled');
                
                // Faz a requisição AJAX
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'alvobot_check_updates',
                        plugin: $button.data('plugin'),
                        _ajax_nonce: '<?php echo wp_create_nonce('alvobot_check_updates'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Recarrega a página para mostrar atualizações
                            location.reload();
                        } else {
                            alert(response.data.message || '<?php echo esc_js(__('Erro ao verificar atualizações.', 'alvobot-pre-artigo')); ?>');
                            $button.text(originalText).removeClass('disabled');
                        }
                    },
                    error: function() {
                        alert('<?php echo esc_js(__('Erro ao verificar atualizações.', 'alvobot-pre-artigo')); ?>');
                        $button.text(originalText).removeClass('disabled');
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * Manipula a verificação manual de atualizações
     */
    public function handle_manual_check(): void {
        // Verifica nonce e permissões
        check_ajax_referer('alvobot_check_updates');
        if (!current_user_can('update_plugins')) {
            wp_send_json_error(['message' => __('Permissão negada.', 'alvobot-pre-artigo')]);
        }

        try {
            // Força uma nova verificação
            delete_site_transient('update_plugins');
            wp_clean_plugins_cache(true);
            
            // Força o Update Checker a verificar novamente
            $this->update_checker->checkForUpdates();
            
            wp_send_json_success(['message' => __('Verificação concluída.', 'alvobot-pre-artigo')]);
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
}
