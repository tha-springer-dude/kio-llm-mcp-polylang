<?php

// Load the shared Polylang helper functions.
require_once __DIR__ . '/../includes/kio-polylang.php';

add_action( 'wp_abilities_api_init', 'my_category_find_by_id_register' );

function my_category_find_by_id_register() {

    wp_register_ability(
        'mykiopolylangmcp/find-category-by-id',
        array(
            'label'       => 'Find Category by ID',
            'description' => 'Finds a WordPress category by ID in the default language.',
            'category'    => 'site',

            'execute_callback'    => 'kio_mcp_find_category_by_id',
            'permission_callback' => 'kio_mcp_find_category_by_id_permission',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'id' => array(
                        'type' => 'integer',
                    ),
                    'translations' => array(
                        'type' => 'boolean',
                    ),
                ),
                'required' => array( 'id' ),
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

function kio_mcp_find_category_by_id_permission() {
    return current_user_can( 'read' );
}

function kio_mcp_find_category_by_id( $input ) {

    if ( ! kio_pll_is_available() ) {
        return array(
            'error' => 'Polylang is not available.',
        );
    }

    $category = get_category( $input['id'] );

    if ( ! $category || is_wp_error( $category ) ) {
        return array(
            'error' => 'Category not found.',
        );
    }

    $default_language = kio_pll_get_default_language();
    $category_language = pll_get_term_language( $category->term_id, 'slug' );

    if ( $category_language !== $default_language ) {
        return array(
            'error' => 'Category is not in the default language.',
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