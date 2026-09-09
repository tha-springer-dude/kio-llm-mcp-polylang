<?php

/**
 * KIO MCP – Polylang – Update Post
 */

add_action(
    'wp_abilities_api_init',
    'my_post_update_register'
);

function my_post_update_register() {

    wp_register_ability(
        'mykiopolylangmcp/update-post',
        array(
            'label'       => 'Update Post',
            'description' => 'Updates an existing WordPress post. Only the specified post is changed; its translations are not modified automatically.',

            'category' => 'site',

            'meta' => array(
                'public' => true,
            ),

            'execute_callback'   => 'my_custom_update_post',
            'permission_callback' => 'my_custom_update_post_permission',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(

                    'post_id' => array(
                        'type'        => 'integer',
                        'description' => 'The ID of the post to update.',
                    ),

                    'content' => array(
                        'type'        => 'string',
                        'description' => 'The new post content.',
                    ),

                    'status' => array(
                        'type' => 'string',
                        'enum' => array(
                            'draft',
                            'publish',
                        ),
                        'description' => 'Optional new post status.',
                    ),
                    'category_id' => array(
                        'type'        => 'integer',
                        'description' => 'Optional default-language category ID to assign to the post.',
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
                    'title' => array(
                        'type' => 'string',
                    ),
                    'status' => array(
                        'type' => 'string',
                    ),
                    'updated' => array(
                        'type' => 'boolean',
                    ),
                ),
            ),
        )
    );
}

function my_custom_update_post_permission( $input ) {

    error_log(
    'KIO UPDATE PERMISSION: user=' . get_current_user_id() .
    ' post=' . $input['post_id'] .
    ' can_edit=' . (
        current_user_can( 'edit_post', $input['post_id'] )
            ? 'YES'
            : 'NO'
    )
);
    return true;
}

function my_custom_update_post( $input ) {

    $post_id = (int) $input['post_id'];

    $post = get_post( $post_id );

    if ( ! $post || 'post' !== $post->post_type ) {
        return new WP_Error(
            'post_not_found',
            'The requested post was not found.'
        );
    }

    /*
     * Validate category before changing the post.
     */
    if ( array_key_exists( 'category_id', $input ) ) {

        $category = get_category(
            (int) $input['category_id']
        );

        if ( ! $category || is_wp_error( $category ) ) {
            return new WP_Error(
                'category_not_found',
                'The requested category was not found.'
            );
        }
    }

    $post_data = array(
        'ID' => $post_id,
    );

    if ( array_key_exists( 'content', $input ) ) {
        $post_data['post_content'] = $input['content'];
    }

    if ( array_key_exists( 'status', $input ) ) {
        $post_data['post_status'] = $input['status'];
    }

    $updated_id = wp_update_post(
        $post_data,
        true
    );

    if ( is_wp_error( $updated_id ) ) {
        return $updated_id;
    }

    /*
     * Update category after the post was successfully updated.
     */
    if ( array_key_exists( 'category_id', $input ) ) {

        wp_set_post_categories(
            $updated_id,
            array(
                (int) $input['category_id'],
            )
        );
    }

    $updated_post = get_post( $updated_id );

    return array(
        'id'      => $updated_post->ID,
        'title'   => $updated_post->post_title,
        'status'  => $updated_post->post_status,
        'updated' => true,
    );
}