<?php
/**
 * KIO MCP – Find Posts by Title
 *
 * Registers an MCP ability for finding WordPress posts by title.
 */

// Load the shared Polylang helper functions.
require_once __DIR__ . '/../includes/kio-polylang.php';

add_action( 'wp_abilities_api_init', 'my_post_find_by_title_register' );

function my_post_find_by_title_register() {

    wp_register_ability(
        'mykiopolylangmcp/find-posts-by-title',
        array(
            'label'       => 'Find Posts by Title',
            'description' => 'Finds WordPress posts by title in the default language and includes their translations.',
            'category'    => 'site',

            'execute_callback'    => 'kio_mcp_find_posts_by_title',
            'permission_callback' => 'kio_mcp_find_posts_by_title_permission',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'title' => array(
                        'type' => 'string',
                    ),
                    'status' => array(
                        'type' => 'string',
                        'enum' => array(
                            'publish',
                            'draft',
                            'any',
                        ),
                    ),
                ),
                'required' => array( 'title' ),
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
                                            'id' => array(
                                                'type' => 'integer',
                                            ),
                                            'language' => array(
                                                'type' => 'string',
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

function kio_mcp_find_posts_by_title_permission() {
    return current_user_can( 'read' );
}

function kio_mcp_find_posts_by_title( $input ) {

    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => -1,
    );

    if ( ! empty( $input['status'] ) ) {
        $args['post_status'] = $input['status'];
    } else {
        $args['post_status'] = 'publish';
    }

    $posts   = get_posts( $args );
    $results = array();

    $default_language = kio_pll_get_default_language();

    foreach ( $posts as $post ) {

        // Only search posts in the default language.
        if ( $default_language && function_exists( 'pll_get_post_language' ) ) {

            $language = pll_get_post_language( $post->ID, 'slug' );

            if ( $language !== $default_language ) {
                continue;
            }
        }

        if ( stripos( $post->post_title, $input['title'] ) === false ) {
            continue;
        }

        $translations = array();

        $translation_ids = kio_pll_get_post_translations( $post->ID );

        foreach ( $translation_ids as $language => $translation_id ) {

            if ( (int) $translation_id === (int) $post->ID ) {
                continue;
            }

            $translation = get_post( $translation_id );

            if ( ! $translation ) {
                continue;
            }

            $translations[] = array(
                'id'       => $translation->ID,
                'language' => $language,
                'title'    => $translation->post_title,
                'status'   => $translation->post_status,
            );
        }

        $results[] = array(
            'id'           => $post->ID,
            'title'        => $post->post_title,
            'status'       => $post->post_status,
            'translations' => $translations,
        );
    }

    return array(
        'results' => $results,
    );
}