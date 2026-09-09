<?php
/**
 * KIO MCP – Polylang – Find Tag by ID
 */

require_once __DIR__ . '/../includes/kio-polylang.php';

add_action(
    'wp_abilities_api_init',
    'my_polylang_tag_find_by_id_register'
);

function my_polylang_tag_find_by_id_register() {

    wp_register_ability(
        'mykiopolylangmcp/find-tag-by-id',
        array(
            'label'       => 'Find Tag by ID',
            'description' => 'Finds a WordPress post tag by ID in the default language. Set translations to true to include translated tag siblings.',
            'category'    => 'site',
            'meta'        => array(
                'public' => true,
            ),
            'execute_callback'    => 'my_polylang_find_tag_by_id',
            'permission_callback' => 'my_polylang_find_tag_by_id_permission',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'tag_id' => array(
                        'type'        => 'integer',
                        'description' => 'The ID of the tag to find.',
                    ),
                    'translations' => array(
                        'type'        => 'boolean',
                        'description' => 'Whether to include translated tag siblings.',
                    ),
                ),
                'required' => array(
                    'tag_id',
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
                    'count' => array(
                        'type' => 'integer',
                    ),
                    'translations' => array(
                        'type' => 'array',
                    ),
                ),
            ),
        )
    );
}

function my_polylang_find_tag_by_id_permission() {
    return current_user_can( 'read' );
}

function my_polylang_find_tag_by_id( $input ) {

    $tag = get_term(
        (int) $input['tag_id'],
        'post_tag'
    );

    if ( ! $tag || is_wp_error( $tag ) ) {
        return new WP_Error(
            'tag_not_found',
            'The requested tag was not found.'
        );
    }

    $result = array(
        'id'    => (int) $tag->term_id,
        'name'  => $tag->name,
        'slug'  => $tag->slug,
        'count' => (int) $tag->count,
    );

    if ( ! empty( $input['translations'] ) ) {

        $result['translations'] = array();

        $translations = kio_pll_get_term_translations(
            $tag->term_id
        );

        foreach ( $translations as $language => $translation_id ) {

            if ( (int) $translation_id === (int) $tag->term_id ) {
                continue;
            }

            $translated_tag = get_term(
                $translation_id,
                'post_tag'
            );

            if ( ! $translated_tag || is_wp_error( $translated_tag ) ) {
                continue;
            }

            $result['translations'][] = array(
                'id'       => (int) $translated_tag->term_id,
                'language' => $language,
                'name'     => $translated_tag->name,
                'slug'     => $translated_tag->slug,
                'count'    => (int) $translated_tag->count,
            );
        }
    }

    return $result;
}