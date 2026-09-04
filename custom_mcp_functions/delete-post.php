<?php

add_action( 'wp_abilities_api_init', 'my_post_delete_register' );

function my_post_delete_register() {

    wp_register_ability(
        'mykiomcp/delete-post',
        array(
            'label'       => 'Custom Delete Post',
            'description' => 'Permanently deletes an existing WordPress post.',
            'category'    => 'site',

            'execute_callback'    => 'my_custom_delete_post',
            'permission_callback' => 'my_custom_delete_post_permission',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'post_id' => array(
                        'type' => 'integer',
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
                    'deleted' => array(
                        'type' => 'boolean',
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

function my_custom_delete_post_permission() {
    return current_user_can( 'delete_posts' );
}

function my_custom_delete_post( $input ) {

    $post = get_post( $input['post_id'] );

    if ( ! $post ) {
        return array(
            'error' => 'Post not found.',
        );
    }

    $deleted = wp_delete_post( $input['post_id'], true );

    if ( ! $deleted ) {
        return array(
            'error' => 'Post could not be deleted.',
        );
    }

    return array(
        'id'      => $input['post_id'],
        'deleted' => true,
    );
}