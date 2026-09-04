<?php
/**
 * KIO MCP – Search Categories
 *
 * Registers an MCP ability for searching WordPress categories.
 */

add_action( 'wp_abilities_api_init', 'my_category_search_register' );

function my_category_search_register() {

    wp_register_ability(
        'mykiopolylangmcp/search-categories',
        array(
            'label'       => 'Custom Search Categories',
            'description' => 'Custom searches WordPress categories by name.',
            'category'    => 'site',

            'execute_callback'    => 'my_custom_category_search',
            'permission_callback' => 'my_custom_category_search_permission',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'search' => array(
                        'type' => 'string',
                    ),
                    'translations' => array(
                        'type' => 'boolean',
                    ),
                ),
                'required' => array( 'search' ),
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
                                'translations' => array(
                                    'type' => 'array',
                                    'items' => array(
                                        'type' => 'object',
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

function my_custom_category_search_permission() {
    return current_user_can( 'read' );
}

function my_custom_category_search( $input ) {

    if ( ! function_exists( 'pll_default_language' ) ) {
        return array(
            'error' => 'Polylang is not available.',
        );
    }

    $default_language = pll_default_language( 'slug' );

    $categories = get_categories(
        array(
            'lang' => $default_language,
        )
    );

    $results = array();

    foreach ( $categories as $category ) {

        if ( stripos( $category->name, $input['search'] ) === false ) {
            continue;
        }

        $result = array(
            'id'   => $category->term_id,
            'name' => $category->name,
        );

        if ( ! empty( $input['translations'] ) && function_exists( 'pll_get_term_translations' ) ) {

            $translations = pll_get_term_translations( $category->term_id );
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

        $results[] = $result;
    }

    return array(
        'results' => $results,
    );
}