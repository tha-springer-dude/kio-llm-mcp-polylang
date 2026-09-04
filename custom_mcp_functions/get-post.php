<?php

add_action( 'wp_abilities_api_init', 'my_post_get_register' );

function my_post_get_register() {

    wp_register_ability(
        'mykiomcp/get-post',
        array(
            'label'       => 'Custom Get Post',
            'description' => 'Retrieves a WordPress post by ID.',
            'category'    => 'site',

            'execute_callback'    => 'my_custom_get_post',
            'permission_callback' => 'my_custom_get_post_permission',

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
        'title' => array(
            'type' => 'string',
        ),
        'content' => array(
            'type' => 'string',
        ),
        'status' => array(
            'type' => 'string',
        ),
        'categories' => array(
            'type'  => 'array',
            'items' => array(
                'type'       => 'object',
                'properties' => array(
                    'id' => array(
                        'type' => 'integer',
                    ),
                    'name' => array(
                        'type' => 'string',
                    ),
                ),
            ),
        ),
        'tags' => array(
            'type'  => 'array',
            'items' => array(
                'type'       => 'object',
                'properties' => array(
                    'id' => array(
                        'type' => 'integer',
                    ),
                    'name' => array(
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

function my_custom_get_post_permission() {
    return current_user_can( 'read' );
}

function my_custom_get_post( $input ) {

    $post = get_post( $input['post_id'] );

    if ( ! $post ) {
        return array(
            'error' => 'Post not found.',
        );
    }

    $categories = get_the_category( $post->ID );
    $category_results = array();

    foreach ( $categories as $category ) {
        $category_results[] = array(
            'id'   => $category->term_id,
            'name' => $category->name,
        );
    }

    $tags = get_the_tags( $post->ID );
    $tag_results = array();

    if ( $tags ) {
        foreach ( $tags as $tag ) {
            $tag_results[] = array(
                'id'   => $tag->term_id,
                'name' => $tag->name,
            );
        }
    }

    return array(
        'id'         => $post->ID,
        'title'      => $post->post_title,
        'content'    => $post->post_content,
        'status'     => $post->post_status,
        'categories' => $category_results,
        'tags'       => $tag_results,
    );
}