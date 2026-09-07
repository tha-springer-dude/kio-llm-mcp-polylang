<?php
// Load the shared Polylang helper functions.
require_once __DIR__ . '/includes/kio-polylang.php';

/**
 * KIO MCP – Search Categories
 *
 * Registers an MCP ability for searching WordPress categories.
 */

/**
 * ABOUT THIS PLUGIN
 *
 * This plugin extends the WordPress MCP setup with multilingual
 * abilities for sites using Polylang.
 *
 * General flow:
 *
 * AI request
 *     ↓
 * MCP ability
 *     ↓
 * WordPress
 *     ↓
 * Polylang when language relationships are needed
 *     ↓
 * MCP response
 *
 * Core multilingual rule:
 *
 * Default object by default.
 * Siblings only when requested.
 */



/**
 * Register this ability when WordPress's Abilities API is ready.
 *
 * The registration below tells WordPress/MCP:
 *
 * - what this ability is called
 * - what it does
 * - what input the AI may send
 * - what output the AI will receive
 * - which PHP function actually performs the search
 * - which PHP function decides whether the current user may use it
 */ 
add_action( 'wp_abilities_api_init', 'my_category_search_register' );

/**
 * Register the category-search ability with WordPress.
 *
 * Think of this block as the "interface" of the ability.
 * It does not perform the search itself. Instead, it describes the
 * ability so that WordPress and the MCP layer know how to call it.
 */
function my_category_search_register() {

    wp_register_ability(
        'mykiopolylangmcp/search-categories',
        array(
   
             /*
             * Human-readable information about the ability.
             */    
            'label'       => 'Custom Search Categories',
            'description' => 'Custom searches WordPress categories by name.',
            'category'    => 'site',

            /*
             * These two callbacks separate security from functionality.
             *
             * permission_callback:
             *     "Is this user allowed to do this?"
             *
             * execute_callback:
             *     "Now actually perform the search."
             */
            'execute_callback'    => 'my_custom_category_search',
            'permission_callback' => 'my_custom_category_search_permission',

            /*
             * INPUT SCHEMA
             *
             * This describes what the AI is allowed to send us.
             *
             * Example:
             *
             * {
             *     "search": "development",
             *     "translations": true
             * }
             *
             * "search" is required.
             * "translations" is optional and controls whether translated
             * sibling categories should also be returned.
             */
            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'search' => array(
                        'type' => 'string',
                    ),
                    'translations' => array(
                        'type' => 'boolean',
                    ),
                ),
                'required' => array( 'search' ),
            ),

             /*
             * OUTPUT SCHEMA
             *
             * This describes the structure we promise to return to MCP.
             *
             * The important part is that the normal result contains:
             *
             *     id
             *     name
             *
             * and translations are added only when requested and available.
             *
             * Example result:
             *
             * {
             *     "id": 848,
             *     "name": "development",
             *     "translations": [
             *         {
             *             "language": "de",
             *             "name": "entwicklung"
             *         }
             *     ]
             * }
             */
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
                                'name' => array(
                                    'type' => 'string',
                                ),
                                'translations' => array(
                                    'type' => 'array',
                                    'items' => array(
                                        'type' => 'object',
                                        'properties' => array(
                                            'language' => array(
                                                'type' => 'string',
                                            ),
                                            'name' => array(
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

            /*
             * Make the ability available through MCP.
             */
            'meta' => array(
                'mcp' => array(
                    'public' => true,
                ),
            ),
        )
    );
}

/**
 * Decide whether the current WordPress user may search categories.
 *
 * This is deliberately kept separate from the search itself.
 *
 * If the user cannot read site content, the ability should not execute.
 */
function my_custom_category_search_permission() {
    return current_user_can( 'read' );
}

/**
 * Perform the actual category search.
 *
 * Important Polylang rule:
 *
 *     Default object by default.
 *     Siblings only when requested.
 *
 * So we do NOT search every language and mix the results together.
 * First we find the site's default language and search only those
 * categories.
 *
 * If the caller asks for translations, we then use Polylang's
 * relationship API to find the translated versions of each matching
 * category.
 *
 * @param array $input MCP input containing the search term and translation flag.
 * @return array Search results, or an error when Polylang is unavailable.
 */
function my_custom_category_search( $input ) {

    /*
     * STEP 1 — Make sure Polylang is available.
     *
     * This ability depends on Polylang because we need its API to:
     *
     * - determine the default language
     * - find translated category IDs
     *
     * function_exists() prevents a fatal PHP error if Polylang is
     * disabled or unavailable.
     */
    // Stop if Polylang is not available.
    if ( ! kio_pll_is_available() ) {
        return array(
            'error' => 'Polylang is not available.',
        );
    }

     /*
     * STEP 2 — Find the site's default language.
     *
     * We deliberately do NOT write something like:
     *
     *     $default_language = 'en';
     *
     * because the plugin must work on different multilingual sites.
     *
     * Polylang tells us what the site's default language actually is.
     */
    //$default_language = pll_default_language( 'slug' );
    
    // Get the site's default language from Polylang.
    $default_language = kio_pll_get_default_language();
     /*
     * STEP 3 — Get categories belonging to the default language.
     *
     * WordPress/Polylang gives us category objects here.
     *
     * We are intentionally starting with the default-language categories.
     * Translated siblings will only be looked up later if requested.
     */
    $categories = get_categories(
        array(
            'lang' => $default_language,
        )
    );

     /*
     * STEP 4 — Prepare the MCP result list.
     *
     * Every matching category will eventually be added to this array.
     */
    $results = array();

    /*
     * STEP 5 — Examine each default-language category.
     */
    foreach ( $categories as $category ) {

        /*
         * Only keep categories whose name contains the requested search text.
         *
         * stripos() makes the comparison case-insensitive.
         *
         * Example:
         *
         * search = "dev"
         *
         * "Development" → match
         * "News"         → no match
         */
        if ( stripos( $category->name, $input['search'] ) === false ) {
            continue;
        }

        /*
         * STEP 6 — Build the basic result.
         *
         * At this point we have found a matching default-language category.
         *
         * This is the primary object returned to MCP.
         */
        $result = array(
            'id'   => $category->term_id,
            'name' => $category->name,
        );


        /*
         * STEP 7 — Only look for translations when requested.
         *
         * This keeps the normal response simple.
         *
         * Example:
         *
         * "What categories are there?"
         *
         *     → default-language categories only
         *
         * "Show the categories and their translations."
         *
         *     → default category + translated siblings
         */
        if ( ! empty( $input['translations'] ) && function_exists( 'pll_get_term_translations' ) ) {

            /*
             * Ask Polylang for the translation relationship.
             *
             * The returned array maps language codes to term IDs.
             *
             * Conceptually:
             *
             *     en → 848
             *     de → 912
             *     fr → 1044
             *
             * We have the default category already, so we use these IDs
             * to find its translated siblings.
             */
            //$translations = pll_get_term_translations( $category->term_id );

            // Get the translated category IDs from Polylang.
            $translations = kio_pll_get_term_translations( $category->term_id );
            $translation_results = array();

            /*
             * STEP 8 — Walk through the translated category IDs.
             */
            foreach ( $translations as $language => $term_id ) {

                 /*
                 * The default-language category is already our main result.
                 *
                 * Do not add it again to the translations array.
                 */
                if ( $language === $default_language ) {
                    continue;
                }

                /*
                 * STEP 9 — Turn the translated term ID back into a
                 * normal WordPress term object.
                 *
                 * Polylang gives us the relationship/ID.
                 * WordPress gives us the actual category object.
                 */
                $translated_term = get_term( $term_id );

                /*
                 * Only add the translation if WordPress successfully
                 * returned a valid term.
                 */
                if ( $translated_term && ! is_wp_error( $translated_term ) ) {
                    $translation_results[] = array(
                        'language' => $language,
                        'name'     => $translated_term->name,
                    );
                }
            }

            /*
             * STEP 10 — Add translations to the result only when
             * at least one translated category actually exists.
             *
             * This means we don't return:
             *
             *     "translations": []
             *
             * for categories that have no translated sibling.
             */
            if ( ! empty( $translation_results ) ) {
                $result['translations'] = $translation_results;
            }
        }

        $results[] = $result;
    }

/*
    * STEP 11 — Add the completed category to the final result list.
    */
    return array(
        'results' => $results,
    );
}