<?php
/**
 * KIO MCP – Find Posts by Category Name
 *
 * Registers an MCP ability for finding WordPress posts
 * belonging to a category by category name.
 */

require_once __DIR__ . '/../includes/kio-polylang.php';

add_action( 'wp_abilities_api_init', 'my_post_find_by_category_name_register' );

function my_post_find_by_category_name_register() {

    wp_register_ability(
        'mykiopolylangmcp/find-posts-by-category-name',
        array(
            'label'       => 'Find Posts by Category Name',
            'description' => 'Finds WordPress posts in a category by category name in the default language, with translations when requested.',
            'category'    => 'site',

            'execute_callback'    => 'my_custom_find_posts_by_category_name',
            'permission_callback' => 'my_custom_find_posts_by_category_name_permission',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(

                    'category_name' => array(
                        'type' => 'string',
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

                'required' => array( 'category_name' ),
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


function my_custom_find_posts_by_category_name_permission() {

    return current_user_can( 'read' );
}


function my_custom_find_posts_by_category_name( $input ) {

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
     * STEP 3 — Find the category by exact name
     * in the default language.
     */
    $categories = get_categories(
        array(
            'lang' => $default_language,
        )
    );

    $category = false;

    foreach ( $categories as $possible_category ) {

        if (
            strcasecmp(
                $possible_category->name,
                $input['category_name']
            ) === 0
        ) {
            $category = $possible_category;
            break;
        }
    }


    if ( ! $category ) {
        return array(
            'error' => 'Category not found.',
        );
    }


    /*
     * STEP 4 — Prepare the WordPress post query.
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
     * STEP 5 — Get matching posts.
     */
    $posts = get_posts( $args );

    $results = array();


    /*
     * STEP 6 — Build the results.
     */
    foreach ( $posts as $post ) {

        $result = array(
            'id'     => $post->ID,
            'title'  => $post->post_title,
            'status' => $post->post_status,
        );


        /*
         * STEP 7 — Add translations when requested.
         */
        if (
            ! empty( $input['translations'] )
            && function_exists( 'pll_get_post_translations' )
        ) {

            $translations = kio_pll_get_post_translations(
                $post->ID
            );

            $translation_results = array();


            foreach ( $translations as $language => $translated_post_id ) {

                if ( $translated_post_id === $post->ID ) {
                    continue;
                }

                $translated_post = get_post(
                    $translated_post_id
                );

                if ( $translated_post ) {

                    $translation_results[] = array(
                        'id'       => $translated_post->ID,
                        'language' => $language,
                        'title'    => $translated_post->post_title,
                        'status'   => $translated_post->post_status,
                    );
                }
            }


            if ( ! empty( $translation_results ) ) {
                $result['translations'] = $translation_results;
            }
        }


        $results[] = $result;
    }


    return array(
        'results' => $results,
    );
}