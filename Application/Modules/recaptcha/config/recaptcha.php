<?php

return array(
    
    'locale' => [
        'lang' => 'en'                                             // Captcha language
    ],
    'api' => [
        'key' => [
            'site' => '6LcWtwUTAAAAACzJjC2NVhHipNPzCtjKa5tiE6tM',  // Api public site key
            'secret' => '6LcWtwUTAAAAAEwwpWdoBMT7dJcAPlborJ-QyW6C',// Api secret key
        ]
    ],
    'user' => [                                                    // Optional
        'autoSendIp' => false                                      // The end user's ip address.
    ],
    'form' => [                                                    // Captcha input configuration.
        'input' => [
            'attributes' => [
                'name' => 'recaptcha',  // Creates hidden input for validator class
                'id' => 'recaptcha',
                'type' => 'text',
                'value' => 1,
                'style' => 'display:none;',
            ]
        ],
        'validation' => [
            'enabled' => true,  // Whether to use validator package
            'callback' => true,
        ]
    ]
);

/* End of file recaptcha.php */
/* Location: .config/recaptcha/recaptcha.php */
