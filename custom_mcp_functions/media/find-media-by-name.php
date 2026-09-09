<?php
/**
 * KIO MCP – Polylang – Find Media by Name
 */

add_action(
    'wp_abilities_api_init',
    'my_media_library_find_by_name_register'
);

function my_media_library_find_by_name_register() {

    wp_register_ability(
        'mykiopolylangmcp/find-media-by-name',
        array(
            'label'       => 'Find Media by Name',
            'description' => 'Finds an image attachment in the WordPress Media Library by its title.',
            'category'    => 'site',

            'meta' => array(
                'public' => true,
            ),

            'execute_callback'    => 'my_media_library_find_by_name',
            'permission_callback' => 'my_media_library_find_by_name_permission',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'name' => array(
                        'type'        => 'string',
                        'description' => 'The Media Library title of the image.',
                    ),
                ),
                'required' => array(
                    'name',
                ),
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
        )
    );
}


function my_media_library_find_by_name_permission() {
    return current_user_can( 'read' );
}


function my_media_library_find_by_name( $input ) {

    $name = trim( $input['name'] );

    $media = get_posts(
        array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'post_mime_type' => 'image',
            'posts_per_page' => -1,
        )
    );

    foreach ( $media as $item ) {

        if ( 0 === strcasecmp( $item->post_title, $name ) ) {

            return array(
                'id'      => $item->ID,
                'title'   => get_the_title( $item->ID ),
                'url'     => wp_get_attachment_url( $item->ID ),
                'alt'     => get_post_meta(
                    $item->ID,
                    '_wp_attachment_image_alt',
                    true
                ),
                'caption' => $item->post_excerpt,
            );
        }
    }

    return new WP_Error(
        'media_not_found',
        'No image with the requested Media Library title was found.'
    );
}