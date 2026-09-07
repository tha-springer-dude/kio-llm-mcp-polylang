<?php
/**
 * KIO MCP – Polylang Helpers
 *
 * Shared internal functions used by the MCP abilities when working
 * with Polylang.
 *
 * These functions do not perform MCP operations themselves.
 * They provide the common language and translation logic that the
 * individual abilities need.
 *
 * General flow:
 *
 * MCP ability
 *     ↓
 * KIO Polylang helper
 *     ↓
 * Polylang public API
 *     ↓
 * WordPress object
 */


/**
 * Check whether the Polylang functions required by this plugin
 * are available.
 *
 * Keeping this check in one place means the individual abilities
 * don't all need to repeat function_exists() checks.
 *
 * @return bool True when Polylang's public API is available.
 */
function kio_pll_is_available() {

    return function_exists( 'pll_default_language' );
}


/**
 * Get the site's default language.
 *
 * We deliberately ask Polylang rather than hard-coding a language
 * such as "en" or "de".
 *
 * @return string|false Default language slug, or false if unavailable.
 */
function kio_pll_get_default_language() {

    if ( ! kio_pll_is_available() ) {
        return false;
    }

    return pll_default_language( 'slug' );
}


/**
 * Get all translations belonging to a WordPress post.
 *
 * Polylang returns an array mapping language slugs to post IDs.
 *
 * Example:
 *
 *     en => 123
 *     de => 456
 *     fr => 789
 *
 * The calling ability can then decide which of those siblings it
 * actually wants to expose.
 *
 * @param int $post_id WordPress post ID.
 * @return array Translation map, or an empty array.
 */
function kio_pll_get_post_translations( $post_id ) {

    if ( ! function_exists( 'pll_get_post_translations' ) ) {
        return array();
    }

    return pll_get_post_translations( $post_id );
}


/**
 * Get all translations belonging to a WordPress term.
 *
 * This is the term/category equivalent of the post translation helper.
 *
 * @param int $term_id WordPress term ID.
 * @return array Translation map, or an empty array.
 */
function kio_pll_get_term_translations( $term_id ) {

    if ( ! function_exists( 'pll_get_term_translations' ) ) {
        return array();
    }

    return pll_get_term_translations( $term_id );
}