<?php
/**
 * KIO MCP – Polylang – Find Media by ID
 */

add_action(
    'wp_abilities_api_init',
    'my_media_library_find_by_id_register'
);

function my_media_library_find_by_id_register() {

    wp_register_ability(
        'mykiopolylangmcp/find-media-by-id',
        array(
            'label'       => 'Find Media by ID',
            'description' => 'Finds an image attachment in the WordPress Media Library by its ID.',
            'category'    => 'site',

            'meta' => array(
                'public' => true,
            ),

            'execute_callback'   => 'my_media_library_find_by_id',
            'permission_callback' => 'my_media_library_find_by_id_permission',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'media_id' => array(
                        'type'        => 'integer',
                        'description' => 'The ID of the image attachment.',
                    ),
                ),
                'required' => array(
                    'media_id',
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


function my_media_library_find_by_id_permission() {
    return current_user_can( 'read' );
}


function my_media_library_find_by_id( $input ) {

    $media_id = (int) $input['media_id'];

    $media = get_post( $media_id );

    if ( ! $media || 'attachment' !== $media->post_type ) {
        return new WP_Error(
            'media_not_found',
            'The requested media item was not found.'
        );
    }

    if ( 0 !== strpos( $media->post_mime_type, 'image/' ) ) {
        return new WP_Error(
            'not_an_image',
            'The requested media item is not an image.'
        );
    }

    return array(
        'id'      => $media->ID,
        'title'   => get_the_title( $media->ID ),
        'url'     => wp_get_attachment_url( $media->ID ),
        'alt'     => get_post_meta(
            $media->ID,
            '_wp_attachment_image_alt',
            true
        ),
        'caption' => $media->post_excerpt,
    );
}