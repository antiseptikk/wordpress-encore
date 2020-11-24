# [Wordpress Encore](https://github.com/antiseptikk/wordpress-encore)

[![Packagist](https://img.shields.io/packagist/v/antiseptikk/wordpress-encore.svg)](https://packagist.org/packages/antiseptikk/wordpress-encore)

Simple and light script to handle and register Webpack Encore assets to WordPress. 

## Installation

### Using Composer

```bash
composer require antiseptikk/wordpress-encore
```

Then in your `functions.php` file of your theme, load composer auto-loader.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$encore = new \Antiseptikk\Encore('build', '1.0.0', 'my-website.com');
```

#### Using [WPress composer-skeleton](https://github.com/agencearcange/wordpress-composer-skeleton)

We recommend using [WPress composer-skeleton](https://github.com/agencearcange/wordpress-composer-skeleton)  for using this library, 
you can use config constants like `WP_HOME`

```php
<?php

$encore = new \Antiseptikk\Encore('build', '1.0.0', WP_HOME);
```

## Example

```PHP
<?php

use Antiseptikk\Encore;

class Bootstrap
{
    private $encore;   

    public function __constuct()
    {
        $this->encore = new Encore('build', '1.0.0', WP_HOME);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets()
    {
        $this->encore->enqueue('app', 'main', []);
    }
}
```

## Contribution

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License

[MIT](https://choosealicense.com/licenses/mit/)
