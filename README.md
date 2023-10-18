# App

Autoload de funções anônimas

## Instalação

```bash
composer require gaucho/app
```

## Como usar

```php
<?php
require 'vendor/autoload.php';

use gaucho\app;

$app=new app(__DIR__.'/src);
$app->nomeDaFuncao();
```
