<?php

return array(

    'mod' => 'cool',  // Set default mod ( "secure" or "cool" ].

    'locale' => [
        'charset' => 'UTF-8'
    ],

    'characters' => [
        'default' => [
            'pool' => 'random'                  // Pools: numbers - alpha - random
        ],
        'pools' => [
            'numbers' => '23456789',
            'alpha'   => 'ABCDEFGHJKLMNPRSTUVWXYZ',
            'random'  => '23456789ABCDEFGHJKLMNPRSTUVWXYZ'
        ],
        'length' => 5                           // Character length of captcha code
    ],

    'font' => [
        'size' => 30,                           // Font size
        'path' => '/assets/fonts',              // Set captcha font path
    ],

    'image' => [
        'trueColor'  => true,                   // Php imagecreatetruecolor(), but it isn't always available
        'type'       => 'png',                  // Set image extension
        'wave'       => true,                   // Image wave for more strong captchas.
        'height'     => 80,                     // Height of captcha image, we calculate the "width" auto no need to set it.
        'expiration' => 1440,                   // Expiration time of captcha
    ],

    'colors' => [                          // Color Schema
        'red'    => '255,0,0',
        'blue'   => '0,0,255',
        'green'  => '0,102,0',
        'black'  => '0,0,0',
        'yellow' => '255,255,0',
        'cyan'   => '0,146,134',
    ],

    'form' => [
        'input' => [
            'attributes' => [              // Set input attributes
                'type'  => 'text',
                'name'  => 'captcha_answer',
                'class' => 'captcha',
                'id'    => 'captcha_answer'         
            ]
        ],
        'img' => [                         // This array <img> data
            'attributes' => [             
                'src'   =>  '/index.php/captcha/create',
                'style' => 'display:block;',
                'id'    => 'captcha_image',
                'class' => ''
            ]
        ],
        'refresh' => [
            'button' => '<input type="button" value="%s" onclick="oResetCaptcha(this.form);" style="margin-bottom:5px;" />',
            'script' => '<script type="text/javascript">
                function oResetCaptcha(form) {
                  form.%s.src="%s?noCache=" + Math.random();
                  form.%s.value = "";
                }
            </script>',
        ],
        'validation' => [
            'enabled' => true,
            'callback' => true,
        ]
    ],

    'text' => [
        'colors' =>  [
            'text' => ['red'],            // If its more than one produce random colors
            'noise' => ['red']            // If its more than one produce random noise colors
        ]
    ],

    'fonts' => [                           // Defined Fonts
        'AlphaSmoke'             => 'AlphaSmoke.ttf',
        'Anglican'               => 'Anglican.ttf',
        'Bknuckss'               => 'Bknuckss.ttf',
        'KingthingsFlashbang'    => 'KingthingsFlashbang.ttf',
        'NightSkK'               => 'NightSkK.ttf',
        'Notjustatoy'            => 'Notjustatoy.ttf',
        'Popsf'                  => 'Popsf.ttf',
        'SurreAlfreak'           => 'SurreAlfreak.ttf',
    ],
);

/* End of file image.php */
/* Location: .config/captcha/image.php */
