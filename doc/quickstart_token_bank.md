
##QUICKSTART BANCO CON TOKEN

A lo largo de este tutorial te enseñaremos como sincronizar una institución bancaria con token, es decir, aquella que además de requerir una autenticación (usuario y contraseña) solicita un token, ejemplos de estas instituciones pueden ser Banorte o BBVA Bancomer. En el tutorial asumiremos que ya hemos creado usuarios y por tanto tenemos usuarios ligados a nuestra API KEY, también asumiremos que hemos instalado la librería y hecho las configuraciones pertinentes. Si tienes dudas acerca de esto te recomendamos que antes de tomar este tutorial consultes el [Quickstart para sincronizar al SAT](https://github.com/Paybook/sync-php/blob/master/doc/quickstart_sat.md) ya que aquí se abordan los temas de creación de usuarios y sesiones.  

### Requerimientos

1. Haber consultado el tutorial [Quickstart SAT](https://github.com/Paybook/sync-php/blob/master/doc/quickstart_sat.md)
2. Tener credenciales de alguna institución bancaria del catálogo de Paybook que solicite token


##Ejecución:

Este tutorial está basado en el script [quickstart_token_bank.php](https://github.com/Paybook/sync-php/blob/master/doc/quickstart_token_bank.php) por lo que puedes descargar el archivo, configurar los valores YOUR_API_KEY, YOUR_BANK_USERNAME y YOUR_BANK_PASSWORD y ejecutarlo en tu equipo. Es recomendable tener el token a la mano puesto que el script eventualmente lo solicitará:

```
$ php quickstart_normal_bank.php
```

Una vez que has ejecutado el archivo podemos continuar analizando el código.

####1. Obtenemos un usuario e iniciamos sesión:
El primer paso para realizar la mayoría de las acciones en Paybook es tener un usuario e iniciar una sesión, por lo tanto haremos una consulta de nuestra lista de usuarios y seleccionaremos el usuario con el que deseamos trabajar. Una vez que tenemos al usuario iniciamos sesión con éste.


```php
$my_users = paybook\User::get();
$user = null;
foreach ($my_users as $index => $my_user) {
    if ($my_user->name == $USERNAME) {
        _print('User '.$USERNAME.' already exists');
        $user = $my_user;
    }//End of if
}//End of foreach
if ($user == null) {
    _print('Creating user '.$USERNAME);
    $user = new paybook\User($USERNAME);
}//End of if
$session = new paybook\Session($user);
_print('Token: '.$session->token);
```

####2. Consultamos el catálogo de las instituciones de Paybook:
Recordemos que Paybook tiene un catálogo de instituciones que podemos seleccionar para sincronizar nuestros usuarios. A continuación consultaremos este catálogo:

```php
$sites = paybook\Catalogues::get_sites($session);
$bank_site = null;
_print('Sites list:');
foreach ($sites as $index => $site) {
    _print($site->name);
    if ($site->name == $BANK_SITE) {
        $bank_site = $site;
    }//End of if
}//End of foreach
_print('Bank site: '.$bank_site->id_site.' '.$bank_site->name);
```

El catálogo muestra las siguienes instituciones:

1. AfirmeNet
2. Personal
3. BancaNet Personal
4. eBanRegio
5. Banorte Personal
6. CIEC
7. Banorte en su empresa
8. BancaNet Empresarial
9. Banca Personal
10. Corporativo
11. Banco Azteca
12. American Express México
13. SuperNET Particulares
14. ScotiaWeb
15. Empresas
16. InbuRed

Usted puede seleccionar cualquier institución y guardarla en la variable $bank_site.

####3. Registramos las credenciales:

A continuación registraremos las credenciales de nuestro banco, es decir, el usuario y contraseña que nos proporcionó el banco para acceder a sus servicios en línea:

```php
$credentials_params = [
    'username' => $BANK_USERNAME,
    'password' => $BANK_PASSWORD,
];//End of credentials_params
_print('Creating credentials of '.$bank_site->name);
$bank_credentials = new paybook\Credentials($session, null, $bank_site->id_site, $credentials_params);
```
####4. Checamos el estatus

Una vez que has registrado las credenciales de una institución bancaria para un usuario en Paybook el siguiente paso consiste en checar el estatus de las credenciales, el estatus será una lista con los diferentes estados por los que las credenciales han pasado, el último será el estado actual. A continuación se describen los diferentes estados de las credenciales:

## Códigos:

| Código         | Descripción                                |                                
| -------------- | ---------------------------------------- | ------------------------------------ |
| 100 | Credenciales registradas   | 
| 101 | Validando credenciales  | 
| 401      | Credenciales inválidas    |
| 410      | Esperando token   |
| 102      | La institución se está sincronizando    |
| 200      | La institución ha sido sincronizada    | 

## Maquina de estados exitosos:
El
![Success](https://github.com/Paybook/sync-py/blob/master/token.png "Success")

## Maquina de estados de error:

![Errores](https://github.com/Paybook/sync-py/blob/master/error.png "Errores")

**Importante** El código 401 se puede presentar múltiples veces en caso de que la autenticación con la institución bancaria requiera múltiples pasos e.g. usuario, contraseña (primera autenticación) y además token (segunda autenticación). Entonces al código 401 únicamente le puede preceder un código 100 (después de introducir usuario y password), o bien, un código 401 (después de haber introducido un token). El código 405 indica que el acceso a la cuenta bancaria está bloqueado y, por otro lado, el código 406 indica que hay una sesión iniciada en esa cuenta por lo que Paybook no puede conectarse.

Checamos el estatus de las credenciales:

```php
$status = $bank_credentials->get_status($session);
```
####5. Analizamos el estatus:

El estatus se muestra a continuación:

```
[{u'code': 100}, {u'code': 101}]
```

Esto quiere decir que las credenciales han sido registradas y se están validando. La institución bancaria a sincronizar i.e. Banorte, requiere de token por lo que debemos esperar un estatus 410, para esto podemos polear mediante un bucle sobre los estados de las credenciales hasta que se tenga un estatus 410, es decir, que el token sea solicitado:

```php
_print('Wating for token request ... ');
$status_410 = false;
while (!$status_410) {
    sleep(5);
    $status = $bank_credentials->get_status($session);
    print_status($status);
    foreach ($status as $index => $each_status) {
        $code = $each_status['code'];
        if ($code == 410) {
            $status_410 = true;
        } elseif ($code >= 400 && $code <= 411) {
            _print('There was an error with your credentials with code: '.strval($code).'.');
            _print('Check the code status in https://www.paybook.com/sync/docs'.PHP_EOL.PHP_EOL);
            exit();
        }//End of if
    }//End of foreach
}//End of while 
```

**Importante:** En este paso también se debe contemplar que en vez de un código 410 (esperando token) se puede obtener un código 401 (credenciales inválidas) lo que implica que se deben registrar las credenciales correctas, por lo que el bucle se puede módificar para agregar esta lógica.

####6. Enviar token bancario
Ahora hay que ingresar el valor del token, el cual lo podemos solicitar en PHP a través de la interfaz readline:

```php
$token_value = readline('Bank token: ');
$bank_credentials->set_twofa($session, null, $token_value);
$bank_sync_completed = false;
```

Una vez que el token bancario es enviado, volvemos a polear por medio de un bucle buscando que el estatus sea 102, es decir, que el token haya sido validado y ahora Paybook se encuentre sincronzando a nuestra institución bancaria, o bien, buscando el estatus 401, es decir, que el token no haya sido validado y por tanto lo tengamos que volver a enviar:

```php
_print('Wating for token sync to be done ... ');
while (!$bank_sync_completed) {
    sleep(5);
    $status = $bank_credentials->get_status($session);
    print_status($status);
    foreach ($status as $index => $each_status) {
        $code = $each_status['code'];
        if ($code >= 200 && $code <= 205) {
            $bank_sync_completed = true;
        } elseif (($code >= 400 && $code <= 411) && $code != 410) {
            _print('There was an error with your credentials with code: '.strval($code).'.');
            _print('Check the code status in https://www.paybook.com/sync/docs'.PHP_EOL.PHP_EOL);
            exit();
        }//End of if
    }//End of foreach
}//End of while 
```

Es importante checar el código 401 que indica que el token introducido es incorrecto, por lo que se puede programar una rutina para pedir el token nuevamente o terminar el script:

```php
if (($code >= 400 && $code <= 411) && $code != 410) {
    _print('There was an error with your credentials with code: '.strval($code).'.');
    _print('Check the code status in https://www.paybook.com/sync/docs'.PHP_EOL.PHP_EOL);
    exit();
}//End of if	
```

En caso de que el estatus sea 102 se evitará la validación previa y podremos continuar con los siguientes pasos.

####7. Esperamos a que la sincronización termine

Una vez que la sincronización se encuentra en proceso (código 102), podemos construir un bucle para polear y esperar por el estatus de fin de sincronización (código 200).

```php
_print('Wating for token sync to be done ... ');
while (!$bank_sync_completed) {
    sleep(5);
    $status = $bank_credentials->get_status($session);
    print_status($status);
    foreach ($status as $index => $each_status) {
        $code = $each_status['code'];
        if ($code >= 200 && $code <= 205) {
            $bank_sync_completed = true;
        } elseif (($code >= 400 && $code <= 411) && $code != 410) {
            _print('There was an error with your credentials with code: '.strval($code).'.');
            _print('Check the code status in https://www.paybook.com/sync/docs'.PHP_EOL.PHP_EOL);
            exit();
        }//End of if
    }//End of foreach
}//End of while 
```

####8. Consultamos las transacciones de la institución bancaria:

Una vez que la sincronización ha terminado podemos consultar las transacciones:

```php
$options = [
    'id_credential' => $bank_credentials->id_credential,
];//End of $options
$bank_transactions = paybook\Transaction::get($session, null, $options);
_print('Bank transactions: '.strval(count($bank_transactions)));
```

¡Felicidades! has terminado con este tutorial.

###Siguientes Pasos

- Puedes consultar y analizar la documentación completa de la librería [aquí](https://github.com/Paybook/sync-php/blob/master/README.md)

- Puedes consultar y analizar la documentación del API REST [aquí](https://www.paybook.com/sync/docs)

- Acceder a nuestro proyecto en Github y checar todos los recursos que Paybook tiene para ti [aquí](https://github.com/Paybook)














