<?php
/**
 * KIO MCP – Polylang – List Tags
 */

require_once __DIR__ . '/../includes/kio-polylang.php';

add_action(
    'wp_abilities_api_init',
    'my_polylang_tags_list_register'
);

function my_polylang_tags_list_register() {

    wp_register_ability(
        'mykiopolylangmcp/list-tags',
        array(
            'label'       => 'List Tags',
            'description' => 'Lists WordPress post tags in the default language. Set translations to true to include translated tag siblings.',
            'category'    => 'site',
            'meta'        => array(
                'public' => true,
            ),
            'execute_callback'   => 'my_polylang_list_tags',
            'permission_callback' => 'my_polylang_list_tags_permission',

            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'translations' => array(
                        'type'        => 'boolean',
                        'description' => 'Whether to include translated tag siblings.',
                    ),
                ),
            ),

            'output_schema' => array(
                'type'       => 'array',
                'items'      => array(
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
                    ),
                ),
            ),
        )
    );
}

function my_polylang_list_tags_permission() {
    return current_user_can( 'read' );
}

function my_polylang_list_tags( $input ) {

    $default_language = kio_pll_get_default_language();

    if ( ! $default_language ) {
        return new WP_Error(
            'polylang_unavailable',
            'Polylang is not available.'
        );
    }

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

    $include_translations = ! empty( $input['translations'] );

    $result = array();

    foreach ( $tags as $tag ) {

        $item = array(
            'id'    => (int) $tag->term_id,
            'name'  => $tag->name,
            'slug'  => $tag->slug,
            'count' => (int) $tag->count,
        );

        if ( $include_translations ) {

            $item['translations'] = array();

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

                $item['translations'][] = array(
                    'id'       => (int) $translated_tag->term_id,
                    'language' => $language,
                    'name'     => $translated_tag->name,
                    'slug'     => $translated_tag->slug,
                    'count'    => (int) $translated_tag->count,
                );
            }
        }

        $result[] = $item;
    }

    return $result;
}