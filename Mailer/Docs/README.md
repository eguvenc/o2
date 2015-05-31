

## Mailer Class

------

The Mailer package contains a mail sending functions that assist in working with Mail, Sendmail, SMTP protocols and Transports services like Mandrill, Queue and any others.

### Initializing the Class

------

```php
$this->c['mailer'];
$this->mailer->method();
```

#### Mail Class supports the following features:

* Multiple Protocols: Mail, Sendmail, SMTP
* Multiple recipients
* Transactional Email Api Calls
* CC and BCCs
* HTML or Plaintext email
* Attachments
* Word wrapping
* Priorities
* BCC Batch Mode, enabling large email lists to be broken into small BCC batches.
* Email debugging tools
* Sending Email
* Sending Emails to Queue Service

Sending email is not only simple, but you can configure it on the fly or set your preferences in a config file.

Here is a basic example demonstrating how you might send email. 

**Note:** This example assumes you are sending the email from one of your controllers.

#### Service Configuration

```php
<?php

namespace Service;

/**
 * Mailer Service
 *
 * @category  Service
 * @package   Mail
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/services
 */
Class Mailer implements ServiceInterface
{
    /**
     * Registry
     *
     * @param object $c container
     * 
     * @return void
     */
    public function register($c)
    {
        $c['mailer'] = function () use ($c) {
            $mailer = $c['app']->provider('mailer')->get(['driver' => 'mandrill', 'options' => array('queue' => false)]);
            $mailer->from('Admin <admin@example.com>');
            return $mailer;
        };
    }
}

// END Mailer class

/* End of file Mailer.php */
/* Location: .classes/Service/Mailer.php */

```

Example code

```php
<?php
$this->c['mailer'];

// $this->mailer->from('your@example.com', 'Your Name');
$this->mailer->to('someone@example.com'); 
$this->mailer->cc('another@another-example.com'); 
$this->mailer->bcc('them@their-example.com'); 
$this->mailer->subject('Email Test');
$this->mailer->message('Testing the email class.');	
$this->mailer->send();

echo $this->mailer->printDebugger();
```

#### Using Service Provider


```php
$this->mailer = $c['app']->provider('mailer')->get('driver' => 'smtp');
$this->mailer->method();
```

#### Using Service Provider Queue Option

```php
$this->mailer = $c['app']->provider('mailer')->get(
    [
        'driver' => 'mandrill', 
        'options' => array('queue' => true)
    ]
);
$this->mailer->method();
```

### Setting Email Preferences

You can either set preferences manually as described here, or automatically via preferences stored in your config file, described below:

Preferences are set by passing an array of preference values to the email initialize function. Here is an example of how you might set some preferences:

```php
<?php
/*
|--------------------------------------------------------------------------
| Mail Class Configuration
|--------------------------------------------------------------------------
| Configuration file
|
*/
return array(

    'drivers' => [
        'mail' => '\Obullo\Mailer\Transport\Mail',
        'smtp' => '\Obullo\Mailer\Transport\Smtp',
        'sendmail' => '\Obullo\Mailer\Transport\Sendmail',
        'mandrill' => '\Obullo\Mailer\Transport\Mandrill',
    ],

    'useragent' => 'Obullo Mailer',  // Mailer "user agent".
    'wordwrap' => true,              // "true" or "false" (boolean) Enable word-wrap.
    'wrapchars' => 76,               // Character count to wrap at.
    'mailtype' => 'html',            // text or html Type of mail. If you send HTML email you must send it as a complete web page. 
    'charset' => 'utf-8',            // Character set (utf-8, iso-8859-1, etc.).
    'validate' => false,             // Whether to validate the email address.
    'priority' =>  3,                // 1, 2, 3, 4, 5   Email Priority. 1 = highest. 5 = lowest. 3 = normal.
    'crlf'  => "\n",                 //  "\r\n" or "\n" or "\r"  Newline character. (Use "\r\n" to comply with RFC 822).
    'newline' =>  "\n",              // "\r\n" or "\n" or "\r"  Newline character. (Use "\r\n" to comply with RFC 822).

    'bccBatch' => array(
        'mode' => false,             // true or false (boolean) Enable BCC Batch Mode.
        'size' => 200,               // None  Number of emails in each BCC batch.
    ),
    
    'queue' => array(
        'channel' => 'Mail',                // Queue Mailer channel name
        'route' => gethostname().'.Mailer', // Queue Mailer route name
        'worker' => 'Workers\Mailer',       // Queue Worker Class
    )
);

/* End of file transport.php */
/* Location: .app/config/env.local/mailer/transport.php */
```

### Setting Preferences Manually

```php
<?php
$config = array(
    'send' => array(
        'settings' => array(
            'protocol' => 'sendmail',
            'mailpath' => '/usr/sbin/sendmail',
            'charset' => 'iso-8859-1',
            'wordwrap' => true,
        )
    )
);
$this->mailer->init($config);
```

**Note:** Most of the preferences have default values that will be used if you do not set them.

### Email Preferences

The following is a list of all the preferences that can be set when sending email.

<table>
    <thead>
            <tr>
                <th>Preference</th>
                <th>Default</th>
                <th>Options</th>
                <th>Description</th>
            </tr>
    </thead>
    <tbody>
            <tr>
                <td>useragent</td>
                <td>Obullo</td>
                <td>None</td>
                <td>The "user agent" of mail service.</td>
            </tr>
            <tr>
                <td>mailpath</td>
                <td>/usr/sbin/sendmail</td>
                <td>None</td>
                <td>The server path to Sendmail.</td>
            </tr>
            <tr>
                <td>smtpHost</td>
                <td>No</td>
                <td>None</td>
                <td>SMTP Server Address.</td>
            </tr>
            <tr>
                <td>smtpUser</td>
                <td>No</td>
                <td>None</td>
                <td>SMTP Username.</td>
            </tr>
            <tr>
                <td>smtpPass</td>
                <td>No</td>
                <td>None</td>
                <td>SMTP Password.</td>
            </tr>
            <tr>
                <td>smtpPort</td>
                <td>25</td>
                <td>None</td>
                <td>SMTP Port.</td>
            </tr>
            <tr>
                <td>smtpTimeout</td>
                <td>5</td>
                <td>None</td>
                <td>SMTP Timeout (in seconds).</td>
            </tr>
            <tr>
                <td>wordwrap</td>
                <td>true</td>
                <td>true or false (boolean)</td>
                <td>Enable word-wrap.</td>
            </tr>
            <tr>
                <td>wordwrap</td>
                <td>true</td>
                <td>true or false (boolean)</td>
                <td>Enable word-wrap.</td>
            </tr>
            <tr>
                <td>wrapchars</td>
                <td>76</td>
                <td></td>
                <td>Character count to wrap at</td>
            </tr>
            <tr>
                <td>mailtype</td>
                <td>text</td>
                <td>text or html</td>
                <td>Type of mail. If you send HTML email you must send it as a complete web page. Make sure you don't have any relative links or relative image paths otherwise they will not work.</td>
            </tr>
            <tr>
                <td>charset</td>
                <td>utf-8</td>
                <td>utf-8, iso-8859-1, etc.</td>
                <td>Character set.</td>
            </tr>
            <tr>
                <td>validate</td>
                <td>false</td>
                <td>true or false (boolean)</td>
                <td>Whether to validate the email address</td>
            </tr>
            <tr>
                <td>priority</td>
                <td>false</td>
                <td>true or false (boolean)</td>
                <td>Whether to validate the email address</td>
            </tr>
            <tr>
                <td>crlf</td>
                <td>\n</td>
                <td>"\r\n" or "\n" or "\r"</td>
                <td>Newline character. (Use "\r\n" to comply with RFC 822).</td>
            </tr>
            <tr>
                <td>newline</td>
                <td>\n</td>
                <td>"\r\n" or "\n" or "\r"</td>
                <td>Newline character. (Use "\r\n" to comply with RFC 822).</td>
            </tr>
            <tr>
                <td>bccBatchMode</td>
                <td>false</td>
                <td>true or false (boolean)</td>
                <td>Enable BCC Batch Mode.</td>
            </tr>
            <tr>
                <td>bccBatchSize</td>
                <td>200</td>
                <td>None</td>
                <td>Number of emails in each BCC batch.</td>
            </tr>

            
    </tbody>
</table>


### Getting Transactional Email Api Call Results

```php
<?php
$this->mailer->from('test@example.com', 'Your Name');
$this->mailer->to('example@example.com'); 
$this->mailer->cc('obulloframework@gmail.com');
$this->mailer->subject('Email Test');
$this->mailer->message('Testing the email class.'); 
$this->mailer->send();

$r = $this->mailer->response()->getArray();

print_r($r); // see example results for Mandrill

/*
Array ( 
        [0] => Array ( [email] => example@example.com [status] => sent [_id] => a775e412a29f4c4587a7d5242c951dd8 [reject_reason] => ) 
        [1] => Array ( [email] => obulloframework@gmail.com [status] => sent [_id] => 015b1e527ae84c2090ae9feb9d573d8d [reject_reason] => )
) 
*/

echo $this->mailer->printDebugger();  // gives debug output

/*
Framework Mailer
Fri, 21 Nov 2014 13:31:30 +0000
example@example.com
obulloframework@gmail.com
Email Test
"Your Name" 
=?utf-8?Q?Email_Test?=
*/
```

### Function Reference

------

#### $this->mailer->from()

Sets the email address and name of the person sending the email:

```
<?php
$this->mailer->from('you@example.com', 'Your Name');
```

#### $this->mailer->replyTo(string $email, string $name)

Sets the reply-to address. If the information is not provided the information in the "from" function is used. Example:

```php
<?php
$this->mailer->replyTo('you@example.com', 'Your Name');
$this->mailer->to()
```

Sets the email address(s) of the recipient(s). Can be a single email, a comma-delimited list or an array:

```php
<?php
$this->mailer->to('someone@example.com');
$this->mailer->to('one@example.com, two@example.com, three@example.com');
$list = array('one@example.com', 'two@example.com', 'three@example.com');
$this->mailer->to($list);
```

#### $this->mailer->cc()

Sets the CC email address(s). Just like the "to", can be a single email, a comma-delimited list or an array.

#### $this->mailer->bcc()

Sets the BCC email address(s). Just like the "to", can be a single email, a comma-delimited list or an array.

#### $this->mailer->subject()

Sets the email subject:

```php
<?php
$this->mailer->subject('This is my subject');
$this->mailer->message();
```

#### $this->mailer->body()

Sets the email message body:

```php
<?php
$this->mailer->message('This is my message');
$this->mailer->setAltMessage();
```

Sets the alternative email message body:

$this->mailer->setAltMessage('This is the alternative message');

This is an optional message string which can be used if you send HTML formatted email. It lets you specify an alternative message with no HTML formatting which is added to the header string for people who do not accept HTML email. If you do not set your own message CodeIgniter will extract the message from your HTML email and strip the tags.

#### $this->mailer->clear()

Initializes all the email variables to an empty state. This function is intended for use if you run the email sending function in a loop, permitting the data to be reset between cycles.


If you set the parameter to true any attachments will be cleared as well:

```php
<?php
$this->mailer->clear(true);
$this->mailer->send()
```

The Email sending function. Returns boolean TRUE or FALSE based on success or failure, enabling it to be used conditionally:

```php
<?php
if ( ! $this->mailer->send()) {
    // Generate error
}
```

#### $this->mailer->attach()

Enables you to send an attachment. Put the file path/name in the first parameter. Note: Use a file path, not a URL. For multiple attachments use the function multiple times. For example:

```php
<?php
$this->mailer->attach('/path/to/photo1.jpg');
$this->mailer->attach('/path/to/photo2.jpg');
$this->mailer->attach('/path/to/photo3.jpg');
$this->mailer->send();
$this->mailer->printDebugger();
```

#### $this->mailer->printDebugger();

Returns a string containing any server messages, the email headers, and the email messsage. Useful for debugging.

#### $this->mailer->response()->getArray();

Returns to array response if your email provider support.

#### $this->mailer->response()->getRaw();

Returns to raw data output of http request.

#### $this->mailer->response()->getXml();

Returns to xml response if your email provider support.


#### Overriding Word Wrapping

If you have word wrapping enabled (recommended to comply with RFC 822) and you have a very long link in your email it can get wrapped too, causing it to become un-clickable by the person receiving it. CodeIgniter lets you manually override word wrapping within part of your message like this:

The text of your email that
gets wrapped normally.

```php
{unwrap}http://example.com/a_long_link_that_should_not_be_wrapped.html{/unwrap}
```

More text that will be
wrapped normally.

Place the item you do not want word-wrapped between: {unwrap} {/unwrap}


### QueueMailer ( Recommended )

------

QueueMailer class allows to you send your emails in the background using Queue service.

If you prefer to use QueueMailer class you will get better performance, but you need to change service configuration and configure a worker to consume queue data.

#### QueueMailer Service

Update your service like below the example.

```php
<?php

namespace Service;

use Obullo\Mail\QueueMailer;

/**
 * Mailer Service
 *
 * @category  Service
 * @package   Mail
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/services
 */
Class Mailer implements ServiceInterface
{
    /**
     * Registry
     *
     * @param object $c container
     * 
     * @return void
     */
    public function register($c)
    {
        $c['mailer'] = function () use ($c) {
            return new QueueMailer($c, $c->load('config')['mail']);
        };
    }
}

// END Mailer class

/* End of file Mailer.php */
/* Location: .classes/Service/Mailer.php */
```

#### QueueMailer Worker

You can configure your worker like below.

```php
<?php

namespace Workers;

use Obullo\Queue\Job;
use Obullo\Queue\JobInterface;
use Obullo\Mail\Transport\Smtp;
use Obullo\Mail\Transport\Mandrill;

 /**
 * Mail Worker
 *
 * @category  Workers
 * @package   Mailer
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/queue
 */
Class Mailer implements JobInterface
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Config parameters
     * 
     * @var array
     */
    protected $config;

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
        $this->config = $c['config']->load('mailer');
    }

    /**
     * Fire the job
     * 
     * @param Job   $job  object
     * @param array $data data array
     * 
     * @return void
     */
    public function fire(Job $job, $data)
    {
        $data = $data['message'];

        switch ($data['mailer']) {

        case 'mandrill':
            $mail = new Mandrill($this->c, $this->config);

            $mail->setMailType($data['mailtype']);
            $mail->from($data['from_email'], $data['from_name']);

            foreach ($data['to'] as $to) {
                $method = $to['type'];
                $mail->$method($to['name'].' <'.$to['email'].'>');
            }
            $mail->subject($data['subject']);
            $mail->message($data[$mail->getMailType()]);

            if (isset($data['attachments'])) {
                foreach ($data['attachments'] as $attachments) {
                    $mail->attach($attachments['fileurl'], 'attachment');
                }
            }
            if (isset($data['images'])) {
                foreach ($data['images'] as $attachments) {
                    $mail->attach($attachments['fileurl'], 'inline');
                }
            }
            $mail->addMessage('send_at', $mail->setDate($data['send_at']));
            $mail->send();

            // print_r($mail->response()->getArray());
            // echo $mail->printDebugger();
            break;

        case 'smtp':

            break;
        }
        /**
         * Delete job from queue after successfull operation.
         */
        $job->delete(); 
            
    }
}

/* End of file Mailer.php */
/* Location: .app/classes/Mailer.php */
```

#### QueueMailer Push Data Format

QueueMailer class send mail data to queue service using following the format.

```php
<?php
    /* PUSH DATA
    {
        "message": {
            "mailer": "mandrill",  // smtp
            "mailtype" "html"      // text
            "html": "<p>Example HTML content</p>",
            "text": "Example text content",
            "subject": "example subject",
            "from_email": "message.from_email@example.com",
            "from_name": "Example Name",
            "to": [
                {
                    "email": "recipient.email@example.com",
                    "name": "Recipient Name",
                    "type": "to"
                }
            ],
            "headers": {
                "Reply-To": "message.reply@example.com"
            },
            "important": false,
            "auto_text": null,
            "auto_html": null,
            "inline_css": null,
            "tags": [
                "password-resets"
            ],
            "attachments": [
                {
                    "type": "text/plain",
                    "name": "myfile.txt",
                    "fileurl": "/var/www/myfile.text"
                }
            ],
            "images": [
                {
                    "type": "image/png",
                    "name": "file.png",
                    "fileurl": "http://www.example.com/images/file.png",
                }
            ]
        },
        "send_at": "example send_at"
    }
    */
```

<kbd>app/Workers/Mailer.php</kbd> parse this format and send your emails in the background.


#### Following Workers/Mailer Output

Uncomments below the lines from your <kbd>app/Workers/Mailer.php</kbd> file.

```php
<?php
print_r($mail->response()->getArray());
echo $mail->printDebugger();
```

Then type to your console and run:

```php
php task queue listen --channel=Mail --route=localhost.Mailer --debug=1
```

If you set parameter <b>--debug=1</b> this means you can see all outputs of the worker. In production configuration you need to use parameter <b>--debug=0</b> or default is always "0".


When you send an email in the application you will get below the output on your console:

```php
                _           _ _       
           ___ | |__  _   _| | | ___  
          / _ \| '_ \| | | | | |/ _ \ 
         | (_) | |_) | |_| | | | (_) |
          \___/|_.__/ \__,_|_|_|\___/  

        Welcome to Queue Manager (c) 2015
    You are running \$php task queue command. For help type php task queue --help."

Array
(
    [] => Array
        (
            [email] => me@example.com
            [status] => queued
            [_id] => 3e537bd42820445da198d64a4f1b99ab
        )

    [1] => Array
        (
            [email] => test@example.com
            [status] => queued
            [_id] => 7c882ac765c74db193900a000f3b19e3
        )

)
<pre>Headers: 
Framework Mailer Transport
Wed, 26 Nov 2014 09:54:43 +0000
<me@example.com>
<test@example.com>
=?utf-8?Q?Email_Test?=
"\"Your Name\"" <admin@example.com>

Subject:  
=?utf-8?Q?Email_Test?=

Message: 
Testing the email class.</pre>Output : 
{"job":"Workers\\Mailer","data":{"message":{"html":"Testing the email class.","mailer":"mandrill","mailtype":"html","subject":"=?utf-8?Q?Email_Test?=","from_email":"test@example.com","from_name":"\"Your Name\"","to":[{"type":"to","email":"me@example.com","name":null},{"type":"cc","email":"test@example.com","name":null}],"headers":{"User-Agent":"Framework Mailer Transport","Date":"Wed, 26 Nov 2014 09:54:43 +0000","To":"eguvenc@gmail.com","Subject":"Email Test","Reply-To":"\"Your Name\" <test@example.com>"},"send_at":"Wed, 26 Nov 2014 09:54:43 +0000","attachments":[{"type":"image\/png","name":"buttons.png","fileurl":"\/var\/www\/framework\/assets\/images\/buttons.png"},{"type":"image\/png","name":"logo.png","fileurl":"\/var\/www\/framework\/assets\/images\/logo.png"}]}}}

```