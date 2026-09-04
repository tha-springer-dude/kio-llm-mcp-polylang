<?php

add_action( 'wp_abilities_api_init', 'my_post_create_register' );

function my_post_create_register() {

    wp_register_ability(
        'mykiomcp/create-post',
        array(
            'label'       => 'Custom Create Post',
            'description' => 'Creates a new WordPress post.',
            'category'    => 'site',

            'execute_callback'    => 'my_custom_create_post',
            'permission_callback' => 'my_custom_create_post_permission',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'title' => array(
                        'type' => 'string',
                    ),
                    'content' => array(
                        'type' => 'string',
                    ),
                    'status' => array(
                        'type' => 'string',
                        'enum' => array(
                            'draft',
                            'publish',
                        ),
                    ),
                    'categories' => array(
                        'type'  => 'array',
                        'items' => array(
                            'type' => 'integer',
                        ),
                    ),
                ),
                'required' => array( 'title', 'content' ),
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

function my_custom_create_post_permission() {
    return current_user_can( 'edit_posts' );
}

function my_custom_create_post( $input ) {

    $post_id = wp_insert_post(
            array(
                'post_title'   => $input['title'],
                'post_content' => $input['content'],
                'post_status'  => $input['status'] ?? 'draft',
                'post_type'    => 'post',
                'post_category' => $input['categories'] ?? array(),
            ),
        true
    );

    if ( is_wp_error( $post_id ) ) {
        return array(
            'error' => $post_id->get_error_message(),
        );
    }

    return array(
        'id'     => $post_id,
        'title'  => get_the_title( $post_id ),
        'status' => get_post_status( $post_id ),
    );
}