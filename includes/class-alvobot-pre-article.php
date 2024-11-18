<?php

declare(strict_types=1);

class Alvobot_Pre_Artigo {

    public function run() {
        // Registra as regras de reescrita de URL
        add_action('init', [$this, 'register_rewrite_rules']);
        // Adiciona a variável de query
        add_filter('query_vars', [$this, 'add_query_vars']);
        // Modifica a query principal
        add_action('pre_get_posts', [$this, 'modify_main_query']);
        // Carrega o template personalizado
        add_filter('template_include', [$this, 'load_pre_article_template']);
        // Enfileira scripts e estilos
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        // Adiciona configurações no admin
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);

        // Adiciona meta box na edição de post
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_meta_box_data']);

        // Enfileira scripts no admin
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);

        // Registra endpoints da REST API
        add_action('rest_api_init', [$this, 'register_rest_routes']);

        // Reescreve as regras ao ativar o plugin
        register_activation_hook(__FILE__, [$this, 'flush_rewrite_rules']);
        // Reescreve as regras ao desativar o plugin
        register_deactivation_hook(__FILE__, [$this, 'flush_rewrite_rules']);
    }

    public function register_rewrite_rules() {
        add_rewrite_rule('^pre/([^/]+)/?', 'index.php?pre_article=1&name=$matches[1]', 'top');
    }

    public function add_query_vars($vars) {
        $vars[] = 'pre_article';
        return $vars;
    }

    public function modify_main_query($query) {
        if (!is_admin() && $query->is_main_query() && get_query_var('pre_article')) {
            $query->set('post_type', 'post');
            $query->set('name', get_query_var('name'));
            $query->set('posts_per_page', 1);
            $query->set('post_status', 'publish');
        }
    }

    public function load_pre_article_template($template) {
        if (get_query_var('pre_article')) {
            // Carrega o post atual
            $post = get_post();
            // Verifica se o post usa configurações personalizadas
            $use_custom = get_post_meta($post->ID, '_alvobot_use_custom', true);
            if ($use_custom === '1') {
                // Carrega as CTAs personalizadas
                $ctas = get_post_meta($post->ID, '_alvobot_ctas', true);
            } else {
                // Usa as configurações padrão do plugin
                $options = get_option('alvobot_pre_artigo_options');
                $num_ctas = $options['num_ctas'] ?? 2;
                $ctas = [];
                for ($i = 1; $i <= $num_ctas; $i++) {
                    $ctas[] = [
                        'text' => $options["button_text_{$i}"] ?? '',
                        'color' => $options["button_color_{$i}"] ?? '#1E73BE',
                    ];
                }
            }
            // Disponibiliza as CTAs para o template
            set_query_var('alvobot_ctas', $ctas);
    
            // Corrige o caminho do template
            $plugin_dir = plugin_dir_path(__FILE__);
            $template_path = $plugin_dir . '/templates/template-pre-article.php';
    
            // Verifica se o arquivo existe
            if (file_exists($template_path)) {
                return $template_path;
            } else {
                // Log para depuração
                error_log('Template não encontrado: ' . $template_path);
                // Opcionalmente, retornar um template padrão ou uma mensagem de erro
                return $template;
            }
        }
        return $template;
    }    

    public function enqueue_scripts() {
        if (get_query_var('pre_article')) {
            wp_enqueue_style(
                'alvobot-pre-artigo-style',
                plugin_dir_url(dirname(__FILE__)) . 'assets/css/style.css',
                [],
                '1.0.0'
            );
        }
    }

    // Modifica o menu do admin
    public function add_admin_menu() {
        add_menu_page(
            'Alvobot',
            'Alvobot',
            'manage_options',
            'alvobot-pre-artigo',
            [$this, 'create_admin_page'],
            'dashicons-admin-generic',
            6
        );
    }    

    public function create_admin_page() {
        $options = get_option('alvobot_pre_artigo_options');
        $num_ctas = $options['num_ctas'] ?? 2;
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Configurações do Alvobot Pré-artigo', 'alvobot-pre-artigo'); ?></h1>
            
            <form action="options.php" method="POST">
                <?php settings_fields('alvobot_pre_artigo_settings'); ?>
                
                <div class="card">
                    <h2><?php _e('Configurações dos Botões CTA', 'alvobot-pre-artigo'); ?></h2>
                    
                    <p>
                        <label for="num_ctas"><strong><?php _e('Quantidade de CTAs:', 'alvobot-pre-artigo'); ?></strong></label>
                        <input 
                            type="number" 
                            id="num_ctas" 
                            name="alvobot_pre_artigo_options[num_ctas]" 
                            value="<?php echo esc_attr($num_ctas); ?>" 
                            min="1" 
                            max="10" 
                            class="small-text"
                        />
                        <p class="description"><?php _e('Defina a quantidade padrão de CTAs (máximo 10).', 'alvobot-pre-artigo'); ?></p>
                    </p>

                    <div id="ctas_container" class="ctas-grid">
                        <?php
                        for ($i = 1; $i <= $num_ctas; $i++) {
                            $button_text = $options["button_text_{$i}"] ?? '';
                            $button_color = $options["button_color_{$i}"] ?? '#1E73BE';
                            ?>
                            <div class="cta-box">
                                <h3><?php printf(__('CTA %d', 'alvobot-pre-artigo'), $i); ?></h3>
                                <div class="cta-field">
                                    <label for="button_text_<?php echo $i; ?>"><?php _e('Texto do Botão:', 'alvobot-pre-artigo'); ?></label>
                                    <input 
                                        type="text" 
                                        id="button_text_<?php echo $i; ?>"
                                        name="alvobot_pre_artigo_options[button_text_<?php echo $i; ?>]" 
                                        value="<?php echo esc_attr($button_text); ?>" 
                                        class="regular-text"
                                    />
                                </div>
                                <div class="cta-field">
                                    <label for="button_color_<?php echo $i; ?>"><?php _e('Cor do Botão:', 'alvobot-pre-artigo'); ?></label>
                                    <input 
                                        type="text" 
                                        id="button_color_<?php echo $i; ?>"
                                        name="alvobot_pre_artigo_options[button_color_<?php echo $i; ?>]" 
                                        value="<?php echo esc_attr($button_color); ?>" 
                                        class="wp-color-picker-field" 
                                        data-default-color="#1E73BE" 
                                    />
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>

                <div class="card">
                    <h2><?php _e('Configurações do AdSense', 'alvobot-pre-artigo'); ?></h2>
                    <div class="adsense-field">
                        <label for="adsense_code">
                            <strong><?php _e('Código do AdSense:', 'alvobot-pre-artigo'); ?></strong>
                        </label>
                        <textarea 
                            id="adsense_code"
                            name="alvobot_pre_artigo_options[adsense_code]" 
                            rows="5" 
                            class="large-text code"
                        ><?php echo esc_textarea($options['adsense_code'] ?? ''); ?></textarea>
                        <p class="description">
                            <?php _e('Cole aqui o código do seu anúncio do Google AdSense. O anúncio será exibido após o segundo bloco de texto.', 'alvobot-pre-artigo'); ?>
                        </p>
                    </div>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>

        <script>
        var alvobotTranslations = {
            cta: '<?php echo esc_js(__('CTA', 'alvobot-pre-artigo')); ?>',
            buttonText: '<?php echo esc_js(__('Texto do Botão:', 'alvobot-pre-artigo')); ?>',
            buttonColor: '<?php echo esc_js(__('Cor do Botão:', 'alvobot-pre-artigo')); ?>'
        };
        </script>
        <?php
    }    

    public function register_settings() {
        register_setting('alvobot_pre_artigo_settings', 'alvobot_pre_artigo_options', [$this, 'sanitize']);
    
        add_settings_section(
            'alvobot_pre_artigo_section',
            __('Configurações dos Botões de CTA', 'alvobot-pre-artigo'),
            null,
            'alvobot-pre-artigo'
        );
    
        add_settings_field(
            'num_ctas',
            __('Quantidade de CTAs', 'alvobot-pre-artigo'),
            [$this, 'num_ctas_callback'],
            'alvobot-pre-artigo',
            'alvobot_pre_artigo_section'
        );

        // Add new AdSense section
        add_settings_section(
            'alvobot_adsense_section',
            __('Configurações do AdSense', 'alvobot-pre-artigo'),
            null,
            'alvobot-pre-artigo'
        );

        add_settings_field(
            'adsense_code',
            __('Código do AdSense', 'alvobot-pre-artigo'),
            [$this, 'adsense_code_callback'],
            'alvobot-pre-artigo',
            'alvobot_adsense_section'
        );
    }    

    public function num_ctas_callback() {
        $options = get_option('alvobot_pre_artigo_options');
        $num_ctas = $options['num_ctas'] ?? 2;
        ?>
        <input type="number" name="alvobot_pre_artigo_options[num_ctas]" value="<?php echo esc_attr($num_ctas); ?>" min="1" max="10" />
        <p class="description"><?php _e('Defina a quantidade padrão de CTAs a serem exibidas nos Pré-Artigos.', 'alvobot-pre-artigo'); ?></p>
        <?php
    }    

    public function adsense_code_callback() {
        $options = get_option('alvobot_pre_artigo_options');
        $adsense_code = $options['adsense_code'] ?? '';
        ?>
        <textarea name="alvobot_pre_artigo_options[adsense_code]" rows="5" cols="50" class="large-text"><?php echo esc_textarea($adsense_code); ?></textarea>
        <p class="description"><?php _e('Cole aqui seu código do Google AdSense.', 'alvobot-pre-artigo'); ?></p>
        <?php
    }    

    public function sanitize($input) {
        $new_input = [];
        
        // Existing CTAs sanitization
        if (isset($input['num_ctas'])) {
            $new_input['num_ctas'] = absint($input['num_ctas']);
            $num_ctas = $new_input['num_ctas'];
            for ($i = 1; $i <= $num_ctas; $i++) {
                if (isset($input["button_text_{$i}"])) {
                    $new_input["button_text_{$i}"] = sanitize_text_field($input["button_text_{$i}"]);
                }
                if (isset($input["button_color_{$i}"])) {
                    $new_input["button_color_{$i}"] = sanitize_hex_color($input["button_color_{$i}"]);
                }
            }
        }

        // AdSense code sanitization
        if (isset($input['adsense_code'])) {
            $new_input['adsense_code'] = wp_kses_post($input['adsense_code']);
        }

        return $new_input;
    }     

    // Meta Box na edição do post
    public function add_meta_boxes() {
        add_meta_box(
            'alvobot_pre_artigo_meta_box',
            __('Configuração do Pré-Artigo', 'alvobot-pre-artigo'),
            [$this, 'render_meta_box'],
            'post',
            'side',
            'high'
        );
    }

    public function render_meta_box($post) {
        // Adiciona nonce para segurança
        wp_nonce_field('alvobot_pre_artigo_nonce_action', 'alvobot_pre_artigo_nonce');

        $use_custom = get_post_meta($post->ID, '_alvobot_use_custom', true);
        $num_ctas = get_post_meta($post->ID, '_alvobot_num_ctas', true);
        $ctas = get_post_meta($post->ID, '_alvobot_ctas', true);

        ?>
        <p>
            <label>
                <input type="checkbox" name="alvobot_use_custom" value="1" <?php checked($use_custom, '1'); ?> />
                <?php _e('Personalizado', 'alvobot-pre-artigo'); ?>
            </label>
        </p>
        <div id="alvobot_custom_options" style="<?php echo ($use_custom == '1') ? '' : 'display:none;'; ?>">
            <p>
                <label for="alvobot_num_ctas"><?php _e('Quantas CTAs:', 'alvobot-pre-artigo'); ?></label>
                <input type="number" name="alvobot_num_ctas" id="alvobot_num_ctas" value="<?php echo esc_attr($num_ctas ? $num_ctas : 1); ?>" min="1" max="10" />
            </p>
            <div id="alvobot_ctas_container">
                <?php
                $num_ctas = $num_ctas ? intval($num_ctas) : 1;
                for ($i = 0; $i < $num_ctas; $i++) {
                    $cta_text = isset($ctas[$i]['text']) ? $ctas[$i]['text'] : '';
                    $cta_color = isset($ctas[$i]['color']) ? $ctas[$i]['color'] : '#1E73BE';
                    ?>
                    <p>
                        <label><?php printf(__('Texto da CTA %d:', 'alvobot-pre-artigo'), $i + 1); ?></label>
                        <input type="text" name="alvobot_ctas[<?php echo $i; ?>][text]" value="<?php echo esc_attr($cta_text); ?>" />
                    </p>
                    <p>
                        <label><?php printf(__('Cor da CTA %d:', 'alvobot-pre-artigo'), $i + 1); ?></label>
                        <input type="text" class="wp-color-picker-field" name="alvobot_ctas[<?php echo $i; ?>][color]" value="<?php echo esc_attr($cta_color); ?>" data-default-color="#1E73BE" />
                    </p>
                    <hr />
                    <?php
                }
                ?>
            </div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            // Function to generate random hex color
            function getRandomColor() {
                const letters = '0123456789ABCDEF';
                let color = '#';
                for (let i = 0; i < 6; i++) {
                    color += letters[Math.floor(Math.random() * 16)];
                }
                return color;
            }

            // Mostrar/esconder opções personalizadas
            $('input[name="alvobot_use_custom"]').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#alvobot_custom_options').show();
                } else {
                    $('#alvobot_custom_options').hide();
                }
            });

            // Atualizar CTAs quando o número mudar
            $('#alvobot_num_ctas').on('change', function() {
                var numCTAs = parseInt($(this).val());
                var container = $('#alvobot_ctas_container');
                var currentCTAs = container.children('p').length / 2; // Divide por 2 pois cada CTA tem 2 parágrafos

                if (numCTAs > currentCTAs) {
                    // Adiciona apenas as novas CTAs
                    for (var i = currentCTAs; i < numCTAs; i++) {
                        var randomColor = getRandomColor();
                        var ctaText = '<?php _e('Texto da CTA', 'alvobot-pre-artigo'); ?> ' + (i + 1) + ':';
                        var ctaColor = '<?php _e('Cor da CTA', 'alvobot-pre-artigo'); ?> ' + (i + 1) + ':';

                        var ctaHtml = '<p><label>' + ctaText + '</label>' +
                                     '<input type="text" name="alvobot_ctas[' + i + '][text]" value="" /></p>';
                        ctaHtml += '<p><label>' + ctaColor + '</label>' +
                                  '<input type="text" class="wp-color-picker-field" ' +
                                  'name="alvobot_ctas[' + i + '][color]" ' +
                                  'value="' + randomColor + '" ' +
                                  'data-default-color="' + randomColor + '" /></p><hr />';

                        container.append(ctaHtml);
                    }
                    // Inicializa color picker apenas para os novos elementos
                    container.children('p').slice(currentCTAs * 2).find('.wp-color-picker-field').wpColorPicker();
                } else if (numCTAs < currentCTAs) {
                    // Remove as CTAs excedentes
                    container.children('p').slice(numCTAs * 2).remove();
                    container.children('hr').slice(numCTAs).remove();
                }
            });

            // Inicializa os color pickers existentes
            $('.wp-color-picker-field').wpColorPicker();
        });
        </script>
        <?php
    }

    public function save_meta_box_data($post_id) {
        // Verifica o nonce
        if (!isset($_POST['alvobot_pre_artigo_nonce']) || !wp_verify_nonce($_POST['alvobot_pre_artigo_nonce'], 'alvobot_pre_artigo_nonce_action')) {
            return;
        }

        // Verifica se é uma revisão ou auto-save
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Verifica permissões
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Salva os dados
        $use_custom = isset($_POST['alvobot_use_custom']) ? '1' : '0';
        update_post_meta($post_id, '_alvobot_use_custom', $use_custom);

        if ($use_custom === '1') {
            $num_ctas = isset($_POST['alvobot_num_ctas']) ? intval($_POST['alvobot_num_ctas']) : 1;
            update_post_meta($post_id, '_alvobot_num_ctas', $num_ctas);

            $ctas = [];
            if (isset($_POST['alvobot_ctas']) && is_array($_POST['alvobot_ctas'])) {
                foreach ($_POST['alvobot_ctas'] as $cta) {
                    $ctas[] = [
                        'text' => sanitize_text_field($cta['text']),
                        'color' => sanitize_hex_color($cta['color']),
                    ];
                }
            }
            update_post_meta($post_id, '_alvobot_ctas', $ctas);
        } else {
            // Remove os meta dados se não estiver usando personalizado
            delete_post_meta($post_id, '_alvobot_num_ctas');
            delete_post_meta($post_id, '_alvobot_ctas');
        }
    }

    public function admin_enqueue_scripts($hook_suffix) {
        // Carrega scripts na edição de post e na página de opções do plugin
        if ('post.php' == $hook_suffix || 'post-new.php' == $hook_suffix || 'toplevel_page_alvobot-pre-artigo' == $hook_suffix) {
            // Enfileira o color picker
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
            
            // Enfileira os estilos e scripts personalizados
            wp_enqueue_style(
                'alvobot-pre-artigo-admin-style',
                plugin_dir_url(dirname(__FILE__)) . 'assets/css/admin-style.css',
                [],
                '1.0.0'
            );
            
            wp_enqueue_script(
                'alvobot-pre-artigo-admin-settings',
                plugin_dir_url(dirname(__FILE__)) . 'assets/js/admin-settings.js',
                ['jquery', 'wp-color-picker'],
                '1.0.0',
                true
            );
        }
    }

    /**
     * Registra as rotas da REST API
     */
    public function register_rest_routes() {
        // Rota para listar todas as URLs dos pré-artigos
        register_rest_route('alvobot-pre-article/v1', '/pre-articles', [
            'methods' => 'GET',
            'callback' => [$this, 'list_pre_article_urls'],
            'permission_callback' => [$this, 'check_rest_permission'],
            'schema' => [$this, 'get_pre_articles_schema']
        ]);

        // Rota para obter CTAs de um post específico
        register_rest_route('alvobot-pre-article/v1', '/posts/(?P<post_id>\d+)/ctas', [
            'methods' => 'GET',
            'callback' => [$this, 'get_post_ctas'],
            'permission_callback' => [$this, 'check_rest_permission'],
            'args' => [
                'post_id' => [
                    'required' => true,
                    'type' => 'integer',
                    'description' => 'ID do post',
                    'validate_callback' => function($param) {
                        return is_numeric($param) && $param > 0;
                    }
                ]
            ],
            'schema' => [$this, 'get_ctas_schema']
        ]);

        // Rota para atualizar CTAs de um post específico
        register_rest_route('alvobot-pre-article/v1', '/posts/(?P<post_id>\d+)/ctas', [
            'methods' => 'PUT',
            'callback' => [$this, 'update_post_ctas'],
            'permission_callback' => [$this, 'check_rest_permission'],
            'args' => [
                'post_id' => [
                    'required' => true,
                    'type' => 'integer',
                    'description' => 'ID do post',
                    'validate_callback' => function($param) {
                        return is_numeric($param) && $param > 0;
                    }
                ]
            ],
            'schema' => [$this, 'get_ctas_schema']
        ]);
    }

    /**
     * Schema para a rota de pré-artigos
     */
    public function get_pre_articles_schema() {
        return [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'pre-article',
            'type' => 'object',
            'properties' => [
                'id' => [
                    'description' => 'ID do post',
                    'type' => 'integer',
                    'context' => ['view']
                ],
                'title' => [
                    'description' => 'Título do post',
                    'type' => 'string',
                    'context' => ['view']
                ],
                'pre_article_url' => [
                    'description' => 'URL da página de pré-artigo',
                    'type' => 'string',
                    'format' => 'uri',
                    'context' => ['view']
                ],
                'post_url' => [
                    'description' => 'URL do post original',
                    'type' => 'string',
                    'format' => 'uri',
                    'context' => ['view']
                ]
            ]
        ];
    }

    /**
     * Schema para a rota de CTAs
     */
    public function get_ctas_schema() {
        return [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'ctas',
            'type' => 'object',
            'properties' => [
                'use_custom' => [
                    'description' => 'Se está usando configurações personalizadas',
                    'type' => 'boolean',
                    'context' => ['view', 'edit']
                ],
                'ctas' => [
                    'description' => 'Lista de CTAs',
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'text' => [
                                'description' => 'Texto do botão CTA',
                                'type' => 'string',
                                'context' => ['view', 'edit']
                            ],
                            'color' => [
                                'description' => 'Cor do botão CTA',
                                'type' => 'string',
                                'pattern' => '^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$',
                                'context' => ['view', 'edit']
                            ]
                        ]
                    ],
                    'context' => ['view', 'edit']
                ]
            ]
        ];
    }

    /**
     * Lista todas as URLs das páginas de pré-artigo
     */
    public function list_pre_article_urls() {
        // Busca todos os posts publicados
        $posts = get_posts([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);

        $urls = [];
        foreach ($posts as $post_id) {
            $post = get_post($post_id);
            if ($post) {
                $urls[] = [
                    'id' => $post_id,
                    'title' => get_the_title($post_id),
                    'pre_article_url' => home_url('pre/' . $post->post_name),
                    'post_url' => get_permalink($post_id)
                ];
            }
        }

        return rest_ensure_response($urls);
    }

    /**
     * Verifica permissões para acessar a API
     */
    public function check_rest_permission() {
        return current_user_can('edit_posts');
    }

    /**
     * Obtém as CTAs de um post específico
     */
    public function get_post_ctas($request) {
        $post_id = $request['post_id'];
        
        // Verifica se o post existe
        if (!get_post($post_id)) {
            return new WP_Error('post_not_found', 'Post não encontrado', ['status' => 404]);
        }

        $use_custom = get_post_meta($post_id, '_alvobot_use_custom', true);
        
        if ($use_custom === '1') {
            $ctas = get_post_meta($post_id, '_alvobot_ctas', true);
        } else {
            $options = get_option('alvobot_pre_artigo_options');
            $num_ctas = $options['num_ctas'] ?? 2;
            $ctas = [];
            for ($i = 1; $i <= $num_ctas; $i++) {
                $ctas[] = [
                    'text' => $options["button_text_{$i}"] ?? '',
                    'color' => $options["button_color_{$i}"] ?? '#1E73BE',
                ];
            }
        }

        return rest_ensure_response([
            'use_custom' => $use_custom === '1',
            'ctas' => $ctas
        ]);
    }

    /**
     * Atualiza as CTAs de um post específico
     */
    public function update_post_ctas($request) {
        $post_id = $request['post_id'];
        $params = $request->get_json_params();
        
        // Verifica se o post existe
        if (!get_post($post_id)) {
            return new WP_Error('post_not_found', 'Post não encontrado', ['status' => 404]);
        }

        // Valida os dados recebidos
        if (!isset($params['use_custom']) || !isset($params['ctas'])) {
            return new WP_Error('invalid_data', 'Dados inválidos', ['status' => 400]);
        }

        // Atualiza os meta dados
        update_post_meta($post_id, '_alvobot_use_custom', $params['use_custom'] ? '1' : '0');
        if ($params['use_custom']) {
            update_post_meta($post_id, '_alvobot_ctas', $params['ctas']);
        }

        return rest_ensure_response([
            'success' => true,
            'message' => 'CTAs atualizadas com sucesso'
        ]);
    }

    public function flush_rewrite_rules() {
        flush_rewrite_rules();
    }
}
