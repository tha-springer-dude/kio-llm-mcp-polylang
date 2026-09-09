<?php
/**
 * KIO MCP – Find Posts by Category ID
 *
 * Registers an MCP ability for finding WordPress posts
 * belonging to a category by category ID.
 */

require_once __DIR__ . '/../includes/kio-polylang.php';

add_action( 'wp_abilities_api_init', 'my_post_find_by_category_id_register' );

function my_post_find_by_category_id_register() {

    wp_register_ability(
        'mykiopolylangmcp/find-posts-by-category-id',
        array(
            'label'       => 'Find Posts by Category ID',
            'description' => 'Finds WordPress posts in a category by category ID in the default language, with translations when requested.',
            'category'    => 'site',

            'execute_callback'    => 'my_custom_find_posts_by_category_id',
            'permission_callback' => 'my_custom_find_posts_by_category_id_permission',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(

                    'category_id' => array(
                        'type' => 'integer',
                    ),

                    'status' => array(
                        'type' => 'string',
                        'enum' => array(
                            'publish',
                            'draft',
                            'any',
                        ),
                    ),

                    'translations' => array(
                        'type' => 'boolean',
                    ),
                ),

                'required' => array( 'category_id' ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'results' => array(
                        'type'  => 'array',
                        'items' => array(
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

                                'translations' => array(
                                    'type'  => 'array',
                                    'items' => array(
                                        'type'       => 'object',
                                        'properties' => array(

                                            'id' => array(
                                                'type' => 'integer',
                                            ),

                                            'language' => array(
                                                'type' => 'string',
                                            ),

                                            'title' => array(
                                                'type' => 'string',
                                            ),

                                            'status' => array(
                                                'type' => 'string',
                                            ),
                                        ),
                                    ),
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


/**
 * Permission check.
 */
function my_custom_find_posts_by_category_id_permission() {

    return current_user_can( 'read' );
}


/**
 * Find posts belonging to a category by category ID.
 */
function my_custom_find_posts_by_category_id( $input ) {

    /*
     * STEP 1 — Make sure Polylang is available.
     */
    if ( ! kio_pll_is_available() ) {
        return array(
            'error' => 'Polylang is not available.',
        );
    }


    /*
     * STEP 2 — Get the site's default language.
     */
    $default_language = kio_pll_get_default_language();


    /*
     * STEP 3 — Get the requested category.
     */
    $category = get_category( $input['category_id'] );

    if ( ! $category || is_wp_error( $category ) ) {
        return array(
            'error' => 'Category not found.',
        );
    }


    /*
     * STEP 4 — Make sure the category itself belongs
     * to the default language.
     */
    $category_language = pll_get_term_language(
        $category->term_id,
        'slug'
    );

    if ( $category_language !== $default_language ) {
        return array(
            'error' => 'Category is not in the default language.',
        );
    }


    /*
     * STEP 5 — Prepare the WordPress post query.
     */
    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => -1,
        'category__in'   => array( $category->term_id ),
        'lang'           => $default_language,
    );


    /*
     * Status defaults to published posts.
     */
    if ( ! empty( $input['status'] ) ) {
        $args['post_status'] = $input['status'];
    } else {
        $args['post_status'] = 'publish';
    }


    /*
     * STEP 6 — Get matching posts.
     */
    $posts = get_posts( $args );

    $results = array();


    /*
     * STEP 7 — Build the results.
     */
    foreach ( $posts as $post ) {

        $result = array(
            'id'     => $post->ID,
            'title'  => $post->post_title,
            'status' => $post->post_status,
        );


        /*
         * STEP 8 — Add translations only when requested.
         */
        if (
            ! empty( $input['translations'] )
            && function_exists( 'pll_get_post_translations' )
        ) {

            $translations = kio_pll_get_post_translations(
                $post->ID
            );

            $translation_results = array();


            /*
             * Walk through the translated post IDs.
             */
            foreach ( $translations as $language => $translated_post_id ) {

                /*
                 * Do not return the default-language post again.
                 */
                if ( $translated_post_id === $post->ID ) {
                    continue;
                }


                /*
                 * Get the actual translated WordPress post.
                 */
                $translated_post = get_post(
                    $translated_post_id
                );


                /*
                 * Only add valid posts.
                 */
                if ( $translated_post ) {

                    $translation_results[] = array(
                        'id'       => $translated_post->ID,
                        'language' => $language,
                        'title'    => $translated_post->post_title,
                        'status'   => $translated_post->post_status,
                    );
                }
            }


            /*
             * Only add the translations property when
             * translated siblings actually exist.
             */
            if ( ! empty( $translation_results ) ) {
                $result['translations'] = $translation_results;
            }
        }


        $results[] = $result;
    }


    /*
     * STEP 9 — Return the completed result.
     */
    return array(
        'results' => $results,
    );
}