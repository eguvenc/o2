
## Uid Class

------

The Uid class generates strong <b>unique</b> ids. You can produce results in numbers or string format. To get more strong unique results is possible with identify methods.

### Initializing the Class

------

```php
<?php
$c->load('user/uid as uid');
$this->uid->method();
```

### Generating Random Numbers


```php
<?php
echo $this->uid->generateNumbers();

// gives  147417756
```

### Generating Random Id with More Entropy

```php
<?php
echo $this->uid->generateNumbers(true);

// gives  3464605246
```

### Generating Random Strings

```php
<?php
echo $this->uid->addIp()->generateString();

// gives 2130706433-1905901887
```

### Generating Strong Random Strings

```php
<?php
echo $this->uid->addHostname()->addIp()->addMacAddress()->generateString();  

// gives  i:2130706433m:{bc:ae:c5:39:10:44}obullo-desktop4213360135
```

### Adding Separator

```php
<?php
echo $this->uid->separator('-')->addIp()->addAgent()->addMacAddress()->addHostname()->generateString();  

// gives i:2130706433-a:0d70b90143a29ddeae4366d34e210758-m:{bc:ae:c5:39:10:44}-3310274859

```

### Generating Strong Random Numbers

```php
<?php
echo $this->uid->separator('-')->addId('username')->addHostname()->addIp()->addMacAddress()->generateNumbers();

// gives i:270565034-a:1353700457-m:3188168158-4164401261
```

### Function Reference

------

#### $this->uid->separator(string $sign);

Add a sign foreach items.

#### $this->uid->addId(string $identifier);

Add an identifier prefix to your algorithm. ( Username, email any string that you want ) 

#### $this->uid->addMacAddress();

Add locale machine mac address to your algorithm.

#### $this->uid->addIp();

Add user ip using ip2long() function.

#### $this->uid->addAgent();

Add user agent using md5() hash format.

#### $this->uid->addHostname();

Add locale machine hostname to your algorithm.

#### $this->uid->generateNumbers();

Generate random unique "numbers".

#### $this->uid->generateString();

Generate random unique "string".