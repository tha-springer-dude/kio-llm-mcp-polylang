<?php
/**
 * Plugin Name: KIO LLM MCP
 * Description: Custom MCP abilities for WordPress.
 * Version: 1.0.0
 * Author: Willie Springer
 */

require_once __DIR__ . '/mcp-assets/kio-mcp-publisher.php';


require_once __DIR__ . '/custom_mcp_functions/categories/list-categories.php';
require_once __DIR__ . '/custom_mcp_functions/categories/find-category-by-id.php';
require_once __DIR__ . '/custom_mcp_functions/categories/find-category-by-name.php';

require_once __DIR__ . '/custom_mcp_functions/posts/list-posts.php';
require_once __DIR__ . '/custom_mcp_functions/posts/find-post-by-id.php';
require_once __DIR__ . '/custom_mcp_functions/posts/find-posts-by-title.php';
require_once __DIR__ . '/custom_mcp_functions/posts/find-posts-by-category-id.php';
require_once __DIR__ . '/custom_mcp_functions/posts/find-posts-by-category-name.php';
require_once __DIR__ . '/custom_mcp_functions/posts/create-post.php';
require_once __DIR__ . '/custom_mcp_functions/posts/create-post-translation.php';
require_once __DIR__ . '/custom_mcp_functions/posts/update-post.php';

require_once __DIR__ . '/custom_mcp_functions/media/list-media-library.php';
require_once __DIR__ . '/custom_mcp_functions/media/find-media-by-id.php';
require_once __DIR__ . '/custom_mcp_functions/media/find-media-by-name.php';