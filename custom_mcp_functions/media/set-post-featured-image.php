<?php
/**
 * KIO MCP – Polylang – Set Post Featured Image
 */

add_action(
    'wp_abilities_api_init',
    'my_polylang_set_post_featured_image_register'
);

function my_polylang_set_post_featured_image_register() {

    wp_register_ability(
        'mykiopolylangmcp/set-post-featured-image',
        array(
            'label'       => 'Set Post Featured Image',
            'description' => 'Sets an image from the WordPress Media Library as the featured image of an existing WordPress post.',

            'category'    => 'site',

            'meta' => array(
                'public' => true,
            ),

            'execute_callback'    => 'my_polylang_set_post_featured_image',
            'permission_callback' => 'my_polylang_set_post_featured_image_permission',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(

                    'post_id' => array(
                        'type'        => 'integer',
                        'description' => 'The ID of the post.',
                    ),

                    'media_id' => array(
                        'type'        => 'integer',
                        'description' => 'The ID of the image from the WordPress Media Library.',
                    ),

                ),

                'required' => array(
                    'post_id',
                    'media_id',
                ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(

                    'post_id' => array(
                        'type' => 'integer',
                    ),

                    'media_id' => array(
                        'type' => 'integer',
                    ),

                    'updated' => array(
                        'type' => 'boolean',
                    ),

                ),
            ),
        )
    );
}


function my_polylang_set_post_featured_image_permission( $input ) {

    if ( empty( $input['post_id'] ) ) {
        return false;
    }

    return current_user_can(
        'edit_post',
        $input['post_id']
    );
}


function my_polylang_set_post_featured_image( $input ) {

    $post_id  = (int) $input['post_id'];
    $media_id = (int) $input['media_id'];

    $post = get_post( $post_id );

    if ( ! $post || 'post' !== $post->post_type ) {
        return new WP_Error(
            'post_not_found',
            'The requested post was not found.'
        );
    }

    $media = get_post( $media_id );

    if (
        ! $media ||
        'attachment' !== $media->post_type ||
        ! wp_attachment_is_image( $media_id )
    ) {
        return new WP_Error(
            'media_not_image',
            'The requested media item is not a valid image.'
        );
    }

    $result = set_post_thumbnail(
        $post_id,
        $media_id
    );

    if ( ! $result ) {
        return new WP_Error(
            'featured_image_failed',
            'The featured image could not be set.'
        );
    }

    return array(
        'post_id'  => $post_id,
        'media_id' => $media_id,
        'updated'  => true,
    );
}