
##QUICKSTART SAT

### Requerimientos

1. Manejo de shell/bash
2. Algunas credenciales de acceso al SAT (RFC y CIEC)
3. [PHP](http://php.net/)
4. [Composer](https://getcomposer.org/doc/00-intro.md)

### Introducción

A lo largo de este tutorial te enseñaremos como consumir el API Rest de Paybook por medio de la librería de Paybook. Al terminar este tutorial habrás podido crear nuevos usuarios en Paybook, sincronizar algunas instituciones de estos usuarios y visualizar las transacciones sincronizadas.

La documentación completa de la librería la puedes consultar [aquí](https://github.com/Paybook/sync-php/blob/master/README.md) 

##En la consola:

####1. Instalamos la librería de Paybook y dependencias:

Para consumir el API de Paybook lo primero que tenemos que hacer es instalar la libreria de Paybook haciendo uso del paquete de instalaciones:

```
$ composer install
```

Esto requiere que tu proyecto contenga un archivo composer.json con el siguiente contenido: 

```
{
    "require": {
        "paybook/paybook": "dev-master"
    }
}
```

####2. Ejecutamos el Script:
Este tutorial está basado en el script [quickstart_sat.php](https://github.com/Paybook/sync-php/blob/master/doc/quickstart_sat.php) por lo que puedes descargar el archivo, configurar los valores YOUR_API_KEY, YOUR_RFC y YOUR_CIEC y ejecutarlo en tu equipo:

```
$ php quickstart_sat.php
```

A continuación explicaremos detalladamente la lógica del script que acabas de ejecutar.

####3. Importamos paybook
El primer paso es importar la librería y algunas dependencias:

```php
require __DIR__.'/vendor/autoload.php';
```

####4. Configuramos la librería
Una vez importada la librería tenemos que configurarla, para esto únicamente se necesita tu API KEY de Paybook.

```php
paybook\Paybook::init($YOUR_API_KEY);
```

####5. Creamos un usuario:
Una vez configurada la librería, el primer paso será crear un usuario, este usuario será, por ejemplo, aquel del cual queremos obtener sus facturas del SAT.

**Importante**: todo usuario estará ligado al API KEY con el que configuraste la librería (paso 4)

```php
_print('Creating user '.$USERNAME);
$user = new paybook\User($USERNAME);
```

####6. Consultamos los usuarios ligados a nuestra API KEY:
Para verificar que el usuario creado en el paso 5 se haya creado corréctamente podemos consultar la lista de usuarios ligados a nuestra API KEY.

```php
$my_users = paybook\User::get();
$user = null;
foreach ($my_users as $index => $my_user) {
    if ($my_user->name == $USERNAME) {
        _print('User '.$USERNAME.' already exists');
        $user = $my_user;
    }//End of if
}//End of foreach
```

####7. Creamos una nueva sesión:
Para sincronizar las facturas del SAT primero tenemos que crear una sesión, la sesión estará ligada al usuario y tiene un proceso de expiración de 5 minutos después de que ésta ha estado inactiva. Para crear una sesión:

```php
$session = new paybook\Session($user);
_print('Token: '.$session->token);
```

####8. Podemos validar la sesión creada:
De manera opcional podemos validar la sesión, es decir, checar que no haya expirado.

```php
$session_verified = $session->verify();
_print('Session verfied: '.strval($session_verified));
```

####9. Consultamos el catálogo de instituciones que podemos sincronizar y extraemos el SAT:
Paybook tiene un catálogo de instituciones que podemos sincronizar por usuario:

![Instituciones](https://github.com/Paybook/sync-py/blob/master/sites.png "Instituciones")

A continuación consultaremos este catálogo y seleccionaremos el sitio del SAT para sincronizar las facturas del usuario que hemos creado en el paso 5:

```php
_print('Session verfied: '.strval($session_verified));
$sites = paybook\Catalogues::get_sites($session);
$sat_site = null;
foreach ($sites as $index => $site) {
    if ($site->name == $SAT_SITE) {
        $sat_site = $site;
    }//End of if
}//End of foreach
_print('SAT site: '.$sat_site->id_site.' '.$sat_site->name);
```

####10. Configuramos nuestras credenciales del SAT:
Una vez que hemos obtenido el sitio del SAT del catálogo de institiciones, configuraremos las credenciales de nuestro usuario (estas credenciales son las que el usuario utiliza para acceder al portal del SAT).

```php
$credentials_params = [
    'rfc' => $RFC,
    'password' => $CIEC,
];//End of credentials_params
$sat_credentials = new paybook\Credentials($session, null, $sat_site->id_site, $credentials_params);
_print('Credentials username: '.$sat_credentials->username);
```

####11. Checamos el estatus de sincronización de las credenciales creadas y esperamos a que la sincronización finalice:
Cada vez que registamos unas credenciales Paybook inicia un Job (proceso) que se encargará de validar esas credenciales y posteriormente sincronizar las transacciones. Este Job se puede representar como una maquina de estados:

![Job Estatus](https://github.com/Paybook/sync-py/blob/master/normal.png "Job Estatus")

Una vez registradas las credenciales se obtiene el primer estado (Código 100), posteriormente una vez que el Job ha empezado se obtiene el segundo estado (Código 101). Después de aquí, en caso de que las credenciales sean válidas, prosiguen los estados 202, 201 o 200. Estos indican que la sincronización está en proceso (código 201), que no se encontraron transacciones (código 202), o bien, la sincronización ha terminado (código 200). La librería proporciona un método para consultar el estado actual del Job. Este método se puede ejecutar constantemente hasta que se obtenga el estado requerido por el usuario, para este ejemplo especifico consultamos el estado hasta que se obtenga un código 200, es decir, que la sincronización haya terminado:

```php
$sat_sync_completed = false;
while (!$sat_sync_completed) {
    sleep(5);
    $status = $sat_credentials->get_status($session);
    print_status($status);
    foreach ($status as $index => $each_status) {
        $code = $each_status['code'];
        if ($code >= 200 && $code <= 205) {
            $sat_sync_completed = true;
        } elseif ($code >= 400 && $code <= 405) {
            _print('There was an error with your credentials with code: '.strval($code).'.');
            _print('Please check your credentials and run this script again'.PHP_EOL.PHP_EOL);
            exit();
        }//End of if
    }//End of foreach
}//End of while 
```

####12. Consultamos las facturas sincronizadas:
Una vez que ya hemos checado el estado de la sincronización y hemos verificado que ha terminado (código 200) podemos consultar las facturas sincronizadas:
```php
$options = [
    'id_credential' => $sat_credentials->id_credential,
];//End of $options
$sat_transactions = paybook\Transaction::get($session, null, $options);
_print('SAT transactions: '.strval(count($sat_transactions)));
```

####13. Consultamos la información de archivos adjuntos:
Podemos también consultar los archivos adjuntos a estas facturas, recordemos que por cada factura el SAT tiene una archivo XML y un archivo PDF:
```php
$sat_attachments = paybook\Attachment::get($session, null, null, $options);
_print('SAT attachments: '.strval(count($sat_attachments)));
```

####14. Obtenemos el XML y PDF de alguna factura:
Podemos descargar estos archivos:
```php
if (count($sat_attachments) > 0) {
    _print('Getting a SAT attachment');
    $url = $sat_attachments[0]->url;
    $id_attachment = substr($url, 1, strlen($url));
    $attachment = paybook\Attachment::get($session, null, $id_attachment);
    print_r($attachment);
}//End of if
```

¡Felicidades! has terminado con este tutorial. 

### Siguientes Pasos

- Revisar el tutorial de como sincronizar una institución bancaria con credenciales simples (usuario y contraseña) [aquí](https://github.com/Paybook/sync-py/blob/master/quickstart_normal_bank.md)

- Revisar el tutorial de como sincronizar una institución bancaria con token [aquí](https://github.com/Paybook/sync-py/blob/master/quickstart_token_bank.md)

- Puedes consultar y analizar la documentación completa de la librería [aquí](https://github.com/Paybook/sync-py/blob/master/readme.md)

- Puedes consultar y analizar la documentación del API REST [aquí](https://www.paybook.com/sync/docs#api-Overview)

- Acceder a nuestro proyecto en Github y checar todos los recursos que Paybook tiene para ti [aquí](https://github.com/Paybook)


























