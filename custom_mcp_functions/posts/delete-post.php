<?php
/**
 * KIO MCP – Polylang – Delete Post
 */

add_action(
    'wp_abilities_api_init',
    'my_polylang_delete_post_register'
);

function my_polylang_delete_post_register() {

    wp_register_ability(
        'mykiopolylangmcp/delete-post',
        array(
            'label'       => 'Delete Post',
            'description' => 'Permanently deletes an existing WordPress post. This does not automatically delete its translations.',

            'category'    => 'site',

            'meta' => array(
                'public' => true,
            ),

            'execute_callback'    => 'my_polylang_delete_post',
            'permission_callback' => 'my_polylang_delete_post_permission',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(

                    'post_id' => array(
                        'type'        => 'integer',
                        'description' => 'The ID of the post to permanently delete.',
                    ),

                ),

                'required' => array(
                    'post_id',
                ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(

                    'id' => array(
                        'type' => 'integer',
                    ),

                    'deleted' => array(
                        'type' => 'boolean',
                    ),

                ),
            ),
        )
    );
}


function my_polylang_delete_post_permission( $input ) {

    if ( empty( $input['post_id'] ) ) {
        return false;
    }

    return current_user_can(
        'delete_post',
        $input['post_id']
    );
}


function my_polylang_delete_post( $input ) {

    $post_id = (int) $input['post_id'];

    $post = get_post( $post_id );

    if ( ! $post || 'post' !== $post->post_type ) {
        return new WP_Error(
            'post_not_found',
            'The requested post was not found.'
        );
    }

    $deleted = wp_delete_post(
        $post_id,
        true
    );

    if ( ! $deleted ) {
        return new WP_Error(
            'delete_failed',
            'The post could not be deleted.'
        );
    }

    return array(
        'id'      => $post_id,
        'deleted' => true,
    );
}