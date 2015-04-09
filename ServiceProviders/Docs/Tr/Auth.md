
## Auth Service Provider

------

Facyory ile auth nesnesi yaratmak.


$auth = $c['service provider auth']->factory(
    [
        'url.login'        => '/membership/login',
        'cache.key'        => 'Auth',
        'db.adapter'       => '\Obullo\Authentication\Adapter\Database', // Adapter
        'db.model'         => '\Obullo\Authentication\Model\User', // User model, you can replace it with your own.
        'db.provider'      => 'database',
        'db.connection'    => 'default',
        'db.tablename'     => 'users', // Database column settings
        'db.id'            => 'id',
        'db.identifier'    => 'username',
        'db.password'      => 'password',
        'db.rememberToken' => 'remember_token',
    ]
);

$auth->login->attempt($credentials);