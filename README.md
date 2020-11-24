# [Wordpress Encore](https://github.com/antiseptikk/wordpress-encore)

[![Packagist](https://img.shields.io/packagist/v/antiseptikk/wordpress-encore.svg)](https://packagist.org/packages/antiseptikk/wordpress-encore)

Simple and light script to handle and register Webpack Encore assets to WordPress. 

## Installation

## Usage

```PHP
$enqueue = new \Antiseptikk\Encore('build', '1.0.0', WP_HOME);
$enqueue->enqueue('blog', 'main', []);
```

## Contribution

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License

[MIT](https://choosealicense.com/licenses/mit/)
