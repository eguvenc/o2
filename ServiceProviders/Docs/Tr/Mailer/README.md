
## Mailer Service Provider

------

Kullanım Örneği

```php
$this->mailer = $this->c['service provider mailer']->get(['driver' => 'mandrill']);
```

Birkez yüklendikten sonra mailer metodlarına erişebilirsiniz.

```php
$this->mailer->from('admin@example.com');
$this->mailer->to('me@me.com');
$this->mailer->subject('Test');
$this->mailer->message('Hello_World !');
$this->mailer->send();
```

> Dikkat etmeniz gereken nokta sürücülerin hepsinin app/config/$env/mailer.php dosyasında önceden tanımlı olmasıdır.


```php

return array(

    'drivers' => array(
        'queue' => '\\Obullo\Mailer\Transport\Queue',
        'mail' => '\\Obullo\Mailer\Send\Protocol\Mail',
        'sendmail' => '\\Obullo\Mailer\Send\Protocol\Sendmail',
        'smtp' => '\\Obullo\Mailer\Send\Protocol\Smtp',
        'mandrill' => '\\Obullo\Mailer\Transport\Mandrill',
        'yourmailer' => '\\Mailer\Transport\YourMailer'
    ),
   
/* End of file mailer.php */
/* Location: .app/config/env/local/mailer.php */ 
```

#### Queue opsiyonu

```php
$this->mailer = $this->c['service provider mailer']->get(
	[
		'driver' => 'mandrill',
		'options' => array('queue' => true)
	]
);
``
Queue opsiyonu true olduğunda servis sağlayıcı mandrill drivier parametresi ile email leri iş kuyruğuna gönderir.


```php
$this->mailer = $this->c['service provider mailer']->get(
	[
		'driver' => 'smtp',
		'options' => array('queue' => true)
	]
);

$this->mailer->from('admin@example.com');
$this->mailer->to('me@me.com');
$this->mailer->subject('Test');
$this->mailer->message('Hello_World !');
$this->mailer->send();
```