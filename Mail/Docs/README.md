

## Mail Class

------

The Mail class package contains a mail sending functions that assist in working with Mail, Sendmail or SMTP protocols.

### Initializing the Class

------

```php
$c->load('service/mailer');
$this->mailer->method();
```

#### Mail Class supports the following features:

* Multiple Protocols: Mail, Sendmail, and SMTP
* Multiple recipients
* CC and BCCs
* HTML or Plaintext email
* Attachments
* Word wrapping
* Priorities
* BCC Batch Mode, enabling large email lists to be broken into small BCC batches.
* Email Debugging tools
* Sending Email

Sending email is not only simple, but you can configure it on the fly or set your preferences in a config file.

Here is a basic example demonstrating how you might send email. 

**Note:** This example assumes you are sending the email from one of your controllers.

```
<?php
$c->load('service/mailer');

$this->mailer->from('your@example.com', 'Your Name');
$this->mailer->to('someone@example.com'); 
$this->mailer->cc('another@another-example.com'); 
$this->mailer->bcc('them@their-example.com'); 
$this->mailer->subject('Email Test');
$this->mailer->message('Testing the email class.');	
$this->mailer->send();

echo $this->mailer->printDebugger();
```

### Setting Email Preferences

There are 17 different preferences available to tailor how your email messages are sent.

You can either set them manually as described here, or automatically via preferences stored in your config file, described below:

Preferences are set by passing an array of preference values to the email initialize function. Here is an example of how you might set some preferences:

```
<?php
/*
|--------------------------------------------------------------------------
| Mail Class Configuration
|--------------------------------------------------------------------------
| Configuration file
|
*/
return array(
    'useragent' => 'Obullo', //  The "user agent".
    'mailpath'  => '/usr/sbin/sendmail',  //  The server path to Sendmail.
    
    'smtpHost' =>  'smtp.mandrillapp.com',  // SMTP Server Address.
    'smtpUser' => 'example@example.com',   // SMTP Username.
    'smtpPass' => '123456',   // SMTP Password.
    'smtpPort' => '587', // SMTP Port.
    'smtpTimeout' => '5' , // SMTP Timeout (in seconds).

    'wordwrap' => true, // "true" or "false" (boolean) Enable word-wrap.
    'wrapchars' => 76,   // Character count to wrap at.
    'mailtype' => 'html', // text
                             .
    'charset' => 'utf-8',
    'validate' => false,  
    'priority' =>  3,
    'crlf'  => "\n",
    'newline' =>  "\n",
    'bccBatchMode' =>  false,
    'bccBatchSize' => 200
);

/* End of file mail.php */
/* Location: .app/env/local/mail.php */
```

### Setting Preferences Manually

```php
<?php
$config['protocol'] = 'sendmail';
$config['mailpath'] = '/usr/sbin/sendmail';
$config['charset'] = 'iso-8859-1';
$config['wordwrap'] = true;

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


### Function Reference

------

#### $this->mailer->from()

Sets the email address and name of the person sending the email:

```
<?php
$this->mailer->from('you@example.com', 'Your Name');
```

#### $this->mailer->replyTo()

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

```php
<?php
foreach ($list as $name => $address) {
    $this->mailer->clear();
    $this->mailer->to($address);
    $this->mailer->from('your@example.com');
    $this->mailer->subject('Here is your info '.$name);
    $this->mailer->message('Hi '.$name.' Here is the info you requested.');
    $this->mailer->send();
}
```

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

Returns a string containing any server messages, the email headers, and the email messsage. Useful for debugging.

#### Overriding Word Wrapping

If you have word wrapping enabled (recommended to comply with RFC 822) and you have a very long link in your email it can get wrapped too, causing it to become un-clickable by the person receiving it. CodeIgniter lets you manually override word wrapping within part of your message like this:

The text of your email that
gets wrapped normally.

```
{unwrap}http://example.com/a_long_link_that_should_not_be_wrapped.html{/unwrap}
```

More text that will be
wrapped normally.

Place the item you do not want word-wrapped between: {unwrap} {/unwrap}