<?php 
use Cake\Core\Configure;

return [
    'HybridAuth' => [
        'providers' => [
            'Google' => [
                'enabled' => true,
                'keys' => [
                    'id' => '82924589894-388fhk22j4okn4o31ju8rnhr57irvkt2.apps.googleusercontent.com',
                    'secret' => 'y7bshIFXCweaxqknvDhujTZn'
                ],
                'redirect_url' => 'http://microfin.empalla.co.tz/hybrid-auth/endpoint?hauth.done=Google'
            ],
            'Facebook' => [
                'enabled' => true,
                'keys' => [
                    'id' => '<facebook-application-id>',
                    'secret' => '<secret-key>'
                ],
                'scope' => 'email, user_about_me, user_birthday, user_hometown'
            ],
            'Twitter' => [
                'enabled' => true,
                'keys' => [
                    'key' => '<twitter-key>',
                    'secret' => '<twitter-secret>'
                ],
                'includeEmail' => true // Only if your app is whitelisted by Twitter Support
            ]
        ],
        'debug_mode' => Configure::read('debug'),
        'debug_file' => LOGS . 'hybridauth.log',
    ]
];


?>