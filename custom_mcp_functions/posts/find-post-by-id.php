<?php

// Load the shared Polylang helper functions.
require_once __DIR__ . '/../includes/kio-polylang.php';

add_action( 'wp_abilities_api_init', 'my_post_find_by_id_register' );

function my_post_find_by_id_register() {

    wp_register_ability(
        'mykiopolylangmcp/find-post-by-id',
        array(
            'label'       => 'Find Post by ID',
            'description' => 'Finds a WordPress post by ID, with translations when requested.',
            'category'    => 'site',

            'execute_callback'    => 'kio_mcp_find_post_by_id',
            'permission_callback' => 'kio_mcp_find_post_by_id_permission',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'post_id' => array(
                        'type' => 'integer',
                    ),
                    'translations' => array(
                        'type' => 'boolean',
                    ),
                ),
                'required' => array( 'post_id' ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'id' => array(
                        'type' => 'integer',
                    ),
                    'title' => array(
                        'type' => 'string',
                    ),
                    'content' => array(
                        'type' => 'string',
                    ),
                    'status' => array(
                        'type' => 'string',
                    ),
                    'categories' => array(
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
                    'tags' => array(
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

            'meta' => array(
                'mcp' => array(
                    'public' => true,
                ),
            ),
        )
    );
}

function kio_mcp_find_post_by_id_permission() {
    return current_user_can( 'read' );
}

function kio_mcp_find_post_by_id( $input ) {

    if ( ! kio_pll_is_available() ) {
        return array(
            'error' => 'Polylang is not available.',
        );
    }

    $post = get_post( $input['post_id'] );

    if ( ! $post || $post->post_type !== 'post' ) {
        return array(
            'error' => 'Post not found.',
        );
    }

    $categories = get_the_category( $post->ID );
    $category_results = array();

    foreach ( $categories as $category ) {
        $category_results[] = array(
            'id'   => $category->term_id,
            'name' => $category->name,
        );
    }

    $tags = get_the_tags( $post->ID );
    $tag_results = array();

    if ( $tags ) {
        foreach ( $tags as $tag ) {
            $tag_results[] = array(
                'id'   => $tag->term_id,
                'name' => $tag->name,
            );
        }
    }

    $result = array(
        'id'         => $post->ID,
        'title'      => $post->post_title,
        'content'    => $post->post_content,
        'status'     => $post->post_status,
        'categories' => $category_results,
        'tags'       => $tag_results,
    );

    if ( ! empty( $input['translations'] ) ) {

        $default_language = kio_pll_get_default_language();
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

    return $result;
}