<?php
error_log( 'KIO DEBUG: list-media.php LOADED' );
/**
 * KIO MCP – Polylang – List Media
 */

add_action( 'wp_abilities_api_init', 'my_media_list_register');

function my_media_list_register() {

error_log( 'KIO DEBUG: my_media_list_register() CALLED' );

    wp_register_ability(
        'mykiopolylangmcp/list-media-library',
        array(
            'label'       => 'List Media Library',
            'description' => 'Lists image attachments from the WordPress Media Library.',
            'category'    => 'site',
            'meta' => array(
            'public' => true,
            ),
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
                                'title' => array(
                                    'type' => 'string',
                                ),
                                'url' => array(
                                    'type' => 'string',
                                ),
                                'alt' => array(
                                    'type' => 'string',
                                ),
                                'caption' => array(
                                    'type' => 'string',
                                ),
                            ),
                        ),
                    ),
                ),
            ),

            'permission_callback' => function () {
                return current_user_can( 'read' );
            },

            'execute_callback' => function () {

                $media = get_posts(
                    array(
                        'post_type'      => 'attachment',
                        'post_status'    => 'inherit',
                        'post_mime_type' => 'image',
                        'posts_per_page' => -1,
                    )
                );

                $results = array();

                foreach ( $media as $item ) {

                    $results[] = array(
                        'id'      => $item->ID,
                        'title'   => $item->post_title,
                        'url'     => wp_get_attachment_url( $item->ID ),
                        'alt'     => get_post_meta(
                            $item->ID,
                            '_wp_attachment_image_alt',
                            true
                        ),
                        'caption' => $item->post_excerpt,
                    );
                }

                return array(
                    'results' => $results,
                );
            },
        )
    );
}