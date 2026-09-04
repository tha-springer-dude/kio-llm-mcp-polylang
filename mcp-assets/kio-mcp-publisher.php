<?php

require_once __DIR__ . '/vendor/autoload.php';

add_action( 'plugins_loaded', static function () {
    \WPMedia\MCP\OAuth\Bootstrap::instance();
} );


add_filter( 'wpmedia_mcp_oauth_trusted_publishers', function ( array $publishers ) {

    $publishers['chatgpt'] = array(
        'client_ids' => array(
            'https://chatgpt.com/oauth/client.json',
        ),
        'host' => 'chatgpt.com',
    );

    //error_log( 'KIO TRUSTED PUBLISHERS: ' . print_r( $publishers, true ) );

    return $publishers;
} );