<?php

// Load the shared Polylang helper functions.
require_once __DIR__ . '/../includes/kio-polylang.php';

add_action( 'wp_abilities_api_init', 'my_category_find_by_name_register' );

function my_category_find_by_name_register() {

    wp_register_ability(
        'mykiopolylangmcp/find-category-by-name',
        array(
            'label'       => 'Find Category by Name',
            'description' => 'Finds a WordPress category by exact name in the default language.',
            'category'    => 'site',

            'execute_callback'    => 'kio_mcp_find_category_by_name',
            'permission_callback' => 'kio_mcp_find_category_by_name_permission',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'name' => array(
                        'type' => 'string',
                    ),
                    'translations' => array(
                        'type' => 'boolean',
                    ),
                ),
                'required' => array( 'name' ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'id' => array(
                        'type' => 'integer',
                    ),
                    'name' => array(
                        'type' => 'string',
                    ),
                    'translations' => array(
                        'type' => 'array',
                        'items' => array(
                            'type'       => 'object',
                            'properties' => array(
                                'language' => array(
                                    'type' => 'string',
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

function kio_mcp_find_category_by_name_permission() {
    return current_user_can( 'read' );
}

function kio_mcp_find_category_by_name( $input ) {

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

    $category = false;

    foreach ( $categories as $item ) {

        if ( strcasecmp( $item->name, $input['name'] ) === 0 ) {
            $category = $item;
            break;
        }
    }

    if ( ! $category ) {
        return array(
            'error' => 'Category not found.',
        );
    }

    $result = array(
        'id'   => $category->term_id,
        'name' => $category->name,
    );

    if ( ! empty( $input['translations'] ) ) {

        $translations = kio_pll_get_term_translations( $category->term_id );
        $translation_results = array();

        foreach ( $translations as $language => $term_id ) {

            if ( $language === $default_language ) {
                continue;
            }

            $translated_term = get_term( $term_id );

            if ( $translated_term && ! is_wp_error( $translated_term ) ) {
                $translation_results[] = array(
                    'language' => $language,
                    'name'     => $translated_term->name,
                );
            }
        }

        if ( ! empty( $translation_results ) ) {
            $result['translations'] = $translation_results;
        }
    }

    return $result;
}