<?php
/**
 * KIO MCP – Create Post Translation
 *
 * Creates a translated sibling of an existing WordPress post.
 */

require_once __DIR__ . '/../includes/kio-polylang.php';

add_action( 'wp_abilities_api_init', 'my_post_create_translation_register' );

function my_post_create_translation_register() {

    wp_register_ability(
        'mykiopolylangmcp/create-post-translation',
        array(
            'label'       => 'Create Post Translation',
            'description' => 'Creates a translated sibling of an existing WordPress post. Use this only when the user explicitly wants to create a translation of an existing post. Do not use this for creating a new independent post.',
            'category'    => 'site',

            'execute_callback'    => 'my_custom_create_post_translation',
            'permission_callback' => 'my_custom_create_post_translation_permission',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(

                    'source_post_id' => array(
                        'type' => 'integer',
                    ),

                    'language' => array(
                        'type' => 'string',
                    ),

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
                ),

                'required' => array(
                    'source_post_id',
                    'language',
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

                    'source_post_id' => array(
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

                    'translation_created' => array(
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


/**
 * Permission check.
 */
function my_custom_create_post_translation_permission() {

    return current_user_can( 'edit_posts' );
}


/**
 * Create a translated sibling.
 */
function my_custom_create_post_translation( $input ) {

    /*
     * STEP 1 — Make sure Polylang is available.
     */
    if ( ! kio_pll_is_available() ) {
        return array(
            'error' => 'Polylang is not available.',
        );
    }


    /*
     * STEP 2 — Make sure the required Polylang
     * functions are available.
     */
    if (
        ! function_exists( 'pll_get_post_language' )
        || ! function_exists( 'pll_get_post_translations' )
        || ! function_exists( 'pll_set_post_language' )
        || ! function_exists( 'pll_save_post_translations' )
        || ! function_exists( 'pll_languages_list' )
    ) {
        return array(
            'error' => 'Required Polylang functions are not available.',
        );
    }


    /*
     * STEP 3 — Get the source post.
     */
    $source_post = get_post( $input['source_post_id'] );

    if ( ! $source_post || $source_post->post_type !== 'post' ) {
        return array(
            'error' => 'Source post not found.',
        );
    }


    /*
     * STEP 4 — Get the source post language.
     */
    $source_language = pll_get_post_language(
        $source_post->ID,
        'slug'
    );

    if ( ! $source_language ) {
        return array(
            'error' => 'Source post has no Polylang language.',
        );
    }


    /*
     * STEP 5 — Make sure the requested target language
     * actually exists.
     */
    $languages = pll_languages_list(
        array(
            'hide_empty' => false,
        )
    );

    if ( ! in_array( $input['language'], $languages, true ) ) {
        return array(
            'error' => 'Requested language is not configured in Polylang.',
        );
    }


    /*
     * STEP 6 — Do not create a duplicate translation.
     */
    $existing_translation = pll_get_post(
        $source_post->ID,
        $input['language']
    );

    if ( $existing_translation ) {
        return array(
            'error' => 'A translation already exists in the requested language.',
        );
    }


    /*
     * STEP 7 — Do not translate a post into its own language.
     */
    if ( $source_language === $input['language'] ) {
        return array(
            'error' => 'The requested language is the same as the source language.',
        );
    }


    /*
     * STEP 7b — Translate the source post's categories.
     */
    $translated_categories = array();
    $category_warnings = array();

$source_categories = wp_get_post_categories(
    $source_post->ID,
    array(
        'lang' => $source_language,
    )
);


    foreach ( $source_categories as $source_category_id ) {

            $translated_category_id = pll_get_term(
                $source_category_id,
                $input['language']
            );

            error_log(
                'KIO DEBUG: source category=' . $source_category_id .
                ' target language=' . $input['language'] .
                ' translated category=' . var_export( $translated_category_id, true )
            );

            if ( $translated_category_id ) {

                $translated_categories[] = $translated_category_id;

            } else {
            $source_category = get_category(
                $source_category_id
            );

            if ( $source_category ) {

                $category_warnings[] =
                    'No ' . $input['language'] .
                    ' translation exists for category "' .
                    $source_category->name . '".';
            }
        }
    }


    /*
     * STEP 8 — Create the new WordPress post.
     *
     * Draft is the safe default.
     */
    $post_data = array(
        'post_title'   => $input['title'],
        'post_content' => $input['content'],
        'post_status'  => ! empty( $input['status'] )
            ? $input['status']
            : $source_post->post_status,
        'post_type'    => 'post',
    );



    $new_post_id = wp_insert_post(
        $post_data,
        true
    );


    /*
     * Check whether WordPress created the post.
     */
    if ( is_wp_error( $new_post_id ) ) {
        return array(
            'error' => $new_post_id->get_error_message(),
        );
    }


    /*
     * STEP 9 — Assign the requested language
     * to the new post.
     */
    pll_set_post_language(
        $new_post_id,
        $input['language']
    );

if ( ! empty( $translated_categories ) ) {
    wp_set_post_categories(
        $new_post_id,
        $translated_categories
    );
}


    /*
     * STEP 10 — Get the complete existing
     * translation group.
     */
    $translations = pll_get_post_translations(
        $source_post->ID
    );


    /*
     * STEP 11 — Add the new sibling to that group.
     */
    $translations[ $input['language'] ] = $new_post_id;


    /*
     * STEP 12 — Save the complete translation relationship.
     *
     * This is the actual moment where the two posts
     * become Polylang siblings.
     */
    pll_save_post_translations(
        $translations
    );


    /*
     * STEP 13 — Return the result.
     */
    return array(
        'id'                  => $new_post_id,
        'source_post_id'      => $source_post->ID,
        'language'            => $input['language'],
        'title'               => $input['title'],
        'status'              => $post_data['post_status'],
        'translation_created' => true,
    );
}