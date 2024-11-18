# 🚀 Alvobot Pre Article

[![Version](https://img.shields.io/badge/version-1.3.0-blue.svg)](https://github.com/alvobot/alvobot-pre-article/releases)
[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-green.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://www.php.net)
[![License](https://img.shields.io/badge/license-GPL%20v2-red.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Build Status](https://img.shields.io/github/actions/workflow/status/alvobot/alvobot-pre-article/ci.yml?branch=main&label=build)](https://github.com/alvobot/alvobot-pre-article/actions)
[![Issues](https://img.shields.io/github/issues/alvobot/alvobot-pre-article.svg)](https://github.com/alvobot/alvobot-pre-article/issues)
[![Stars](https://img.shields.io/github/stars/alvobot/alvobot-pre-article.svg?style=social&label=Star)](https://github.com/alvobot/alvobot-pre-article)

O **Alvobot Pre Article** é um plugin WordPress que gera automaticamente páginas de pré-artigo para seus posts existentes, otimizadas para conversão. Com **CTAs personalizáveis**, **integração com Google AdSense**, suporte a **API REST** e uma **interface administrativa intuitiva**, este plugin é a ferramenta definitiva para aumentar o engajamento e as conversões do seu site.

---

## 📋 Índice

- [📢 Visão Geral](#-visão-geral)
- [✨ Recursos](#-recursos)
- [🔧 Instalação](#-instalação)
- [⚙️ Configuração](#️-configuração)
- [📡 API REST](#-api-rest)
- [💡 Exemplos de Uso](#-exemplos-de-uso)
- [🛠️ Desenvolvimento](#-desenvolvimento)
- [❓ FAQ](#-faq)
- [🤝 Contribuindo](#-contribuindo)
- [📝 Changelog](#-changelog)
- [📄 Licença](#-licença)
- [💪 Suporte](#-suporte)

---

## 📢 Visão Geral

O **Alvobot Pre Article** automatiza a criação de páginas de pré-artigo para cada post existente no seu site WordPress. Estas páginas são projetadas para atrair leitores e direcioná-los para o conteúdo completo através de CTAs estrategicamente posicionados.

### Principais Funcionalidades

- ✅ **Geração Automática**: Cria páginas de pré-artigo automaticamente para todos os posts existentes.
- ✅ **CTAs Personalizáveis**: Configure CTAs individuais por post com textos e cores personalizáveis.
- ✅ **Integração com Google AdSense**: Exiba anúncios de forma otimizada nas páginas de pré-artigo.
- ✅ **API REST Completa**: Integre com outros sistemas facilmente através da API REST fornecida.
- ✅ **Interface Administrativa Intuitiva**: Gerencie todas as configurações de forma fácil e rápida.
- ✅ **Sistema de Templates Responsivo**: Garanta que suas páginas de pré-artigo fiquem ótimas em qualquer dispositivo.
- ✅ **Atualizações Automáticas via GitHub**: Mantenha seu plugin sempre atualizado com as últimas versões.

---

## ✨ Recursos

### Geração Automática de Pré-Artigos

- Cria páginas de pré-artigo para cada post existente sem necessidade de intervenção manual.
- Suporte para atualização automática quando novos posts são adicionados.

### CTAs Personalizáveis

- Personalize o texto e a cor dos CTAs para cada post individualmente.
- Escolha entre múltiplas posições para os CTAs nas páginas de pré-artigo.

### Integração com Google AdSense

- Insira facilmente seu código do AdSense nas configurações do plugin.
- Anúncios são exibidos automaticamente nas posições configuradas.

### API REST

- **Listar Pré-Artigos**: `GET /wp-json/alvobot-pre-article/v1/pre-articles`
- **Obter CTAs de um Post**: `GET /wp-json/alvobot-pre-article/v1/posts/{post_id}/ctas`
- **Atualizar CTAs de um Post**: `PUT /wp-json/alvobot-pre-article/v1/posts/{post_id}/ctas`

### Interface Administrativa

- Painel de controle fácil de usar para gerenciar todas as configurações do plugin.
- Seções dedicadas para configuração global e por post.

---

## 🔧 Instalação

### Requisitos

- **WordPress**: 5.8 ou superior
- **PHP**: 7.4 ou superior

### Passo a Passo

1. **Download**
   - Baixe a última versão do plugin a partir dos [Releases no GitHub](https://github.com/alvobot/alvobot-pre-article/releases).

2. **Upload via Painel do WordPress**
   - Acesse o painel administrativo do WordPress.
   - Navegue até **Plugins > Adicionar Novo**.
   - Clique em **Enviar Plugin** e selecione o arquivo `.zip` baixado.
   - Clique em **Instalar Agora** e, em seguida, **Ativar** o plugin.

3. **Configuração Inicial**
   - Após a ativação, vá até **Configurações > Alvobot Pre Article** para configurar as opções do plugin.

### Alternativas de Instalação

#### Via Composer

Se você prefere gerenciar dependências via Composer:

```bash
composer require alvobot/alvobot-pre-article

Instalação Manual

	1.	Baixe a última versão do plugin aqui.
	2.	Descompacte o arquivo na pasta /wp-content/plugins/.
	3.	Ative o plugin no painel do WordPress.

⚙️ Configuração

Configurações Globais

	1.	Acesse Configurações > Alvobot Pre Article no painel do WordPress.
	2.	Configure as opções padrão:
	•	Textos dos CTAs: Defina os textos padrão para os CTAs.
	•	Cores dos Botões: Escolha as cores padrão para os botões de CTA.
	•	Posicionamento dos Anúncios: Selecione onde os anúncios do AdSense serão exibidos.
	•	Configurações do AdSense: Insira seu código de AdSense para ativar a integração.

Configurações por Post

	1.	Edite o post desejado.
	2.	Localize a seção Alvobot Pre Article na página de edição.
	3.	Marque a opção Usar CTAs Personalizados.
	4.	Configure:
	•	Texto do CTA: Personalize o texto do botão de CTA.
	•	Cor do Botão: Escolha a cor específica para o CTA deste post.
	•	Posição dos Anúncios: Defina onde os anúncios serão exibidos nesta página de pré-artigo.

📡 API REST

O Alvobot Pre Article oferece uma API REST completa para integração com outros sistemas.

Autenticação

A API requer autenticação via WordPress REST API Authentication. Os usuários precisam ter a capacidade edit_posts.

Endpoints Disponíveis

Listar URLs de Pré-Artigos

GET /wp-json/alvobot-pre-article/v1/pre-articles

Descrição: Retorna uma lista de todas as URLs de pré-artigos.

Resposta:

[
  {
    "id": 123,
    "title": "Título do Post",
    "pre_article_url": "https://seu-site.com/pre/titulo-do-post",
    "post_url": "https://seu-site.com/titulo-do-post"
  }
]

Obter CTAs de um Post

GET /wp-json/alvobot-pre-article/v1/posts/{post_id}/ctas

Descrição: Retorna as CTAs configuradas para um post específico.

Resposta:

{
  "use_custom": true,
  "ctas": [
    {
      "text": "Leia Mais",
      "color": "#FF0000"
    }
  ]
}

Atualizar CTAs de um Post

PUT /wp-json/alvobot-pre-article/v1/posts/{post_id}/ctas

Descrição: Atualiza as CTAs de um post específico.

Corpo da Requisição:

{
  "use_custom": true,
  "ctas": [
    {
      "text": "Novo Texto",
      "color": "#00FF00"
    }
  ]
}

Resposta:

{
  "success": true,
  "message": "CTAs atualizadas com sucesso"
}

Documentação Completa

Para mais detalhes sobre a API, consulte o arquivo CHANGELOG.md.

💡 Exemplos de Uso

PHP

// Obtendo CTAs de um post
$response = wp_remote_get(
    rest_url('alvobot-pre-article/v1/posts/123/ctas'),
    array(
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode('username:password')
        )
    )
);

if (is_wp_error($response)) {
    // Trate o erro
} else {
    $body = wp_remote_retrieve_body($response);
    $ctas = json_decode($body, true);
    // Use os CTAs conforme necessário
}

JavaScript

// Atualizando CTAs de um post
fetch('/wp-json/alvobot-pre-article/v1/posts/123/ctas', {
    method: 'PUT',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': wpApiSettings.nonce
    },
    body: JSON.stringify({
        use_custom: true,
        ctas: [{
            text: "Leia mais",
            color: "#FF0000"
        }]
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('CTAs atualizadas com sucesso');
    } else {
        console.error('Falha ao atualizar CTAs');
    }
})
.catch(error => console.error('Erro:', error));

🛠️ Desenvolvimento

Requisitos

	•	PHP: 7.4+
	•	WordPress: 5.8+
	•	Composer: (opcional, para gerenciamento de dependências)
	•	Node.js: (para desenvolvimento frontend)

Setup Local

	1.	Clone o Repositório

git clone https://github.com/alvobot/alvobot-pre-article.git
cd alvobot-pre-article


	2.	Instale as Dependências

composer install
npm install


	3.	Build dos Assets

npm run build


	4.	Ative o Plugin no WordPress
	•	Coloque a pasta alvobot-pre-article na diretoria /wp-content/plugins/ do seu ambiente WordPress.
	•	Ative o plugin através do painel administrativo do WordPress.

Estrutura do Projeto

alvobot-pre-article/
├── assets/
│   ├── css/
│   │   └── admin-style.css
│   ├── js/
│   │   └── admin-settings.js
├── includes/
│   ├── class-alvobot-pre-article.php
│   └── class-alvobot-pre-article-updater.php
├── templates/
│   └── pre-article.php
├── tests/
│   └── ... (testes automatizados)
├── composer.json
├── package.json
├── README.md
├── CHANGELOG.md
└── alvobot-pre-article.php

Padrões de Código

	•	Siga os Padrões de Código do WordPress.
	•	Utilize PSR-4 para autoloading de classes.
	•	Adote PHPDoc para documentação de funções e classes.

Testes

	•	Escreva testes automatizados para novas funcionalidades.
	•	Utilize ferramentas como PHPUnit para testes unitários.
	•	Assegure-se de que todas as funcionalidades principais estão cobertas por testes.

Contribuindo

	1.	Fork o Repositório
	2.	Crie uma Branch para sua Feature

git checkout -b minha-nova-feature


	3.	Faça Commit das suas Alterações

git commit -m "Descrição clara da feature"


	4.	Push para o Fork

git push origin minha-nova-feature


	5.	Abra um Pull Request

❓ FAQ

Como personalizar o template do pré-artigo?

Crie um arquivo template-pre-article.php no diretório do seu tema para sobrescrever o template padrão fornecido pelo plugin.

Como funciona a integração com AdSense?

Insira seu código do AdSense nas configurações do plugin. Os anúncios serão exibidos automaticamente nas posições configuradas nas páginas de pré-artigo.

O plugin é compatível com plugins de cache?

Sim, o Alvobot Pre Article é compatível com os principais plugins de cache do WordPress. Recomenda-se limpar o cache após alterações nas configurações do plugin.

Posso usar múltiplos CTAs por pré-artigo?

Sim, o plugin suporta múltiplos CTAs por página de pré-artigo. Você pode configurar cada CTA individualmente nas configurações por post.

A API REST do plugin é segura?

Sim, a API REST do plugin requer autenticação adequada e apenas usuários com a capacidade edit_posts podem acessar e modificar os dados através da API.

🤝 Contribuindo

Contribuições são sempre bem-vindas! Siga estas etapas para contribuir:
	1.	Fork o Repositório
	2.	Crie uma Branch para sua Feature ou Correção
	3.	Faça Commit das suas Alterações
	4.	Push para o Fork
	5.	Abra um Pull Request

Diretrizes de Contribuição

	•	Siga os Padrões de Código do WordPress.
	•	Adicione testes para novas funcionalidades.
	•	Atualize a documentação conforme necessário.
	•	Use Conventional Commits nas mensagens de commit.
	•	Respeite as diretrizes de Código de Conduta.

📝 Changelog

Confira o CHANGELOG.md para o histórico completo de alterações.

Últimas Versões

[1.3.0] - 2024-01-17

Adicionado

	•	Novo endpoint REST API /wp-json/alvobot-pre-article/v1/pre-articles para listar todas as URLs de pré-artigos.
	•	Schemas JSON para documentação e validação da API.
	•	Arquivos separados para CSS e JavaScript:
	•	assets/css/admin-style.css
	•	assets/js/admin-settings.js

Alterado

	•	Padronização dos endpoints da REST API:
	•	/ctas/{post_id} → /posts/{post_id}/ctas
	•	Método POST alterado para PUT na atualização de CTAs.
	•	Melhorias na validação de parâmetros da API.
	•	Organização do código administrativo:
	•	CSS e JavaScript movidos para arquivos dedicados.
	•	Melhor estrutura de código.

Removido

	•	Método não utilizado get_plugin_info().
	•	CSS e JavaScript inline do painel administrativo.

[1.2.0] - 2024-11-17

Adicionado

	•	Atualização automática via GitHub.
	•	Sistema de changelog.
	•	Melhorias na interface de administração.
	•	Suporte a múltiplos CTAs por artigo.
	•	Integração com Google AdSense.

Alterado

	•	Atualização da estrutura do plugin.
	•	Melhorias de performance.
	•	Otimização do código.

[1.1.0] - 2024-06-01

Adicionado

	•	Personalização de CTAs por post.
	•	Melhorias na interface do usuário.

Alterado

	•	Lançamento inicial.

📄 Licença

Este projeto está licenciado sob a GPL v2 ou posterior. Veja o arquivo LICENSE para detalhes.

💪 Suporte

	•	📚 Documentação
	•	🐛 Issues
	•	💬 Fórum de Suporte no WordPress
	•	✉️ Contato

Desenvolvido com ❤️ por Alvobot