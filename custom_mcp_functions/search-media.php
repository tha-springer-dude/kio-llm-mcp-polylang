<?php

add_action( 'wp_abilities_api_init', 'my_media_search_register' );

function my_media_search_register() {

    wp_register_ability(
        'mykiomcp/search-media',
        array(
            'label'       => 'Custom Search Media',
            'description' => 'Searches images in the WordPress Media Library.',
            'category'    => 'site',

            'execute_callback'    => 'my_custom_media_search',
            'permission_callback' => 'my_custom_media_search_permission',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'search' => array(
                        'type' => 'string',
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

            'meta' => array(
                'mcp' => array(
                    'public' => true,
                ),
            ),
        )
    );
}

function my_custom_media_search_permission() {
    return current_user_can( 'read' );
}

function my_custom_media_search( $input ) {

    $media = get_posts(
        array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'post_mime_type' => 'image',
            's'              => $input['search'],
            'posts_per_page' => -1,
        )
    );

    $results = array();

    foreach ( $media as $item ) {

        $results[] = array(
            'id'      => $item->ID,
            'title'   => $item->post_title,
            'url'     => wp_get_attachment_url( $item->ID ),
            'alt'     => get_post_meta( $item->ID, '_wp_attachment_image_alt', true ),
            'caption' => $item->post_excerpt,
        );
    }

    return array(
        'results' => $results,
    );
}