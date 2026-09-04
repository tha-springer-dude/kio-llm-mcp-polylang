KIO LLM MCP – Polylang
======================

Custom WordPress MCP abilities for multilingual WordPress sites
using Polylang.

The project extends the WordPress MCP Adapter with custom abilities
for managing WordPress content through an MCP-compatible AI client.

Current abilities:

- Search categories
- Search posts
- Get posts
- Create posts
- Update posts
- Delete posts
- Search media

Polylang
--------

The multilingual design follows one simple principle:

    Default object by default.
    Siblings only when requested.

The default language is determined dynamically through Polylang.

For example, a category search can return:

    Development
        → German: Entwicklung

Translations are obtained through Polylang's public API rather than
hard-coded language-specific logic.

Status
------

Active development.

The category search is currently fully Polylang-aware. The remaining
abilities are being adapted and tested for multilingual content.

Requirements
------------

- WordPress
- WordPress MCP Adapter
- Polylang
- PHP
- MCP-compatible AI client