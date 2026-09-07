<?php

// Load the shared Polylang helper functions.
require_once __DIR__ . '/../includes/kio-polylang.php';

add_action( 'wp_abilities_api_init', 'my_category_list_register' );

function my_category_list_register() {

    wp_register_ability(
        'mykiopolylangmcp/list-categories',
        array(
            'label'       => 'List Categories',
            'description' => 'Lists WordPress categories in the default language.',
            'category'    => 'site',

            'execute_callback'    => 'kio_mcp_list_categories',
            'permission_callback' => 'kio_mcp_list_categories_permission',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'results' => array(
                        'type'  => 'array',
                        'items' => array(
                            'type'       => 'object',
                            'properties' => array(
                                'id' => array(
                                    'type' => 'integer',
                                ),
                                'name' => array(
                                    'type' => 'string',
                                ),
                            ),
                        ),
                    ),
                ),
            ),

            'meta' => array(
                'mcp' => array(
                    'public' => true,
                ),
            ),
        )
    );
}

function kio_mcp_list_categories_permission() {
    return current_user_can( 'read' );
}

function kio_mcp_list_categories() {

    if ( ! kio_pll_is_available() ) {
        return array(
            'error' => 'Polylang is not available.',
        );
    }

    $default_language = kio_pll_get_default_language();

    $categories = get_categories(
        array(
            'lang' => $default_language,
        )
    );

    $results = array();

    foreach ( $categories as $category ) {

        $results[] = array(
            'id'   => $category->term_id,
            'name' => $category->name,
        );
    }

    return array(
        'results' => $results,
    );
}