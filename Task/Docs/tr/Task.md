
## Task

------

Task paketi komut satırından yürütülen işlemlerini <kbd>modules/tasks</kbd> klasörü içerisinde yaratılmış olan kontrolör dosyalarına istek göndererek yürütür. Framework konsol arayüzü projenizin ana dizinindeki **task** dosyası üzerinden çalışır.

Bir task kontrolör dosyasının normal bir kontrolör dosyasından hiçbir farkı yoktur sadece dosyanın üzerinde <b>namespace</b> olarak <b>Tasks</b> belirtilmek zorundadır.

```php
namespace Tasks;

use Controller;

class Hello extends Controller {
  
    /**
     * Index
     * 
     * @return void
     */
    public function index($variable = null)
    {
        echo 'Hello ';

        echo $variable;
    }
}

/* End of file hello.php */
/* Location: .modules/tasks/Hello.php */
```

```php
php task hello index World   // Hello World
```

Daha detaylı bilgi için [Cli.md](/Cli/Docs/tr/Cli.md) dosyasını inceleyiniz.