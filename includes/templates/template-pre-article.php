<?php

declare(strict_types=1);

if (have_posts()) : while (have_posts()) : the_post();

// Obtém as CTAs personalizadas ou padrão
$ctas = get_query_var('alvobot_ctas');

if (empty($ctas)) {
    $ctas = [];
}

// Adiciona o link do post a cada CTA
foreach ($ctas as &$cta) {
    $cta['link'] = get_permalink();
}
unset($cta);

// Prepara o conteúdo
$content = get_the_content();
$content = apply_filters('the_content', $content);
$allowed_tags = '<p><br><strong><em><ul><ol><li><a>';
$content = strip_tags($content, $allowed_tags);

// Função para truncar o conteúdo sem quebrar palavras ou tags HTML
function truncate_html_words($text, $word_limit) {
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML(mb_convert_encoding($text, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();

    $body = $doc->getElementsByTagName('body')->item(0);
    $word_count = 0;
    $truncated = false;

    $stack = [$body];
    while ($stack) {
        $node = array_pop($stack);
        if ($node instanceof DOMText) {
            $words = preg_split('/\s+/u', $node->nodeValue, -1, PREG_SPLIT_NO_EMPTY);
            $current_word_count = count($words);

            if ($word_count + $current_word_count > $word_limit) {
                $remaining_words = $word_limit - $word_count;
                $node->nodeValue = implode(' ', array_slice($words, 0, $remaining_words));
                $truncated = true;
                // Remove nós irmãos seguintes
                $nextNode = $node->nextSibling;
                while ($nextNode) {
                    $toRemove = $nextNode;
                    $nextNode = $nextNode->nextSibling;
                    $toRemove->parentNode->removeChild($toRemove);
                }
                // Remove nós filhos
                if ($node->hasChildNodes()) {
                    $childNodes = $node->childNodes;
                    $remove = false;
                    foreach ($childNodes as $child) {
                        if ($remove) {
                            $node->removeChild($child);
                        }
                        if ($child->isSameNode($node)) {
                            $remove = true;
                        }
                    }
                }
                break;
            } else {
                $word_count += $current_word_count;
            }
        }

        if ($node->hasChildNodes()) {
            $children = [];
            foreach ($node->childNodes as $child) {
                array_unshift($children, $child);
            }
            foreach ($children as $child) {
                $stack[] = $child;
            }
        }
    }

    $result = '';
    foreach ($body->childNodes as $child) {
        $result .= $doc->saveHTML($child);
    }

    return [$result, $truncated];
}

// Primeira parte - 400 caracteres
$first_part = '';
$first_truncated = false;
$words = explode(' ', strip_tags($content));
$word_count = 0;
$first_part_words = [];

foreach ($words as $word) {
    $word_count += strlen($word);
    $first_part_words[] = $word;
    
    if ($word_count >= 200) {
        $first_truncated = true;
        break;
    }
}

$first_part = implode(' ', $first_part_words);

// Pega o restante do conteúdo
$remaining_words = array_slice($words, count($first_part_words));
$second_part = '';
$second_truncated = false;
$char_count = 0;
$second_part_words = [];

// Segunda parte - 150 caracteres
foreach ($remaining_words as $word) {
    $char_count += strlen($word);
    $second_part_words[] = $word;
    
    if ($char_count >= 400) {
        $second_truncated = true;
        break;
    }
}

$second_part = implode(' ', $second_part_words);

// Reaplica as tags HTML ao conteúdo
$first_part = wpautop($first_part);
$second_part = wpautop($second_part);

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title('|', true, 'right'); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class('pre-article-page'); ?>>
    <?php wp_body_open(); ?>
    
    <div class="pre-article-container">
        <main class="pre-article-content">
            <div class="pre-article-text">
                <h2><?php the_title(); ?></h2>
                
                <!-- Primeira parte do conteúdo -->
                <div class="excerpt">
                    <?php 
                    // Remove qualquer HTML no final que possa causar quebra de linha
                    $first_part = preg_replace('/<\/p>\s*$/', '', $first_part);
                    // Remove espaços e pontuação final
                    $first_part = rtrim($first_part, " \n\r\t\v\x00.");
                    // Adiciona UTM ao link
                    $continue_1_url = add_query_arg([
                        'utm_content' => 'continue_1'
                    ], get_permalink());
                    // Adiciona o continue na mesma linha
                    echo $first_part . ' <a href="' . esc_url($continue_1_url) . '" class="continue-reading">...continue</a>';
                    ?>
                </div>

                <!-- CTAs -->
                <div class="cta-buttons">
                    <?php
                    if (!empty($ctas)) {
                        foreach ($ctas as $index => $cta) {
                            // Constrói a UTM
                            $utm_params = [
                                'utm_content' => sanitize_title($cta['text']) . '_cta_' . ($index + 1)
                            ];
                            
                            // Adiciona os parâmetros UTM à URL
                            $cta_url = add_query_arg($utm_params, esc_url($cta['link']));
                            ?>
                            <a href="<?php echo esc_url($cta_url); ?>" class="cta-button" style="background-color: <?php echo esc_attr($cta['color']); ?>;">
                                <?php echo esc_html($cta['text']); ?>
                            </a>
                            <?php
                        }
                    }
                    ?>
                </div>

                <!-- Segunda parte do conteúdo -->
                <div class="excerpt-continuation">
                    <?php 
                    if (!empty($second_part)) {
                        // Remove qualquer HTML no final que possa causar quebra de linha
                        $second_part = preg_replace('/<\/p>\s*$/', '', $second_part);
                        // Remove espaços e pontuação final
                        $second_part = rtrim($second_part, " \n\r\t\v\x00.");
                        // Adiciona UTM ao link
                        $continue_2_url = add_query_arg([
                            'utm_content' => 'continue_2'
                        ], get_permalink());
                        // Adiciona o continue na mesma linha
                        echo $second_part . ' <a href="' . esc_url($continue_2_url) . '" class="continue-reading">...continue</a>';
                    }
                    ?>
                </div>

                <!-- AdSense Block -->
                <div class="adsense-block">
                    <?php 
                    $options = get_option('alvobot_pre_artigo_options');
                    $adsense_code = $options['adsense_code'] ?? '';
                    echo wp_kses_post($adsense_code);
                    ?>
                </div>
            </div>

            <footer class="pre-article-footer">
                <div class="footer-content">
                    <div class="footer-section disclaimer-section">
                        <div class="legal-disclaimer">
                            <?php
                            $options = get_option('alvobot_pre_artigo_options');
                            $default_footer = 'Aviso Legal: As informações deste site são meramente informativas e não substituem orientação profissional. Os resultados apresentados são ilustrativos, sem garantia de sucesso específico. Somos um site independente, não afiliado a outras marcas, que preza pela privacidade do usuário e protege suas informações pessoais, utilizando apenas para comunicações relacionadas aos nossos serviços.';
                            
                            $footer_text = $options['footer_text'] ?? $default_footer;
                            
                            // Substitui o nome do site
                            $footer_text = str_replace('{NOME DO SITE}', get_bloginfo('name'), $footer_text);
                            
                            // Substitui "Política de Privacidade" por um link
                            $privacy_url = get_privacy_policy_url();
                            if ($privacy_url) {
                                $footer_text = str_replace(
                                    'Política de Privacidade',
                                    '<a href="' . esc_url($privacy_url) . '" class="footer-link">Política de Privacidade</a>',
                                    $footer_text
                                );
                            }
                            
                            echo wp_kses_post($footer_text);
                            ?>
                        </div>
                    </div>

                    <div class="footer-section copyright-section">
                        <div class="footer-links">
                            <?php
                            // Links padrão do rodapé
                            $privacy_policy_url = get_privacy_policy_url();
                            if ($privacy_policy_url) {
                                echo '<a href="' . esc_url($privacy_policy_url) . '" class="footer-link">' . __('Política de Privacidade', 'alvobot-pre-artigo') . '</a>';
                            }
                            ?>
                        </div>
                        <p class="copyright">
                            &copy; <?php echo date('Y'); ?> <?php echo esc_html(get_bloginfo('name')); ?>. 
                            <?php _e('Todos os direitos reservados.', 'alvobot-pre-artigo'); ?>
                        </p>
                    </div>
                </div>
            </footer>
        </main>
    </div>
    <?php wp_footer(); ?>
</body>
</html>
<?php
endwhile; endif;
?>