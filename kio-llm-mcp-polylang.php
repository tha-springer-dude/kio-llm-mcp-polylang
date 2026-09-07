<?php
/**
 * Plugin Name: KIO LLM MCP
 * Description: Custom MCP abilities for WordPress.
 * Version: 1.0.0
 * Author: Willie Springer
 */

require_once __DIR__ . '/mcp-assets/kio-mcp-publisher.php';


require_once __DIR__ . '/custom_mcp_functions/update-post.php';
require_once __DIR__ . '/custom_mcp_functions/create-post.php';
require_once __DIR__ . '/custom_mcp_functions/search-media.php';
require_once __DIR__ . '/custom_mcp_functions/delete-post.php';

require_once __DIR__ . '/custom_mcp_functions/categories/list-categories.php';
require_once __DIR__ . '/custom_mcp_functions/categories/find-category-by-id.php';
require_once __DIR__ . '/custom_mcp_functions/categories/find-category-by-name.php';

require_once __DIR__ . '/custom_mcp_functions/posts/list-posts.php';
require_once __DIR__ . '/custom_mcp_functions/posts/find-post-by-id.php';
require_once __DIR__ . '/custom_mcp_functions/posts/find-posts-by-title.php';