<?php

// Load the shared Polylang helper functions.
require_once __DIR__ . '/../includes/kio-polylang.php';

add_action( 'wp_abilities_api_init', 'my_post_list_register' );

function my_post_list_register() {

    wp_register_ability(
        'mykiopolylangmcp/list-posts',
        array(
            'label'       => 'List Posts',
            'description' => 'Lists WordPress posts in the default language by status, with translations when requested.',
            'category'    => 'site',

            'execute_callback'    => 'kio_mcp_list_posts',
            'permission_callback' => 'kio_mcp_list_posts_permission',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'status' => array(
                        'type' => 'string',
                        'enum' => array(
                            'publish',
                            'draft',
                            'any',
                        ),
                    ),
                    'translations' => array(
                        'type' => 'boolean',
                    ),
                ),
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
                                'title' => array(
                                    'type' => 'string',
                                ),
                                'status' => array(
                                    'type' => 'string',
                                ),
                                'translations' => array(
                                    'type'  => 'array',
                                    'items' => array(
                                        'type'       => 'object',
                                        'properties' => array(
                                            'language' => array(
                                                'type' => 'string',
                                            ),
                                            'id' => array(
                                                'type' => 'integer',
                                            ),
                                            'title' => array(
                                                'type' => 'string',
                                            ),
                                            'status' => array(
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

function kio_mcp_list_posts_permission() {
    return current_user_can( 'read' );
}

function kio_mcp_list_posts( $input ) {

    if ( ! kio_pll_is_available() ) {
        return array(
            'error' => 'Polylang is not available.',
        );
    }

    $default_language = kio_pll_get_default_language();

    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => -1,
        'lang'           => $default_language,
    );

    if ( isset( $input['status'] ) ) {
        $args['post_status'] = $input['status'];
    } else {
        $args['post_status'] = 'publish';
    }

    $posts = get_posts( $args );

    $results = array();

    foreach ( $posts as $post ) {

        $result = array(
            'id'     => $post->ID,
            'title'  => $post->post_title,
            'status' => $post->post_status,
        );

        if ( ! empty( $input['translations'] ) ) {

            $translations = kio_pll_get_post_translations( $post->ID );
            $translation_results = array();

            foreach ( $translations as $language => $post_id ) {

                if ( $language === $default_language ) {
                    continue;
                }

                $translated_post = get_post( $post_id );

                if ( $translated_post ) {
                    $translation_results[] = array(
                        'language' => $language,
                        'id'       => $translated_post->ID,
                        'title'    => $translated_post->post_title,
                        'status'   => $translated_post->post_status,
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