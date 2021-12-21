# Waffler for Laravel

This package is a fancy wrapper for the Laravel Framework. You must know
the basics of the [waffler/waffler](https://github.com/waffler-io/waffler)
and [guzzlehttp/guzzle](https://github.com/guzzle/guzzle) packages.

## How to use
This package exposes a waffler.php config file where you'll register your
client interfaces into the application [service container](https://laravel.com/docs/8.x/container).
The config file is pretty much self-explanatory, you'll populate the 'clients'
array with you client interfaces and guzzle http configurations.

## Installation

```shell
$ composer require waffler/waffler-laravel
```