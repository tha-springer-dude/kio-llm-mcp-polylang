<?php
/**
 * KIO MCP – Polylang – Set Post Tags
 */

add_action(
    'wp_abilities_api_init',
    'my_polylang_set_post_tags_register'
);

function my_polylang_set_post_tags_register() {

    wp_register_ability(
        'mykiopolylangmcp/set-post-tags',
        array(
            'label'       => 'Set Post Tags',
            'description' => 'Sets the tags assigned to an existing WordPress post. Replaces the post’s current tags with the supplied tag IDs.',
            'category'    => 'site',

            'meta' => array(
                'public' => true,
            ),

            'execute_callback'    => 'my_polylang_set_post_tags',
            'permission_callback' => 'my_polylang_set_post_tags_permission',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(

                    'post_id' => array(
                        'type'        => 'integer',
                        'description' => 'The ID of the post whose tags should be changed.',
                    ),

                    'tag_ids' => array(
                        'type'        => 'array',
                        'description' => 'The tag IDs to assign to the post. Replaces the existing tags.',
                        'items'       => array(
                            'type' => 'integer',
                        ),
                    ),

                ),

                'required' => array(
                    'post_id',
                    'tag_ids',
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

                    'tag_ids' => array(
                        'type'  => 'array',
                        'items' => array(
                            'type' => 'integer',
                        ),
                    ),

                    'updated' => array(
                        'type' => 'boolean',
                    ),

                ),
            ),
        )
    );
}


function my_polylang_set_post_tags_permission( $input ) {

    if ( empty( $input['post_id'] ) ) {
        return false;
    }

    return current_user_can(
        'edit_post',
        $input['post_id']
    );
}


function my_polylang_set_post_tags( $input ) {

    $post_id = (int) $input['post_id'];

    $post = get_post( $post_id );

    if ( ! $post || 'post' !== $post->post_type ) {
        return new WP_Error(
            'post_not_found',
            'The requested post was not found.'
        );
    }

    $tag_ids = array_map(
        'intval',
        $input['tag_ids']
    );

    $result = wp_set_post_tags(
        $post_id,
        $tag_ids,
        false
    );

    if ( is_wp_error( $result ) ) {
        return $result;
    }

    return array(
        'id'      => $post_id,
        'title'   => $post->post_title,
        'tag_ids' => $tag_ids,
        'updated' => true,
    );
}