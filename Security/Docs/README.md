

## Csrf Katmanı

```php
$this->match(['get', 'post'], 'widgets/tutorials/hello_form')->middleware('Csrf');
```

#### İstisnai Durumlarda Csrf Katmanını Kapatmak

Csrf katmanı global olarak tanımlandığında tüm http POST isteklerinde çalışır.

```php
/**
 * Index
 *
 * @middleware->remove("Csrf");
 * 
 * @return void
 */
public function index()
{
    $this->view->load(
        'welcome',
        [
            'title' => 'Welcome to Obullo !',
        ]
    );
}
```

http://shiflett.org/articles/cross-site-request-forgeries

```html
<form action="buy.php" method="post">
<input type="hidden" name="<?php echo $this->c['csrf']->getTokenName() ?>" value="<?php echo $this->c['csrf']->getToken(); ?>" />
<p>
Symbol: <input type="text" name="symbol" /><br />
Shares: <input type="text" name="shares" /><br />
<input type="submit" value="Buy" />
</p>
</form>
```