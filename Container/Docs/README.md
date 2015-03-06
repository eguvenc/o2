
## Container Class

------

A <b>DIC</b> or service container is responsible for creating and storing services. It can recursively create dependencies of the requested services and inject them.

If you are new to service containers or Dependency Injection, it would be a good idea to read up on the concept. If you are new to Pimple, reading up on it is going to be extremely important. <a href="http://pimple.sensiolabs.org/" target="_blank">Pimple's documentation</a> is pretty sparse but dense.

**Note:** <kbd>$c</kbd> variable is declared by the system as default. ( At top of Application/Obullo.php ).


### Function Reference

------

#### $c['class'];

#### $c['namespace/class'];

#### $c['service'];

#### $c['service provider class']->get(array $params);

#### $c->exists();

Paketin konteyner içerisine kayıtlı olup olmadığını kontrol eder.

#### $c->frozen();

Paketin daha önceden çağrılıp çağrılmadığını kontrol eder.

#### $c->raw();

#### $c->extend();

#### $c->register();