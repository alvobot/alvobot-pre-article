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
    <title><?php the_title(); ?> - <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
    <meta name="robots" content="noindex">
</head>
<body <?php body_class(); ?>>
    <div class="pre-article-wrapper">
        <header class="pre-article-header">
            <?php
            if (has_custom_logo()) {
                $logo = wp_get_attachment_image_src(get_theme_mod('custom_logo'), 'full');
                if ($logo) {
                    echo '<img src="' . esc_url($logo[0]) . '" alt="' . esc_attr(get_bloginfo('name')) . '">';
                }
            } else {
                echo '<h1>' . esc_html(get_bloginfo('name')) . '</h1>';
            }
            ?>
        </header>

        <div class="pre-article-content">
            <h2><?php the_title(); ?></h2>
            
            <!-- Primeira parte do conteúdo -->
            <div class="excerpt">
                <?php 
                // Remove qualquer HTML no final que possa causar quebra de linha
                $first_part = preg_replace('/<\/p>\s*$/', '', $first_part);
                // Remove espaços e pontuação final
                $first_part = rtrim($first_part, " \n\r\t\v\x00.");
                // Adiciona o continue na mesma linha
                echo $first_part . ' <a href="' . esc_url(get_permalink()) . '" class="continue-reading">...continue</a>';
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
                    // Adiciona o continue na mesma linha
                    echo $second_part . ' <a href="' . esc_url(get_permalink()) . '" class="continue-reading">...continue</a>';
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
                    <div class="primary-disclaimer">
                        <p><b><?php _e('Aviso Importante', 'alvobot-pre-artigo'); ?></b>: <?php _e('As informações disponíveis neste site são apenas para fins informativos e não substituem, em nenhuma hipótese, o parecer de qualquer profissional qualificado. Sempre consulte um profissional especializado da área relacionada antes de tomar qualquer decisão baseada em nosso conteúdo.', 'alvobot-pre-artigo'); ?>
                        </p>
                    </div>
                    <div class="legal-notices">
                        <div class="notice-block">
                            <p><b><?php _e('Resultados e Responsabilidade', 'alvobot-pre-artigo'); ?></b>: <?php _e('Os resultados apresentados em nosso conteúdo são ilustrativos e não devem ser considerados como garantia de ganhos ou sucessos específicos. O sucesso depende de diversos fatores individuais, incluindo dedicação, contexto e implementação adequada.', 'alvobot-pre-artigo'); ?>
                            </p>
                        </div>
                        <div class="notice-block">
                        <p><b><?php _e('Identificação do Site', 'alvobot-pre-artigo'); ?></b>: <?php _e('Este é um site independente e não é afiliado, associado, autorizado, endossado ou de qualquer forma oficialmente conectado a outras marcas ou empresas mencionadas. Todas as marcas registradas pertencem a seus respectivos proprietários.', 'alvobot-pre-artigo'); ?>
                            </p>
                        </div>
                        <div class="notice-block">
                        <p><b><?php _e('Privacidade e Publicidade', 'alvobot-pre-artigo'); ?></b>: <?php _e('Este site pode conter links patrocinados e publicidade. Suas informações pessoais são protegidas e nunca serão compartilhadas com terceiros sem seu consentimento. Ao se cadastrar, você poderá receber comunicações relacionadas aos nossos serviços.', 'alvobot-pre-artigo'); ?>
                                <a href="<?php echo esc_url(get_privacy_policy_url()); ?>" class="footer-link"><?php _e('Política de Privacidade', 'alvobot-pre-artigo'); ?></a>.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="footer-section copyright-section">
                    <p class="copyright">
                        &copy; <?php echo date('Y'); ?> <?php echo esc_html(get_bloginfo('name')); ?>. 
                        <?php _e('Todos os direitos reservados.', 'alvobot-pre-artigo'); ?>
                    </p>
                </div>
            </div>
        </footer>
    </div>
    <?php wp_footer(); ?>
</body>
</html>
<?php
endwhile; endif;
?>