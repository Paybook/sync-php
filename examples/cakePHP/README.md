# Ejemplo CakePHP 

Este es un ejemplo de como implementar la librería de Paybook para PHP a través de [Cake PHP](http://book.cakephp.org/3.0/en/intro.html).

Es importante mencionar que este ejemplo está basado en una modificación de [Bookmarker Tutorial](http://book.cakephp.org/3.0/en/tutorials-and-examples/bookmarks/intro.html) que expone CagePHP en su documentación. Por lo que el desarrollador puede optar por consultar este tutorial a manera opcional. 

Las modifiaciones hechas al proyecto original fueron las siguientes:

1. Se modificó el archivo composer del proyecto para agregar la librería de Paybook.

2. Se modificó la página de /home del proyecto para que mostrará datos obtenidos haciendo uso del API de Paybook por medio de la librería de PHP.


## Installation

### Clonar el contenido

Debes clonar el contenido de la carpeta CagePHP a tu computadora en el directoro de tu elección.

### Instalar dependencias

Dentro del directorio creado en el paso previo ejecutar:

```
$ composer install
```

Esto instalará todas las dependencias que la aplicación requiere además de instalar la librería de paybook. Es importante mencionar que para instalar la librería de Paybook se tuvo que modificar el archivo composer.json original del proyecto:

```
"require": {
        "php": ">=5.5.9",
        "cakephp/cakephp": "3.3.*",
        "mobiledetect/mobiledetectlib": "2.*",
        "cakephp/migrations": "~1.0",
        "cakephp/plugin-installer": "*",
        "paybook/paybook": "dev-master"
}
```

Aquí se puede ver como se agrega la dependencia de Paybook por medio de la línea "paybook/paybook": "dev-master".

### Configurar API Key

Modificar el archivo /src/Template/Pages/home.ctp en la línea 22 y agrega el valor de tu API key de Paybook:

```php
$PAYBOOK_API_KEY = 'YOUR_API_KEY';
```

## Ejecución

Una vez que el proyecto ha sido instalado únicamente hay que levantar la aplicación. Dentro del directorio de la aplicación ejectuar:

```
$ bin/cake server
```

La aplicación debe iniciar y se mostrará lo siguiente:

```
Welcome to CakePHP v3.3.3 Console
---------------------------------------------------------------
App : src
Path: /Users/hugo/Documents/paybook/dev/libraries/sync-php/examples/cagePHP/src/
DocumentRoot: /Users/hugo/Documents/paybook/dev/libraries/sync-php/examples/cagePHP/webroot
---------------------------------------------------------------
built-in server is running in http://localhost:8765/
You can exit with `CTRL-C`
```

## Aplicación

Por medio del navegador puedes acceder a la aplicación en:

```
http://localhost:8765
```

Se verá una pantalla como la siguiente:

![Instituciones](https://github.com/Paybook/sync-py/blob/master/sites.png "Instituciones")

En caso de no haber configurado tu API key o que esta sea incorrecta verás una pantalla como la siguiente:

![Instituciones](https://github.com/Paybook/sync-py/blob/master/sites.png "Instituciones")

Felicidades, has terminado con este tutorial :)













