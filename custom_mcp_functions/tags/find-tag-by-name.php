<?php
/**
 * KIO MCP – Polylang – Find Tag by Name
 */

add_action(
    'wp_abilities_api_init',
    'my_tag_find_by_name_register'
);

function my_tag_find_by_name_register() {

    wp_register_ability(
        'mykiopolylangmcp/find-tag-by-name',
        array(
            'label'       => 'Find Tag by Name',
            'description' => 'Finds a WordPress tag by its exact name in the default language. When the user asks for the translation or translated version of a tag, set translations to true to return its Polylang translations.',
            'category'    => 'site',

            'execute_callback'   => 'my_custom_find_tag_by_name',
            'permission_callback' => 'my_custom_find_tag_by_name_permission',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(

                    'name' => array(
                        'type'        => 'string',
                        'description' => 'The exact name of the tag to find.',
                    ),

                    'translations' => array(
                        'type'        => 'boolean',
                        'description' => 'Whether to include translated versions of the tag.',
                    ),

                ),
                'required' => array(
                    'name',
                ),
            ),

            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(

                    'id' => array(
                        'type' => 'integer',
                    ),

                    'name' => array(
                        'type' => 'string',
                    ),

                    'slug' => array(
                        'type' => 'string',
                    ),

                    'translations' => array(
                        'type' => 'object',
                    ),

                ),
            ),

            'meta' => array(
                'public' => true,
            ),
        )
    );
}


function my_custom_find_tag_by_name_permission() {

    return current_user_can( 'read' );
}


function my_custom_find_tag_by_name( $input ) {

    $name = trim( $input['name'] );

    $default_language = pll_default_language( 'slug' );

    $tags = get_terms(
        array(
            'taxonomy'   => 'post_tag',
            'hide_empty' => false,
            'lang'       => $default_language,
        )
    );

    if ( is_wp_error( $tags ) ) {
        return $tags;
    }

    foreach ( $tags as $tag ) {

        if ( strcasecmp( $tag->name, $name ) === 0 ) {

            $result = array(
                'id'   => (int) $tag->term_id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            );

            if ( ! empty( $input['translations'] ) ) {

                $result['translations'] = pll_get_term_translations(
                    $tag->term_id
                );
            }

            return $result;
        }
    }

    return new WP_Error(
        'tag_not_found',
        'No tag with that name was found.'
    );
}