
## Request Katmanı

> Uygulamaya gelen Http isteklerinin tümünü evrensel olarak filtrelemeyi sağlayan çekirdek katmandır.

### Konfigürasyon

Framework çekirdeğinde çalışan bir filtredir herhangi bir kurulum ve konfigürasyon gerektirmez fakat gelen istekleri doğru filtreleyebilmesi için önemlilik sırasına göre en en başta çalışması gerekir. Bu nedenle diğer katmanlardan önce yani <kbd>app/middlewares.php</kbd> dosyası içerisinde <b>en son satırda</b> tanımlanmış olmalıdır.

> **Not:** Http katmanlarında önemlilik sırası en yüksek olan katman en son tanımlanan katmandır.

```php
$c['app']->middleware('Http\Middlewares\Request');

/* End of file middlewares.php */
/* Location: .middlewares.php */
```

### Çalıştırma

<kbd>app/classes/Http/Request.php</kbd> dosyasını açın ve kullanmak istediğiniz <b>Trait</b> sınıflarını katmanınıza dahil edin.

```php
namespace Http\Middlewares;

use Obullo\Application\Middleware;
use Obullo\Authentication\AuthConfig;
use Obullo\Application\Middlewares\BenchmarkTrait;
use Obullo\Application\Middlewares\SanitizerTrait;

class Request extends Middleware
{
    use BenchmarkTrait;
    use SanitizerTrait;

    /**
     * User service
     * 
     * @var object
     */
    protected $user;

    /**
     * Top Level Constructor
     */
    public function __construct()
    {
        $this->user = $this->c->get(
            'user',
            [
                'table' => 'users'
            ]
        );
    }

    /**
     *  Call action
     * 
     * @return void
     */
    public function call()
    {
        $this->sanitize();
        
        $this->benchmarkStart();
        $this->next->call();
        $this->benchmarkEnd();

        $this->c['logger']->shutdown();
    }

}
```