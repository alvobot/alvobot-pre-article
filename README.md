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