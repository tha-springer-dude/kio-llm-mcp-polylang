<?php
/**
 * KIO MCP – Create Post
 *
 * Creates a new WordPress post in the default language.
 * This ability does NOT create translations/siblings.
 */

require_once __DIR__ . '/../includes/kio-polylang.php';

add_action( 'wp_abilities_api_init', 'my_post_create_register' );

function my_post_create_register() {

    wp_register_ability(
        'mykiopolylangmcp/create-post',
        array(
            'label'       => 'Create Post',
            'description' => 'Creates a new WordPress post in the default language. This ability creates a new independent post and does not create translations or siblings.',
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

                'required' => array(
                    'title',
                    'content',
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


/**
 * Permission check.
 */
function my_custom_create_post_permission() {

    return current_user_can( 'edit_posts' );
}


/**
 * Create a new default-language post.
 */
function my_custom_create_post( $input ) {

    /*
     * Make sure Polylang is available.
     */
    if ( ! kio_pll_is_available() ) {
        return array(
            'error' => 'Polylang is not available.',
        );
    }


    /*
     * Get the site's default language.
     */
    $default_language = kio_pll_get_default_language();


    /*
     * Prepare the post data.
     */
    $post_data = array(
        'post_title'   => $input['title'],
        'post_content' => $input['content'],
        'post_status'  => ! empty( $input['status'] )
            ? $input['status']
            : 'draft',
        'post_type'    => 'post',
    );


    /*
     * Add categories when supplied.
     */
    if ( ! empty( $input['categories'] ) ) {
        $post_data['post_category'] = $input['categories'];
    }


    /*
     * Create the post.
     */
    $post_id = wp_insert_post(
        $post_data,
        true
    );


    /*
     * Check for an error.
     */
    if ( is_wp_error( $post_id ) ) {
        return array(
            'error' => $post_id->get_error_message(),
        );
    }


    /*
     * Explicitly assign the new post to the
     * site's default language.
     */
    pll_set_post_language(
        $post_id,
        $default_language
    );


    /*
     * Return the newly created post.
     */
    return array(
        'id'     => $post_id,
        'title'  => $input['title'],
        'status' => $post_data['post_status'],
    );
}