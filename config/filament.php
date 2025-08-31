    <?php

    // use App\Filament\Pages\Auth\UserLogin;
    // use App\Filament\Pages\Auth\SupplierLogin;

    // return [

    //     'default_panel' => 'admin',

    //     'panels' => [

    //         'admin' => [
    //             'id' => 'admin',
    //             'path' => 'dashboard',
    //             'auth' => [
    //                 'guard' => 'pharmacy',
    //                 'pages' => [
    //                     'login' => \App\Filament\Pages\Auth\UserLogin::class,
    //                 ],
    //             ],
    //         ],

    //         'supplier' => [
    //             'id' => 'supplier',
    //             'path' => 'supplier/dashboard',
    //             'auth' => [
    //                 'guard' => 'supplier',
    //                 'pages' => [
    //                     'login' => \App\Filament\Pages\Auth\SupplierLogin::class,
    //                 ],
    //             ],
    //         ],
    //     ],

    //     'providers' => [
    //         \App\Providers\Filament\AdminPanelProvider::class,
    //         \App\Providers\Filament\SupplierPanelProvider::class, // ✅ This must be included
    //     ],
    // ];
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    use App\Filament\Pages\Auth\SupplierLogin;
    use App\Filament\Pages\Auth\UserLogin;

    return [

        /*
    |--------------------------------------------------------------------------
    | Filament Path
    |--------------------------------------------------------------------------
    |
    | The default is `admin` but you can change it to whatever works best and
    | doesn't conflict with the routing in your application.
    |
    */

        'path' => 'dashboard',

        /*
    |--------------------------------------------------------------------------
    | Filament Core Path
    |--------------------------------------------------------------------------
    |
    | This is the path which Filament will use to load its core assets from.
    | You may need to change this value if you're behind a proxy or have
    | a subdirectory in your application.
    |
    */

        'core_path' => '/',

        /*
    |--------------------------------------------------------------------------
    | Filament Assets Path
    |--------------------------------------------------------------------------
    |
    | This is the path where Filament will serve assets from. You may need to
    | change this value if you're behind a proxy or have a subdirectory in
    | your application.
    |
    */

        'assets_path' => null,

        /*
    |--------------------------------------------------------------------------
    | Filament Layout
    |--------------------------------------------------------------------------
    |
    | This is the configuration for the layout of Filament. You can change the
    | spacing, max content width, and the theme.
    |
    */

        'layout' => [
            'actions' => [
                'modal' => [
                    'actions' => [
                        'alignment' => 'left',
                    ],
                ],
            ],
            'forms' => [
                'actions' => [
                    'alignment' => 'left',
                ],
                'have_inline_labels' => false,
                'have_inline_field_containers' => false,
                'have_inline_labels_for_toggle_buttons' => false,
                'have_inline_labels_for_file_upload_buttons' => false,
            ],
            'max_content_width' => null,
            'spacing' => 'default',
        ],

        /*
    |--------------------------------------------------------------------------
    | Filament Theme
    |--------------------------------------------------------------------------
    |
    | This is the configuration for the theme of Filament. You can change the
    | colors, dark mode, and the custom CSS classes.
    |
    */

        'theme' => [
            'dark_mode' => true,
            'url' => null,
        ],

        /*
    |--------------------------------------------------------------------------
    | Filament Database Notifications
    |--------------------------------------------------------------------------
    |
    | This is the configuration for the database notifications feature of Filament.
    | You can change the database connection, queue, and the model.
    |
    */

        'database_notifications' => [
            'enabled' => true,
            'queue' => null,
            'ttl' => null,
        ],

        /*
    |--------------------------------------------------------------------------
    | Filament User Menu
    |--------------------------------------------------------------------------
    |
    | This is the configuration for the user menu in Filament. You can change the
    | profile URL, logout URL, and the items in the menu.
    |
    */

        'user_menu' => [
            'avatar_url' => null,
            'profile_url' => null,
            'logout_url' => null,
            'items' => [],
        ],

        /*
    |--------------------------------------------------------------------------
    | Filament Widgets
    |--------------------------------------------------------------------------
    |
    | This is the configuration for the widgets in Filament. You can change the
    | default namespace and the path where widgets are stored.
    |
    */

        'widgets' => [
            'default_namespace' => 'App\\Filament\\Widgets',
            'path' => app_path('Filament/Widgets'),
        ],

        /*
    |--------------------------------------------------------------------------
    | Filament Plugins
    |--------------------------------------------------------------------------
    |
    | This is the configuration for the plugins in Filament. You can change the
    | default namespace and the path where plugins are stored.
    |
    */

        'plugins' => [
            'default_namespace' => 'App\\Filament\\Plugins',
            'path' => app_path('Filament/Plugins'),
        ],

        /*
    |--------------------------------------------------------------------------
    | Filament Default Panel
    |--------------------------------------------------------------------------
    |
    | This is the default panel that Filament will use. You can change the
    | default panel by setting the `default` key to the ID of the panel.
    |
    */

        'default_panel' => 'admin',

        /*
    |--------------------------------------------------------------------------
    | Filament Panels
    |--------------------------------------------------------------------------
    |
    | This is the configuration for the panels in Filament. You can change the
    | default panel, the path, and the provider.
    |
    */

        'default_panel' => 'admin',

        'panels' => [
            'admin' => [
                'id' => 'admin',
                'path' => 'dashboard',
                'auth' => [
                    'guard' => 'pharmacy',
                    'pages' => [
                        'login' => UserLogin::class,
                    ],
                ],
            ],
            'supplier' => [
                'id' => 'supplier',
                'path' => 'supplier',
                'auth' => [
                    'guard' => 'supplier',
                    'pages' => [
                        'login' => SupplierLogin::class,
                    ],
                ],
            ],
        ],

        'providers' => [
            App\Providers\Filament\AdminPanelProvider::class,
            App\Providers\Filament\SupplierPanelProvider::class,
        ],

    ];
