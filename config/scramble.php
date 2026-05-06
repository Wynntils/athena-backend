<?php

/**
 * Scramble API Documentation Configuration
 *
 * Replaces the previous L5-Swagger / manual OpenAPI attribute documentation layer.
 * Scramble auto-generates OpenAPI docs by analysing controller code, routes, Form
 * Requests, and return types — no duplicate docs classes needed.
 *
 * @see https://scramble.dedoc.co/
 */

return [
    /*
     * API path used by the default route matcher. Set to empty string because
     * a custom route resolver in AppServiceProvider handles route filtering
     * via a prefix-allowlist instead.
     */
    'api_path' => '',

    /*
     * Your API domain. By default, app domain is used.
     */
    'api_domain' => null,

    /*
     * The path where your OpenAPI specification will be exported
     * when running `php artisan scramble:export`.
     */
    'export_path' => 'api.json',

    'info' => [
        /*
         * API version — matches the version previously defined in app/Docs/OpenAPI.php.
         */
        'version' => '1.0.0',

        /*
         * Description rendered on the home page of the API documentation.
         */
        'description' => 'Athena Backend API',
    ],

    /*
     * Customize the Stoplight Elements documentation UI.
     */
    'ui' => [
        'title' => 'Athena API Documentation',
        'theme' => 'dark',
        'hide_try_it' => false,
        'hide_schemas' => false,
        'logo' => 'https://wynntils.com/images/logo.png',
        'try_it_credentials_policy' => 'include',
        'layout' => 'responsive',
    ],

    /*
     * Server entries shown in the documentation and "Try It" dropdown.
     * Matches the servers previously defined in app/Docs/OpenAPI.php.
     */
    'servers' => [
        'Production' => 'https://athena.wynntils.com',
        'Localhost' => 'http://127.0.0.1',
    ],

    /**
     * Determines how Scramble stores the descriptions of enum cases.
     */
    'enum_cases_description_strategy' => 'description',

    /**
     * Determines how Scramble stores the names of enum cases.
     */
    'enum_cases_names_strategy' => false,

    /**
     * When true, Scramble flattens deep objects in query parameters so the
     * generated OpenAPI document correctly describes the API.
     */
    'flatten_deep_query_parameters' => true,

    /*
     * Middleware applied to the Scramble docs routes.
     * RestrictedDocsAccess is intentionally omitted — the docs are public.
     * Public access is also granted via a `viewApiDocs` gate in AuthServiceProvider.
     */
    'middleware' => [
        'web',
    ],

    'extensions' => [],
];
