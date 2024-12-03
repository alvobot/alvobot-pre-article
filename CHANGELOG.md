# Changelog

## [1.4.9] - 2024-01-03

### Melhorado
- Atualizado sistema de atualizações para usar diretório includes/lib
- Garantida compatibilidade com atualizações via GitHub
- Melhorada estrutura de arquivos do plugin

## [1.4.7] - 2024-01-03

### Corrigido
- Removido botão duplicado de verificação de atualizações
- Melhorada interface de atualização do plugin
- Corrigido problema com diretório vendor movendo Plugin Update Checker para includes/lib

## [1.4.6] - 2024-01-03

### Melhorado
- Atualizado sistema de atualizações para usar Plugin Update Checker 5.5
- Melhorada integração com releases do GitHub
- Adicionado botão de verificação manual de atualizações
- Corrigido problemas na detecção de novas versões

## [1.4.5] - 2024-12-03

### Corrigido
- Corrigido problema na detecção de novas versões do plugin
- Melhorado sistema de cache para atualizações
- Adicionada limpeza automática de cache na ativação do plugin

## [1.4.4] - 2024-12-03

### Corrigido
- Corrigido erro fatal ao carregar o plugin devido à função `is_plugin_active()`
- Corrigido tipo da propriedade `$active` no updater para aceitar valor booleano
- Melhorada a inicialização do sistema de atualizações

## [1.4.0] - 2024-01-09

### Adicionado
- Novo botão "Verificar Atualização" na lista de plugins para verificação manual de atualizações
- Sistema de verificação automática de atualizações via GitHub
- Integração com a API do GitHub para atualizações automáticas

### Melhorado
- Atualização da descrição do plugin
- Documentação do código

## [1.3.0] - 2024-01-17

### Adicionado
- Novo endpoint REST API `/wp-json/alvobot-pre-article/v1/pre-articles` para listar todas as URLs de pré-artigos
- Schemas JSON para documentação e validação da API
- Arquivos separados para CSS e JavaScript:
  - `assets/css/admin-style.css`
  - `assets/js/admin-settings.js`

### Alterado
- Padronização dos endpoints da REST API:
  - `/ctas/{post_id}` → `/posts/{post_id}/ctas`
  - Método POST alterado para PUT na atualização de CTAs
- Melhorias na validação de parâmetros da API
- Organização do código administrativo:
  - CSS e JavaScript movidos para arquivos dedicados
  - Melhor estrutura de código

### Removido
- Método não utilizado `get_plugin_info()`
- CSS e JavaScript inline do painel administrativo

### Documentação da API

#### Listar URLs de Pré-artigos
```http
GET /wp-json/alvobot-pre-article/v1/pre-articles
```
Retorna uma lista de todos os pré-artigos com suas URLs.

**Resposta**
```json
[
  {
    "id": 123,
    "title": "Título do Post",
    "pre_article_url": "https://seu-site.com/pre/titulo-do-post",
    "post_url": "https://seu-site.com/titulo-do-post"
  }
]
```

#### Obter CTAs de um Post
```http
GET /wp-json/alvobot-pre-article/v1/posts/{post_id}/ctas
```
Retorna as CTAs configuradas para um post específico.

**Resposta**
```json
{
  "use_custom": true,
  "ctas": [
    {
      "text": "Texto do CTA",
      "color": "#FF0000"
    }
  ]
}
```

#### Atualizar CTAs de um Post
```http
PUT /wp-json/alvobot-pre-article/v1/posts/{post_id}/ctas
```
Atualiza as CTAs de um post específico.

**Corpo da Requisição**
```json
{
  "use_custom": true,
  "ctas": [
    {
      "text": "Novo Texto",
      "color": "#00FF00"
    }
  ]
}
```

**Resposta**
```json
{
  "success": true,
  "message": "CTAs atualizadas com sucesso"
}
```

### Requisitos
- WordPress 5.8+
- PHP 7.4+

## [1.2.0] - 2024-11-17
### Added
- Atualização automática via GitHub
- Sistema de changelog
- Melhorias na interface de administração
- Suporte a múltiplos CTAs por artigo
- Integração com Google AdSense

### Changed
- Atualização da estrutura do plugin
- Melhorias de performance
- Otimização do código

## [1.0.0] - 2024-06-01
### Added
- Lançamento inicial
- Sistema de pré-artigos
- Configuração de CTAs
- Template personalizado
- Painel de administração
