=== Alvobot Pre Article ===
Contributors: alvobot
Tags: pre-article, cta, seo
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Gere páginas de pré-artigo automaticamente para seus posts existentes.

== Description ==

O Alvobot Pre Article é um plugin WordPress que gera páginas de pré-artigo automaticamente para seus posts existentes. Cada pré-artigo inclui CTAs personalizáveis para direcionar os leitores ao conteúdo completo.

**Recursos Principais:**
* Geração automática de pré-artigos
* CTAs personalizáveis por post
* Integração com Google AdSense
* API REST para integração com outros sistemas
* Interface administrativa intuitiva

**API REST**
O plugin oferece uma API REST completa para integração com outros sistemas:

* Listar URLs de pré-artigos: `GET /wp-json/alvobot-pre-article/v1/pre-articles`
* Obter CTAs de um post: `GET /wp-json/alvobot-pre-article/v1/posts/{post_id}/ctas`
* Atualizar CTAs de um post: `PUT /wp-json/alvobot-pre-article/v1/posts/{post_id}/ctas`

Para documentação completa da API, consulte o arquivo CHANGELOG.md.

== Installation ==

1. Faça upload do plugin para a pasta `/wp-content/plugins/`
2. Ative o plugin através do menu 'Plugins' no WordPress
3. Configure as opções do plugin em 'Configurações > Alvobot Pre Article'

== Frequently Asked Questions ==

= Como personalizar os CTAs de um post específico? =

1. Vá até a edição do post
2. Localize a seção "Alvobot Pre Article"
3. Marque "Usar CTAs personalizados"
4. Configure o texto e a cor dos CTAs

= Como integrar com outros sistemas via API? =

O plugin oferece endpoints REST API para integração. Consulte a documentação completa no arquivo CHANGELOG.md.

== Changelog ==

= 1.3.0 =
* Novo endpoint REST API para listar URLs de pré-artigos
* Padronização dos endpoints da API
* Melhorias na organização do código
* Documentação completa da API

= 1.2.0 =
* Adicionado suporte ao Google AdSense
* Melhorias na interface administrativa

= 1.1.0 =
* Adicionada personalização de CTAs por post
* Melhorias na interface do usuário

= 1.0.0 =
* Lançamento inicial

== Upgrade Notice ==

= 1.3.0 =
Esta versão inclui uma API REST padronizada e documentada para integração com outros sistemas. Atualização recomendada para todos os usuários.

== Screenshots ==

1. Interface administrativa
2. Configuração de CTAs por post
3. Exemplo de pré-artigo
