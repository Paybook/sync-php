<center>

![Sync-Paybook-logo][logo]

</center>

# SYNC-PHP

**Sync** recupera información de las cuentas y sus transacciones, de instituciones financieras autorizados por el usuario, y lo regresa a soluciones de terceros en un formato organizado, muy fácil de utilizar.

## Tabla de contenidos

- [Instalación](#instalación)   
- [Requerimientos](#requerimientos)    
- [Modelo de información](#modelo-de-información)    
- [Implementación](#implementación)    
  - [La librería](#la-librería)
  - [Implementación de un Webhook ](#implementación-de-un-Webhook )
  - [Sync Widget](#sync-widget)
- [Autenticación](#autenticación)        
  - [Obtener un Token de sesión:](#obtener-un-token-de-sesión)        
  - [Flujo de Información](#flujo-de-información) 
- [Recursos y Ejemplos](#recursos-y-ejemplos)        
  - [Usuarios](#usuarios)            
    - [Consultar usuarios](#consultar-usuarios) 
    - [Consultar un usuario en especifico](#consultar-un-usuario-en-especifico)            
    - [Crear un Usuario](#crear-un-usuario)            
    - [Actualizar un Usuario](#actualizar-un-usuario)            
    - [Eliminar un usuario](#eliminar-un-usuario)        
  - [Catálogos](#catálogos)            
    - [Catálogos de Instituciones](#catálogos-de-instituciones)        
  - [Credenciales](#credenciales)            
    - [Credenciales normales](#credenciales-normales) 
    - [Crear credenciales TWOFA](#credenciales-twofa)
    - [Consultar credenciales](#consultar-credenciales)          
    - [Eliminar una credencial](#eliminar-una-credencial)            
    - [Consultar historial de cambios de la credencial](#consultar-historial-de-cambios-de-la-credencial)        
  - [Cuentas](#cuentas)            
    - [Consulta las cuentas de un usuario específico](#consulta-las-cuentas-de-un-usuario-específico)        
  - [Transacciones](#transacciones)            
    - [Consulta las transacciones de un usuario específico](#consulta-las-transacciones-de-un-usuario-específico)            
    - [Consulta el número de transacciones dados algunos parámetros de búsqueda](#consulta-el-número-de-transacciones-dados-algunos-parámetros-de-búsqueda)        
  - [Webhooks](#webhooks)            
    - [Crear Webhook](#crear-webhook)            
    - [Consultar Webhooks](#consultar-webhooks)            
    - [Eliminar Webhook](#eliminar-webhook)        
  - [Archivos adjuntos (Attachments)](#archivos-adjuntos-attachments)            
    - [Consulta los archivos adjuntos de un usuario específico](#consulta-los-archivos-adjuntos-de-un-usuario-específico)            
    - [Regresa el archivo adjunto](#regresa-el-archivo-adjunto)            
    - [Regresa la información extraída del archivo adjunto](#regresa-la-información-extraída-del-archivo-adjunto)    
- [Entorno](#entorno)    
- [Enlaces de interes](#enlaces-de-interes)    
- [Comentarios y aportes](#comentarios-y-aportes)

## Instalación

1. Instalar mediante composer
```bash
  composer require paybook/sync-php
```
## Requerimientos

1. **API Key:**

  Cuando creamos una cuenta de [Paybook Sync] se nos proporcionó una API Key, por ejemplo:
  ```
  api_key = 7767a4a04f990e9231bafc949e8ca08a
  ```
 Al crear tu cuenta se te proporcionan dos API Keys: ***Producción*** & ***Sandbox***. Las cuales tienen el mismo proposito, ambas nos permiten implementar **Paybook Sync**, pero ***Sandbox*** nos permite hacerlo sin la necesidad de tener credenciales reales de bancos o el SAT.
  
  Este API key lo podemos visualizar como la contraseña o llave de acceso a los servicios de [Paybook Sync]. Solamente a través de ella podremos empezar a interactuar con las instituciones que sincronicemos.

  > ***Nota:*** Pasar de una API key a otra es tan sensillo como escribir la nueva API key correspondiente.

2.**Webhook**

_Opcional_ pero altamente recomendable para lograr una integración completa de las funcionalidades de Paybook Sync.

## Modelo de información

El modelo de información de Paybook Sync es bastante sencillo.
Prácticamente podemos visualizar al API key como el elemento raíz del modelo, del cual se desglosan los usuarios, pero entendamos como usuarios a aquellas entidades (personas o empresas) a las cuales se les desea sincronizar las instituciones financieras que el servicio de Paybook Sync facilita, podemos apreciarlo gráficamente en este diagrama:

![sync-model-image][sync-model-image]

> Hay que mencionar la diferencia entre un usuario de Paybook Sync (usted, el developer) y un usuario del API key: Por cada usuario de Paybook Sync, hay un API key y cada API key puede tener N usuarios.


### Users ([Usuarios](#usuarios))
Los usuarios son segmentaciones lógicas para los usuarios finales. Una mejor práctica es registrar usuarios para tener su información agrupada y control en ambos extremos. 

### Catalogs/Sites ([Catálogos](#catálogos))
Los catálogos son colecciones de endpoints que son importantes para la clasificación de otros endpoints. Dentro de estos se encuentran los sitios que nos permite consultar los sitios financieros disponibles para sincronizar a través de Paybook Sync.

### Credentials ([Credenciales](#credenciales))
Las credenciales se refieren a la información de terceras personas que se necesita para autorizar el acceso a un sitio de terceros. Las credenciales se encriptan al momento de introducirse y no están disponibles en ningún endpoint. La información que se extrae de este endpoint, será sólo complementaria.
>Es necesario tener al menos un usuario registrado para crear credenciales.

### Accounts ([Cuentas](#cuentas))
Las cuentas son repositorios de transacciones de usuarios finales, que normalmente se clasifican por alguna característica como tipo y/o número de cuenta. La cuenta y la información de las transacciones pueden recuperarse desde sitios de terceros y se actualizan hasta una vez al día.

### Transactions ([Transacciones](#transacciones))
Las transacciones son los movimientos financieros que están relacionados con una cuenta, y reflejan el ingreso o egreso que el usuario final tiene en determinado sitio. La cantidad de información histórica que Sync puede recuperar, varía dependiendo de la fuente pero, por lo general, estarán disponibles las transacciones de 60 días.

### Attachments ([Archivos Adjuntos](#archivos-adjuntos-attachments))
Los archivos adjuntos son archivos que están relacionados con las cuentas o las transacciones. La disponibilidad y el tipo de archivo adjunto varía de acuerdo a la fuente.


#### En Resumen:
A partir de los usuarios podemos crear credenciales, las cuales son únicas por cada usuario e institución.
Una vez creada una credencial automáticamente se crea una cuenta correspondiente, esto permite manejar fácilmente diferentes cuentas de un mismo usuario de una misma institución.

Por último pero no menos importante, se encuentran las transacciones que dependen de su respectiva cuenta, algunas transacciones vienen con un documento el cual llamaremos Attachments o mejor dicho _“documentos adjuntos”_, los cuales son el último recurso en el modelo de de información de Paybook Sync.

## Implementación

### La librería
Incluye la librería y declara tu API Key.
```php
require __DIR__.'./vendor/autoload.php';
define('API_KEY', '<TU_API_KEY>');
use Paybook\Sync\Sync;
```
> **_¡Importante!:_** No escribas tu API KEY directamente en tu código o en texto plano, ya que es una mala práctica de seguridad.

La librería incluye los métodos:
* `Sync.auth()`
```php
// Crear una sesión para un usuario
$token = Sync::auth(
    array("api_key" => API_KEY), // Tu API KEY
    array("id_user"=>$id_user) // ID de usuario
);
```
  >_**Nota**: Para realizar la autenticación es necesario tener creado un usuario, de donde se obtiene el **id_user**, vease este [ejemplo](#crear-un-usuario)._
* `Sync.run()`

```php
// Consumir un recurso de Sync
$response = Sync::run(
    $token, // Autenticación
    "/credentials", // Recurso
    null, // Parametros
    'GET' // Método HTTP
);
```

Y hace uso los siguientes métodos de HTTP:

Metodo | Acción
---| ---|
GET | Consultar
POST | Crear
PUT |  Actualizar
DELETE | Eliminar

Dependiendo del recurso y la acción a realizar, será el método de la librería, el método de HTTP y el elemento de autenticación (se explica en la sección de [autenticación](#autenticación)) a usar.

>Puedes ver más acerca de cada uno en [recursos y ejemplos](#recursos-y-ejemplos).

### Implementación de un Webhook 

Un **Webhook** es una devolución de llamada HTTP a un URL especificado. Ellos se activan cada vez que se actualizan los datos de sincronización para ayudarte a mantenerte al día con los últimos cambios en la información.

La ventaja principal es que te permite recibir las últimas actualizaciones de credenciales, transacciones y attachments directamente en tu aplicación sin necesidad de estar preguntando constantemente por ellas.

![alt](https://media.giphy.com/media/l2JehPbx5eIFLqAms/giphy.gif)

Para fines prácticos de desarrollo usaremos el servicio de [ngrok][ngrok], el cual nos permite crear URLs públicas para exponer nuestro servidor local a través de internet.
Puedes consultar cómo instalarlo en su [página de descargas](https://ngrok.com/download).

Ahora crearemos un servidor sencillo con [PHP](https://www.php.net/) y [Slim](https://www.slimframework.com/). 

> Cabe mencionar que se hace uso de estas tecnologías con fines ilustrativos y el desarrollador es libre de implementar las que crea más convenientes. 

Creando un archivo al que llamaremos `server.php` e incluiremos el siguiente código:
```php
<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

$app->post('/webhook', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello from my webhook!");
    return $response;
});

$app->run();
```

Habiendo terminado lo anterior, instalamos slim con el comando `composer require slim/slim slim/psr7` y luego corremos nuestro servidor con el comando `php -S server.js`

> **Warning:** The built-in web server was designed to aid application development. It may also be useful for testing purposes or for application demonstrations that are run in controlled environments. It is not intended to be a full-featured web server. It should not be used on a public network.

Por último ejecutamos ngrok con el comando: `<path-to>/ngrok http 3000` y tendremos nuestro servidor listo escuchando por actualizaciones del webhook.

La URL que nos proporcione ngrok es la misma que tendrás que mandar cuando creas un webhook como en este [ejemplo](#crear-webhook).

> **Nota**: ngrok solo debe ser usado durante el desarrollo y no se recomienda usarlo de esta forma en producción.

### Sync Widget

El widget de Sync se puede usar para **crear**, **actualizar** y **activar** la sincronización de credenciales de forma sencilla con pocas líneas de código desde tu ***Front-end***. Visita el [repositorio oficial][sync-widget-repo] para implementarlo, ampliamente recomendado.

<figure class="image">
  <img src="https://drive.google.com/uc?export=view&id=1Ll-fQQodIEnlx9ys0U4hn67y8w_EjNlX"/>
</figure>

La ventaja principal es que te brinda una poderosa interfaz que te permite ahorrar pasos en la implementación. 

## Autenticación

En el API de Paybook Sync hay dos elementos de autenticación: **_API KEY_** & **_TOKEN_**.

- **API KEY:**  Es la llave maestra que se le otorga a un desarrollador cuando se registra en Paybook Sync, como se muestra en el apartado de [requerimientos](#requerimientos).

- **Token:** El token es una llave de operación volátil, caduca luego de cinco minutos de inactividad, su alcance se limita a nivel User y fue diseñada para realizar operaciones a nivel FrontEnd. Va ligado directamente a un usuario y únicamente puede consultar o actualizar la información de éste (a diferencia de la API key con la que puedes consultar todo). Prácticamente es una combinación de tu API key y un usuario.

> Por razones de seguridad se recomienda ampliamente utilizar el Token ya que así limitas el acceso de información solamente al usuario correspondiente.

Todas las peticiones al API de Paybook Sync deben ir autenticadas, dependerá del recurso con el que se va a operar la llave de autenticación a utilizar.

Recurso | Auth | 
---------|----------|
 Usuarios | API KEY
 Webhooks | API KEY
 Catalogs | Token
 Credentials | Token
 Accounts | Token
 Transactions | Token
 Attachments | Token

### Obtener un Token de sesión:
```php
$token = Sync::auth(
  array("api_key" => API_KEY), // Tu API KEY
  array("id_user"=>$id_user) // ID de usuario
);
```
#### Respuesta:
```php
  print_r($token);
  // #Imprime: { token: "d5b33dcf996ac34fd2fa56782d72bff6"}
```
>Puedes ver más acerca de cada uno en [recursos y ejemplos](#recursos-y-ejemplos).

## Flujo de Información

En este apartado veremos un ejemplo del flujo ideal para obtener la información desde el API de Paybook Sync, aunque las posibilidades son muchas, este es el caso ideal para hacernos con la información.

![data-flow-img][data-flow-img]

> Para este punto, si decidiste hacer uso del [Sync Widget](#sync-widget), te habrás ahorrado los pasos 2, 3 y 4.

_Los pasos en el diagrama son los siguientes:_

1. Crea un usuario. ([ver ejemplo](#crear-un-usuario))
2. Consulta los sitios y selecciona el sitio que deseas sincronizar. ([ver ejemplo](#catálogos-de-instituciones))
3. Crea una credencial para ese usuario y sitio. ([ver ejemplo](#crear-credenciales-normal))
4. Monitorea el estatus de la nueva credencial. ([ver ejemplo](#consultar-credenciales))
5. Crea un webhook nuevo, indica un Endpoint en tu aplicación donde esperas las actualizaciones). ([ver ejemplo](#crear-webhook))
6. Espera y procesa las actualizaciones que recibas de Sync.
7. Consulta la nueva información disponible en el recurso respectivo.

## Recursos y Ejemplos

Puedes consultar más información acerca de los parametros de cada recurso en la [documentación oficial de paybook][sync-doc-endpoint].

### Usuarios

<table>
<thead>
  <tr>
    <th>Recurso</th>
    <th>Acción</th>
    <th>Método</th>
    <th>Autenticación</th>
    <th>Parametros</th>
  </tr>
</thead>
<tbody>
  <tr>
    <td rowspan="2">/users</td>
    <td>Consulta usuarios para esa API Key</td>
    <td>GET</td>
<td rowspan="4">

```php
{ api_key: API_KEY }
```
</td>
<td>

```php
{
    "id_external", // Opcional
    "fields", // Opcional
    "limit", // Opcional
    "skip", // Opcional
    "order" // Opcional
}
```
</td>
  </tr>
  <tr>
    <td>Crea un usuario nuevo</td>
    <td>POST</td>
  <td rowspan="2">

```php
{
  "id_external", // Opcional
  "name"
}
```
  </td>
  </tr>
  <tr>
    <td rowspan="2">/users/:id_user</td>
    <td>Actualiza un usuario</td>
    <td>PUT</td>
  </tr>
  <tr>
    <td>Elimina un usuario</td>
    <td>DELETE</td>
    <td>{}</td>
  </tr>
</tbody>
</table>

#### Consultar usuarios
```php
$response = Sync::run(
    array("api_key" => SYNC_API_KEY),
    '/users', 
    array(), 
    'GET'
);
```
Devuelve:
```json
[
  {
      "id_user": "5df859c4a7a6442757726ef4",
      "id_external": "ELSAN090909",
      "name": "El Santo",
      "dt_create": 1576556996,
      "dt_modify": null
  },
  {
      "id_user": "5e061a673e0acd77bf7d3c7b",
      "id_external": "BLDM140389",
      "name": "Blue Demon",
      "dt_create": 1577458279,
      "dt_modify": null
  }
]
```

#### Consultar un usuario en especifico
```php
 $response = Sync::run(
  array("api_key" => API_KEY),
  '/users', 
  array("id_user"=>'5df859c4a7a6442757726ef4'), 
  'GET'
);
```
Devuelve:
```json
[
  {
      "id_user": "5df859c4a7a6442757726ef4",
      "id_external": "ELSAN090909",
      "name": "El Santo",
      "dt_create": 1576556996,
      "dt_modify": null
  }
]
```
#### Crear un Usuario
```php
$response = Sync::run(
  array("api_key" => API_KEY),
  '/users', 
  array(
      "id_external"=> 'MIST030794',
      "name"=> 'Rey Misterio'
  ), 
  'POST'
);
$id_user = $response->id_user;
```
Devuelve:
```json
{
    "id_user": "5e06694b93f4a91cb218f28f",
    "id_external": "MIST030794",
    "name": "Rey Misterio",
    "dt_create": 1577478475,
    "dt_modify": null
}
```
#### Actualizar un Usuario

```php
$response = Sync::run(
  array("api_key" => API_KEY),
  "/users/$id_user", 
  array(
      "name"=> 'El Santo Jr.'
  ), 
  'PUT'
);
```
Devuelve:
```json
  {
      "id_user": "5df859c4a7a6442757726ef4",
      "id_external": "ELSAN090909",
      "name": "El Santo Jr.",
      "dt_create": 1576556996,
      "dt_modify": 1576557005
  }
```

#### Eliminar un usuario
>_**Nota:** Esto eliminará toda la información del usuario._

```php
$response = Sync::run(
  array("api_key" => API_KEY),
  "/users/$id_user", 
  array(), 
  'DELETE'
);
```
Devuelve:
```json
{
  "rid": "d337e963-680f-4009-85dc-ab439bfdc771",
  "code": 200,
  "errors": null,
  "status": true,
  "message": null,
  "response": true
}
```

### Catálogos


<table>
<thead>
  <tr>
    <th>Recurso</th>
    <th>Acción</th>
    <th>Método</th>
    <th>Autenticación</th>
    <th>Parametros</th>
  </tr>
</thead>
<tbody>
  <tr>
    <td rowspan="1">/catalogues/account_types</td>
    <td>Consultar los tipos de cuentas</td>
    <td rowspan="6">GET</td>
<td rowspan="6">

```php
{ token: TOKEN }
```
</td>
<td rowspan="3">

```php
{
    "fields", // opcional
    "limit", // opcional
    "skip", // opcional
    "order", // opcional
}
```
</td>
  </tr>
  <tr>
    <td>/catalogues/attachment_types</td>
    <td>Consulta los tipos de archivos adjuntos</td>
  </tr>
  <tr>
    <td>/catalogues/countries</td>
    <td>Consulta los países disponibles</td>
  </tr>
  <tr>
    <td>/catalogues/sites</td>
    <td>Consulta los sitios disponiles</td>
  <td rowspan="2">

```php
{
  "id_site", //opcional
  "id_site_organization", //opcional
  "id_site_organization_type", //opcional
  "fields", //opcional
  "limit", //opcional
  "skip", //opcional
  "order", //opciona
}
```
  </td>
  </tr>
  <tr>
    <td>/catalogues/site_organizations</td>
    <td>Consulta las organizaciones del sitio</td>
  </tr>
  <tr>
    <td>/catalogues/organizations/sites</td>
    <td>Consulta los sitios agrupados por organización del sitio</td>
  <td rowspan="1">

```php
{
  "id_site", //opcional
  "id_site_organization", //opcional
  "id_site_organization_type", //opcional
}
```
  </td>
  </tr>
</tbody>
</table>

#### Catálogos de Instituciones

Paybook Sync proporciona un catálogo de las instituciones que podemos sincronizar para los usuarios.

```php
// Consultar catálogos
$response = Sync::run(
  $token,
  "/catalogues/sites", 
  null,
  'GET'
);
```

Devuelve:
```json
[
  {
      "id_site": "56cf5728784806f72b8b4568",
      "id_site_organization": "56cf4ff5784806152c8b4567",
      "id_site_organization_type": "56cf4f5b784806cf028b4568",
      "id_site_type": "5b285177056f2911c13dbce1",
      "is_business": 1,
      "is_personal": 1,
      "version": 1,
      "name": "Normal",
      "credentials": [
          {
              "name": "username",
              "type": "text",
              "label": "Username",
              "required": true,
              "username": true,
              "token": false,
              "validation": null
          },
          {
              "name": "password",
              "type": "password",
              "label": "Password",
              "required": true,
              "username": false,
              "token": false,
              "validation": null
          }
      ],
      "endpoint": "/v1/credentials"
  },
  {
      "id_site": "56cf5728784806f72b8b4569",
      "id_site_organization": "56cf4ff5784806152c8b4567",
      "id_site_organization_type": "56cf4f5b784806cf028b4568",
      "id_site_type": "5b285177056f2911c13dbce1",
      "is_business": 1,
      "is_personal": 1,
      "version": 1,
      "name": "Token",
      "credentials": [
          {
              "name": "username",
              "type": "text",
              "label": "Username",
              "required": true,
              "username": true,
              "token": false,
              "validation": null
          },
          {
              "name": "password",
              "type": "password",
              "label": "Password",
              "required": true,
              "username": false,
              "token": false,
              "validation": null
          }
      ],
      "endpoint": "/v1/credentials"
  },
  {
      "id_site": "572ba390784806060f8b458b",
      "id_site_organization": "56cf4ff5784806152c8b4567",
      "id_site_organization_type": "56cf4f5b784806cf028b4568",
      "id_site_type": "5b285177056f2911c13dbce1",
      "is_business": 1,
      "is_personal": 1,
      "version": 1,
      "name": "Token & captcha",
      "credentials": [
          {
              "name": "username",
              "type": "text",
              "label": "Username",
              "required": true,
              "username": true,
              "token": false,
              "validation": null
          },
          {
              "name": "password",
              "type": "password",
              "label": "Password",
              "required": true,
              "username": false,
              "token": false,
              "validation": null
          }
      ],
      "endpoint": "/v1/credentials"
  }
  .
  .
  .
]
```

### Credenciales

<table>
<thead>
  <tr>
    <th>Recurso</th>
    <th>Acción</th>
    <th>Método</th>
    <th>Autenticación</th>
    <th>Parametros</th>
  </tr>
</thead>
<tbody>
  <tr>
    <td rowspan="2">/credentials</td>
    <td>Consulta credenciales registradas</td>
    <td>GET</td>
<td rowspan="4">

```php
{ token: TOKEN }
```

</td>
<td>

```php
{}
```
</td>
  </tr>
  <tr>
    <td>Crea o actualiza credenciales</td>
    <td>POST</td>
  <td rowspan="1">

```php
{
  "id_site",
  "credentials"
}
```
  </td>
  </tr>
  <tr>
    <td>/credentials/:id_credential</td>
    <td>Elimina un usuario</td>
    <td>DELETE</td>
<td rowspan="2">

```php
{}
```
</td>
  </tr>
  <tr>
    <td>/jobs/:id_job/status</td>
    <td>Consulta historial de cambios hechos a estas credenciales (últimos 15 días)</td>
    <td>GET</td>
  </tr>
</tbody>
</table>

> Puedes consultar más acerca de los códigos de respuesta y su significado [aquí][sync-doc-code.response].

Cada institución tiene sus propias credenciales, algunas instituciones requieren un paso de seguridad _"Two factor authentication"_ o _"TWOFA"_; Los siguientes ejemplos cubren ambos casos.

#### Credenciales normales
**_Normal_**: Credenciales que sólo requieren **_user_** y **_password_**.

1. ##### Crear credenciales normal
```php
  // Consultar catálogos
$payload = array("id_site"=>"5da784f1f9de2a06483abec1");
$response = Sync::run(
  $token,
  "/catalogues/sites", 
  $payload,
  'GET'
);
$site = $response[0];
print_r($site)
/* Algo como esto:
{
    "id_site": "56cf5728784806f72b8b4568",
    .
    .
    .
    "credentials": [
        {
            "name": "username",
            "type": "text",
            "label": "Username",
            "required": true,
            "username": true,
            "token": false,
            "validation": null
        },
        {
            "name": "password",
            "type": "password",
            "label": "Password",
            "required": true,
            "username": false,
            "token": false,
            "validation": null
        }
    ],
    "endpoint": "/v1/credentials"
}
*/
$credentials = array();
$credentials[$site->credentials[0]->name] = 'ACM010101ABC';
$credentials[$site->credentials[1]->name] = 'test';
$payload['credentials'] = $credentials;
$normalCredential = Sync::run(
  $token,
  "/credentials", 
  $payload,
  'POST'
);
```
Devuelve:
```json
{
   "id_credential":"5e17c432d7288d358a039141",
   "id_job_uuid":"5e17c4325d4f6077171c0253",
   "id_job":"5e17c4325d4f6077171c0254",
   "is_new":1,
   "username":"A**********C",
   "ws":"wss://sync.paybook.com/v1/status/5e17c4325d4f6077171c0254",
   "status":"https://sync.paybook.com/v1/jobs/5e17c4325d4f6077171c0254/status",
   "twofa":"https://sync.paybook.com/v1/jobs/5e17c4325d4f6077171c0254/twofa"
}
```

2. ##### Monitoreo del estado de la credencial

```php
$response = Sync::run(
  $token,
  "/credentials", 
  null,
  'GET'
);
```
Devuelve:
```json
[
   {
      "id_credential":"5e17c432d7288d358a039141",
      "id_user":"5e17c430b021255889294af7",
      "id_environment":"574894bf7848066d138b4570",
      "id_external":"",
      "id_site":"56cf5728784806f72b8b4568",
      "id_site_organization":"56cf4ff5784806152c8b4567",
      "id_site_organization_type":"56cf4f5b784806cf028b4568",
      "id_organization":"56cf4ff5784806152c8b4567",
      "is_authorized":1,
      "is_locked":0,
      "is_twofa":0,
      "can_sync":0,
      "ready_in":86377,
      "username":"A**********C",
      "code":200,
      "keywords":null,
      "dt_authorized":1578615866,
      "dt_execute":1578615858,
      "dt_ready":1578702266,
      "dt_refresh":null
   }
]
```
#### Credenciales TWOFA

1. ##### Consulta el sitio Twofa del catálogo
```php
// Consultar catálogos
$payload = array("id_site"=>"56cf5728784806f72b8b4569");
$response = Sync::run(
  $token,
  "/catalogues/sites", 
  $payload,
  'GET'
);
```
2. ##### Crea las credencials
```php
// Crear credenciales con Token o Autenticación de dos pasos
$twofaSite = $response[0];
$credentials = array();
$credentials[$twofaSite->credentials[0]->name] = 'test';
$credentials[$twofaSite->credentials[1]->name] = 'test';
$payload['credentials'] = $credentials;
$twofaCredential = Sync::run(
  $token,
  "/credentials", 
  $payload,
  'POST'
);
/* Regresa:
{
  id_credential: "5e27c17ca2fc48614c41b33e",
  id_job_uuid: "5e27c17ce6fea94c7c6b4193",
  id_job: "5e27c17ce6fea94c7c6b4194",
  is_new: 1,
  username: "t**t",
  ws: "wss://sync.paybook.com/v1/status/5e27c17ce6fea94c7c6b4194",
  status: "https://sync.paybook.com/v1/jobs/5e27c17ce6fea94c7c6b4194/status",
  twofa: "https://sync.paybook.com/v1/jobs/5e27c17ce6fea94c7c6b4194/twofa"
}
*/
```
3. ##### Consulta el status de las credenciales y valida que sea TWOFA
```php
// Consulta Status Credenciales twofa
$id_job = $twofaCredential->id_job;
$response = Sync::run(
  $token,
  "/jobs/$id_job/status", 
  null,
  'GET'
);
/* Regresa:
[
  {
      code: 100
  },
  {
      code: 101
  },
  {
      code: 410,
      address: "https://sync.paybook.com/v1/jobs/5e27c17ce6fea94c7c6b4194/twofa",
      twofa: [
          {
              name: "token",
              type: "text",
              label: "Enter any number sequence as a token"
          }
      ]
  }
]
*/
$is_twofa = False;
if($response[sizeof($response)->code] == 410) {
  $is_twofa = True;
}
```
4. ##### Manda el TWOFA
```php
// Manda TWOFA
$twofaToken = array("twofa" => array());
$twofaToken["twofa"][$response[2]->twofa[0]->name] = "123456";
$twofa = Sync::run(
  $token,
  "/jobs/$id_job/twofa", 
  $twofaToken, 
  'POST'
);
/* Regresa:
{
  rid: "ea1e848d-6ec2-454d-b296-0fe2b6c958c2",
  code: 200,
  errors: null,
  status: true,
  message: null,
  response: true
}
*/
```
5. ##### Consulta nuevamente el status
```php
$id_job = $credential->id_job;
$response = Sync::run(
  $token,
  "/jobs/$id_job/status", 
  null,
  'GET'
);
/* Regresa:
[
  {
      code: 100
  },
  {
      code: 101
  },
  {
      code: 410,
      address: "https://sync.paybook.com/v1/jobs/5e27c17ce6fea94c7c6b4194/twofa",
      twofa: [
          {
              name: "token",
              type: "text",
              label: "Enter any number sequence as a token"
          }
      ]
  },
  {
      code: 102
  },
  {
      code: 200
  }
]
*/
```

#### Consultar credenciales
```php
$response = Sync::run(
  $token,
  "/credentials", 
  null,
  'GET'
);
```
Devuelve:
```json
[
   {
      "id_credential":"5e17c432d7288d358a039141",
      "id_user":"5e17c430b021255889294af7",
      "id_environment":"574894bf7848066d138b4570",
      "id_external":"",
      "id_site":"56cf5728784806f72b8b4568",
      "id_site_organization":"56cf4ff5784806152c8b4567",
      "id_site_organization_type":"56cf4f5b784806cf028b4568",
      "id_organization":"56cf4ff5784806152c8b4567",
      "is_authorized":1,
      "is_locked":0,
      "is_twofa":0,
      "can_sync":0,
      "ready_in":86377,
      "username":"t**t",
      "code":200,
      "keywords":null,
      "dt_authorized":1578615866,
      "dt_execute":1578615858,
      "dt_ready":1578702266,
      "dt_refresh":null
   }
]
```

#### Eliminar una credencial
```php
$id_credential = $credential->id_credential;
$response = Sync::run(
  $token,
  "/credentials/$id_credential", 
  null,
  'DELETE'
);
```

Devuelve:
```json
{
   "rid":"c241efe0-03a6-41cb-9e8e-73d6e0a779c1",
   "code":200,
   "errors":null,
   "status":true,
   "message":null,
   "response":true
}
```
#### Consultar historial de cambios de la credencial
```php
// Despues de crear una credencial (como en ejemplos anteriores)
$id_job = $twofaCredential->id_job;
$response = Sync::run(
  $token,
  "/jobs/$id_job/status", 
  null,
  'GET'
);
```

Devuelve:
```json
[
   {
      "code":100
   },
   {
      "code":101
   },
   {
      "code":102
   },
   {
      "code":200
   }
]
```
> Puedes consultar el significado de cada código [aquí][sync-doc-code.response].

### Cuentas
<table>
<thead>
  <tr>
    <th>Recurso</th>
    <th>Acción</th>
    <th>Método</th>
    <th>Autenticación</th>
    <th>Parametros</th>
  </tr>
</thead>
<tbody>
  <tr>
    <td>/accounts</td>
    <td>Consulta las cuentas de un usuario específico</td>
    <td>GET</td>
<td>

```php
{ token: token }
```
</td>
<td>

```php
{
    "id_account", //opcional
    "id_credential", //opcional
    "id_site", //opcional
    "id_site_organization", //opcional
    "id_site_organization_type", //opcional
    "is_disable", //opcional
    "fields", //opcional
    "limit", //opcional
    "skip", //opcional
    "order", //opcional
    "keywords", //opcional
    "skip_keywords" //opcional
}
```
</td>
  </tr>
</tbody>
</table>

#### Consulta las cuentas de un usuario específico

```php
$response = Sync::run(
  $token,
  "/accounts", 
  array("id_credential"=>$id_credential),
  'GET'
);
```
Devuelve:
```json
[
  {
    "id_account":"3406d3750b215b9a7f8b4523",
    "id_user":"15f98da7784606ef028b4598",
    "id_external":"37f98da4584806ef028b4567",
    "id_credential":"4806d33c0b234af8028b478b",
    "id_site":"98cf5728784839f72b8b449f",
    "id_site_organization":"29cf4ff5784806152c8b4548",
    "name":"My Bank Account",
    "number":null,
    "balance":1200,
    "site":{
        "id_site":"12cf5728784883f72b8b495f",
        "name":"The Bank",
        "avatar":"/images/8574c68f0b212a194a8c1819/avatar",
        "cover":"/images/5944c68f0b212a194a8c3949/cover",
        "small_cover":"/images/59454c68f0b212a194a8c5719/small_cover"
    },
    "dt_refresh":1460816581
  }
]
```

### Transacciones
<table>
<thead>
  <tr>
    <th>Recurso</th>
    <th>Acción</th>
    <th>Método</th>
    <th>Autenticación</th>
    <th>Parametros</th>
  </tr>
</thead>
<tbody>
  <tr>
    <td>/transactions</td>
    <td>Consulta las transacciones de un usuario específico</td>
    <td>GET</td>
<td rowspan="2">

```php
{ token: token }
```
</td>
<td>

```php
{
    "id_transaction", //opcional
    "id_account", //opcional
    "id_credential", //opcional
    "id_site", //opcional
    "id_site_organization", //opcional
    "id_site_organization_type", //opcional
    "has_attachment", //opcional
    "is_disable", //opcional
    "dt_refresh_from", //opcional
    "dt_refresh_to", //opcional
    "dt_transaction_from", //opcional
    "dt_transaction_to", //opcional
    "fields", //opcional
    "limit", //opcional
    "skip", //opcional
    "order", //opcional
    "keywords", //opcional
    "skip_keywords" //opcional
}
```
</td>
  </tr>
  <tr>
    <td>/transactions/count</td>
    <td>Consulta el número de transacciones dados algunos parámetros de búsqueda</td>
    <td>GET</td>
<td>

```php
{
    "id_transaction", //opcional
    "id_account", //opcional
    "id_credential", //opcional
    "id_site", //opcional
    "id_site_organization", //opcional
    "id_site_organization_type", //opcional
    "is_disable", //opcional
    "dt_refresh_from", //opcional
    "dt_refresh_to", //opcional
    "dt_transaction_from", //opcional
    "dt_transaction_to", //opcional
    "keywords", //opcional
    "skip_keywords" //opcional
}
```
</td>
  </tr>
</tbody>
</table>

#### Consulta las transacciones de un usuario específico
```php
$response = Sync::run(
  $token,
  "/transactions", 
  array(
      "id_credential"=>$id_credential,
      "limit"=>5
  ),
  'GET'
);
```
Devuelve:
```json
[
  {
    "id_transaction": "5b64940bcaaf237edf60ce7a",
    "id_account": "5703f88223428348328b45db",
    "id_account_type": "520d3aa93b8e778e0d000000",
    "id_credential": "5e1d5734e849507b770c5007",
    "id_currency": "523a25953b8e77910e8b456c",
    "id_disable_type": "5bcff1e77d8b6b44380f6da2",
    "id_external": "",
    "id_site": "56cf5728784806f72b8b4568",
    "id_site_organization": "56cf4ff5784806152c8b4567",
    "id_site_organization_type": "56cf4f5b784806cf028b4568",
    "id_user": "5df7e5f40437a90a8d5037a0",
    "is_account_disable": 0,
    "is_deleted": 0,
    "is_disable": 1,
    "is_pending": 0,
    "description": "ACME Checking Transaction 20",
    "amount": 26,
    "currency": "MXN",
    "attachments": [],
    "extra": null,
    "reference": null,
    "keywords": null,
    "dt_transaction": 1533099600,
    "dt_refresh": 1561500261,
    "dt_disable": 1561500261,
    "dt_deleted": null
  },
  {
      "id_transaction": "5b686fd6caaf237ddc20304a",
      "id_account": "5703f88223428348328b45db",
      "id_account_type": "520d3aa93b8e778e0d000000",
      "id_credential": "5e1d5734e849507b770c5007",
      "id_currency": "523a25953b8e77910e8b456c",
      "id_disable_type": "5bcff1e77d8b6b44380f6da2",
      "id_external": "",
      "id_site": "56cf5728784806f72b8b4568",
      "id_site_organization": "56cf4ff5784806152c8b4567",
      "id_site_organization_type": "56cf4f5b784806cf028b4568",
      "id_user": "5df7e5f40437a90a8d5037a0",
      "is_account_disable": 0,
      "is_deleted": 0,
      "is_disable": 1,
      "is_pending": 0,
      "description": "ACME Checking Transaction 20",
      "amount": 49,
      "currency": "MXN",
      "attachments": [],
      "extra": null,
      "reference": null,
      "keywords": null,
      "dt_transaction": 1533099600,
      "dt_refresh": 1559007370,
      "dt_disable": 1559007370,
      "dt_deleted": null
  },
  .
  .
  .
]
```
#### Consulta el número de transacciones dados algunos parámetros de búsqueda

```php
$response = Sync::run(
  $token,
  "/transactions/count", 
  array("id_credential"=>$id_credential),
  'GET'
);
```

Devuelve:
```json
{
  "count": 140
}
```

### Webhooks
<table>
<thead>
  <tr>
    <th>Recurso</th>
    <th>Acción</th>
    <th>Método</th>
    <th>Autenticación</th>
    <th>Parametros</th>
  </tr>
</thead>
<tbody>
  <tr>
    <td rowspan="3">/webhooks</td>
    <td>Consulta los webhooks creados</td>
    <td>GET</td>
<td rowspan="3">

```php
{ api_key: API_KEY }
```
</td>
<td>

```php
{}
```
</td>
  </tr>
  <tr>
    <td>Crear o actualizar un webhhook</td>
    <td>POST</td>
<td>

```php
{
    "url", 
    "events" //["credential_create","credential_update","refresh"]
}
```
</td>
  </tr>
  </tr>
  <tr>
    <td>Eliminar un webhhook</td>
    <td>DELETE</td>
<td>

```php
{}
```
</td>
  </tr>
</tbody>
</table>

#### Crear Webhook

```php
$response = Sync::run(
  array("api_key" => API_KEY),
  "/webhooks", 
  array(
      "url"=>'http://mydomain.ngrok.io/webhook'; // Tu endpoint donde recibiras la devolución de llamada, 
      "events"=>array("credential_create","credential_update","refresh")
  ),
  'POST'
);
```
Devuelve:
```json
{
   "id_webhook":"5e17c4746cee651e7b03df34",
   "id_user":null,
   "events":[
      "credential_create",
      "credential_update",
      "refresh"
   ],
   "url":"http://8e763e9e.ngrok.io/webhook",
   "delay":0,
   "dt_created":1578615924,
   "dt_modified":null
}
```

#### Consultar Webhooks

```php
$response = Sync::run(
  array("api_key" => API_KEY),
  "/webhooks", 
  null,
  'GET'
);
```

Devuelve:
```json
[
   {
      "id_webhook":"5e17c4746cee651e7b03df34",
      "id_user":null,
      "is_disabled":0,
      "events":[
         "credential_create",
         "credential_update",
         "refresh"
      ],
      "url":"http://8e763e9e.ngrok.io/webhook",
      "delay":0,
      "ct_failed":0,
      "dt_created":"2020-01-10T00:25:24+00:00",
      "dt_modified":null
   }
]
```
####  Eliminar Webhook

```php
$id_webhook = $response->id_webhook;
$response = Sync::run(
  array("api_key" => API_KEY),
  "/webhooks/$id_webhook", 
  null,
  'DELETE'
);
```
Devuelve:
```json
{
   "rid":"faea7aad-5db5-4d8e-b0c3-57a975c18ea2",
   "code":200,
   "errors":null,
   "status":true,
   "message":null,
   "response":true
}
```

### Archivos adjuntos (Attachments)
<table>
<thead>
  <tr>
    <th>Recurso</th>
    <th>Acción</th>
    <th>Método</th>
    <th>Autenticación</th>
    <th>Parametros</th>
  </tr>
</thead>
<tbody>
  <tr>
    <td>/attachments</td>
    <td>Consulta los archivos adjuntos de un usuario específico</td>
    <td rowspan="4">GET</td>
<td rowspan="4">

```php
{token: TOKEN}
```
</td>
<td>

```php
{
    "id_account", //opcional
    "id_attachment_type", //opcional
    "id_credential", //opcional
    "id_transaction", //opcional
    "is_valid", //opcional
    "dt_refresh_from", //opcional
    "dt_refresh_to", //opcional
    "fields", //opcional
    "limit", //opcional
    "skip", //opcional
    "order", //opcional
    "keywords", //opcional
    "skip_keywords" //opcional
}
```
</td>
  </tr>
  <tr>
    <td>/attachments/count</td>
    <td>Consultar el número de archivos adjuntos</td>
<td>

```php
{
  "id_account", //opcional
  "id_attachment_type", //opcional
  "id_credential", //opcional
  "id_transaction", //opcional
  "is_valid", //opcional
  "dt_refresh_from", //opcional
  "dt_refresh_to", //opcional
  "keywords", //opcional
  "skip_keywords" //opcional
}
```
</td>
  </tr>

  <tr>
    <td>/attachments/:id_attachment</td>
    <td>Regresa el archivo adjunto</td>
<td>

```php
{}
```
</td>
  </tr>
</td>
  </tr>

  <tr>
    <td>/attachments/:id_attachment/extra</td>
    <td>Regresa la información extraída del archivo adjunto</td>
<td>

```php
{}
```
</td>
  </tr>
</tbody>
</table>

#### Consulta los archivos adjuntos de un usuario específico

```php
$response = Sync::run(
  $token,
  "/attachments", 
  array("id_credential"=>$id_credential),
  'GET'
);
```

Devuelve:
```json
[
   {
      "id_attachment":"5db073b4caaf236a6a4c2eb5",
      "id_account":"5db073b1caaf236a6a4c2acc",
      "id_user":"5e17c430b021255889294af7",
      "id_external":"",
      "id_attachment_type":"56bcdfca784806d1378b4567",
      "id_transaction":"5db073b1caaf236a6a4c2acd",
      "is_valid":1,
      "file":"4B2B511C-A29E-4CDF-8AD3-143515CF6152.xml",
      "mime":null,
      "url":"/attachments/5db073b4caaf236a6a4c2eb5",
      "keywords":[
         "3.3",
         "emitidas",
         "i",
         "timbrefiscaldigital",
         "vigente"
      ],
      "dt_refresh":1571844997
   },
   {
      "id_attachment":"5db073b4caaf236a6a4c2eb6",
      "id_account":"5db073b1caaf236a6a4c2acc",
      "id_user":"5e17c430b021255889294af7",
      "id_external":"",
      "id_attachment_type":"56bcdfca784806d1378b4567",
      "id_transaction":"5db073b1caaf236a6a4c2ace",
      "is_valid":1,
      "file":"27D33E8C-0120-4F98-ACCD-1C0DBA9D794F.xml",
      "mime":null,
      "url":"/attachments/5db073b4caaf236a6a4c2eb6",
      "keywords":[
         "001",
         "002",
         "3.3",
         "i",
         "impuestos",
         "recibidas",
         "retenciones",
         "timbrefiscaldigital",
         "traslados",
         "vigente"
      ],
      "dt_refresh":1571844997
   },
   .
   .
   .
]
```
#### Regresa el archivo adjunto
```php
$attachment = $response[0];
$attachmentUrl = $attachment->url;
$response = Sync::run(
  $token,
  $attachmentUrl, 
  null,
  'GET'
);
```
Devuelve: 
```xml
<?xml version="1.0" encoding="utf-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv33.xsd" Version="3.3" Serie="C" Folio="78" Fecha="2019-01-24T06:15:49" FormaPago="99" NoCertificado="0000100000040090000" Certificado="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" SubTotal="810.18" Descuento="0.00" Moneda="MXN" Total="810.18" TipoDeComprobante="I" MetodoPago="PUE" LugarExpedicion="00000" Sello="CjGJ6Uz6jWQ6wFyn8SEvRVCOCZq2sRtTAsUkLJjrs8vPSHfeEs+bMBhNnZ+7gLE5gSO+FU+IA64d+I9w98DEop24GRNDoWPxRGvX7SON4p47Ygna/rCWynyjCw8kYqRtBHxypwh0HGFLoNx+ulK+WcOXG7F3Nx2I+EPTg6jn1VvwBm1c1iat1Zgnhcna2ZJyZA3cRFOBhNONocr+qAF8zTtHSoJNYmolOlyIC9akyIPfrNl/ALgni4k1KwpEcr4HsyqVabUDW47vH5TqSNfFz+ZY3bZZZf7FLdTBn8So984+vbomWg/rP7OCBALI/u/+kIkgotm4TF/ImuGjUeKITw==">
    <cfdi:Emisor Rfc="ACM010101ABC" Nombre="ACME CORP" RegimenFiscal="601"/>
    <cfdi:Receptor Rfc="EKU9003173C9" Nombre="ESCUELA KEMPER URGATE SA DE CV" UsoCFDI="P01"/>
    <cfdi:Conceptos>
        <cfdi:Concepto ClaveProdServ="00195316" Cantidad="1" ClaveUnidad="H87" Unidad="PZA" Descripcion="PRODUCTO 1" ValorUnitario="810.18" Importe="810.18" Descuento="0"></cfdi:Concepto>
    </cfdi:Conceptos>
    <cfdi:Complemento>
        <tfd:TimbreFiscalDigital xmlns:tfd="http://www.sat.gob.mx/TimbreFiscalDigital" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sat.gob.mx/TimbreFiscalDigital http://www.sat.gob.mx/sitio_internet/cfd/timbrefiscaldigital/TimbreFiscalDigitalv11.xsd" Version="1.1" UUID="4B2B511C-A29E-4CDF-8AD3-143515CF6152" FechaTimbrado="2019-10-11T02:43:34" SelloCFD="CjGJ6Uz6jWQ6wFyn8SEvRVCOCZq2sRtTAsUkLJjrs8vPSHfeEs+bMBhNnZ+7gLE5gSO+FUz6jWQ6wFyn8SEvRVCOCZq2sWPxRGvX7SON4p47Ygna/rCWynyjCw8kYqRtBHxypwh0HGFLoNx+ulK+WcOXG7F3Nx2I+EPTg6jn1Vvz6jWQ6wFyn8SEvRVCOCZq2s3cRFOBhNONocr+qAF8zTtHSoJNYmolOlyIC9akyIPfrNl/ALgni4k1KwpEcr4HsyqVabz6jWQ6wFyn8SEvRVCOCZq2sf7FLdTBn8So984+vbomWg/rP7OCBALI/u/+kIkgotm4TF/ImuGjUeKITw==" NoCertificadoSAT="00001000000407657133" SelloSAT="W82etl3ZwAEDP7qT705tkK7jryQU5WCFUIOw1nmDyy5/iaxsOVuDPVyhK7fQMRG6A6x9WHGYjkMDZ0DkOnq1vcClr2Sn5yOLdWd9VYf6hg6e/RiLTn1mSUni/47rWNbyODfcom3hmKasclgBEYZL6unymQD2bsUfMc0L5ODZ5/AfK36/bYVeakauL1NxXMZ2Zc4RrX5Zd5AkF04VB6UWqDL2/zuCGDccA6/MsJR2BeXnq/hQ1I8WNSNGcy1OzBukQqRbhe9hIRit58F/ZtTtFnzyeT3Tknu9MIZFq/7D+tw0TxUziC+g2n86iiKRXnRJQvlowqKmkPGO8jMefrx8pg==" RfcProvCertif="ACM100625MC0"/>
    </cfdi:Complemento>
</cfdi:Comprobante>
```

#### Regresa la información extraída del archivo adjunto
```php
$attachment = $response[0];
$attachmentUrl = $attachment->url;
$response = Sync::run(
  $token,
  $attachmentUrl."/extra", 
  null,
  'GET'
);
```

Devuelve:
```json
{
    "id_attachment": "5db073b4caaf236a6a4c2eb5",
    "id_user": "5df7e5f40437a90a8d5037a0",
    "id_external": "",
    "is_valid": 1,
    "file": "4B2B511C-A29E-4CDF-8AD3-143515CF6152.xml",
    "mime": null,
    "extra": [
        {
            "n": "CFDI:COMPROBANTE",
            "a": {
                "XMLNS:CFDI": "http://www.sat.gob.mx/cfd/3",
                "XMLNS:XSI": "http://www.w3.org/2001/XMLSchema-instance",
                "XSI:SCHEMALOCATION": "http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv33.xsd",
                "VERSION": "3.3",
                "SERIE": "C",
                "FOLIO": "78",
                "FECHA": "2019-01-24T06:15:49",
                "FORMAPAGO": "99",
                "NOCERTIFICADO": "0000100000040090000",
                "CERTIFICADO": "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",
                "SUBTOTAL": "810.18",
                "DESCUENTO": "0.00",
                "MONEDA": "MXN",
                "TOTAL": "810.18",
                "TIPODECOMPROBANTE": "I",
                "METODOPAGO": "PUE",
                "LUGAREXPEDICION": "00000",
                "SELLO": "CjGJ6Uz6jWQ6wFyn8SEvRVCOCZq2sRtTAsUkLJjrs8vPSHfeEs+bMBhNnZ+7gLE5gSO+FU+IA64d+I9w98DEop24GRNDoWPxRGvX7SON4p47Ygna/rCWynyjCw8kYqRtBHxypwh0HGFLoNx+ulK+WcOXG7F3Nx2I+EPTg6jn1VvwBm1c1iat1Zgnhcna2ZJyZA3cRFOBhNONocr+qAF8zTtHSoJNYmolOlyIC9akyIPfrNl/ALgni4k1KwpEcr4HsyqVabUDW47vH5TqSNfFz+ZY3bZZZf7FLdTBn8So984+vbomWg/rP7OCBALI/u/+kIkgotm4TF/ImuGjUeKITw=="
            },
            "c": [
                {
                    "n": "CFDI:EMISOR",
                    "a": {
                        "RFC": "ACM010101ABC",
                        "NOMBRE": "ACME CORP",
                        "REGIMENFISCAL": "601"
                    }
                },
                {
                    "n": "CFDI:RECEPTOR",
                    "a": {
                        "RFC": "EKU9003173C9",
                        "NOMBRE": "ESCUELA KEMPER URGATE SA DE CV",
                        "USOCFDI": "P01"
                    }
                },
                {
                    "n": "CFDI:CONCEPTOS",
                    "c": [
                        {
                            "n": "CFDI:CONCEPTO",
                            "a": {
                                "CLAVEPRODSERV": "00195316",
                                "CANTIDAD": "1",
                                "CLAVEUNIDAD": "H87",
                                "UNIDAD": "PZA",
                                "DESCRIPCION": "PRODUCTO 1",
                                "VALORUNITARIO": "810.18",
                                "IMPORTE": "810.18",
                                "DESCUENTO": "0"
                            }
                        }
                    ]
                },
                {
                    "n": "CFDI:COMPLEMENTO",
                    "c": [
                        {
                            "n": "TFD:TIMBREFISCALDIGITAL",
                            "a": {
                                "XMLNS:TFD": "http://www.sat.gob.mx/TimbreFiscalDigital",
                                "XMLNS:XSI": "http://www.w3.org/2001/XMLSchema-instance",
                                "XSI:SCHEMALOCATION": "http://www.sat.gob.mx/TimbreFiscalDigital http://www.sat.gob.mx/sitio_internet/cfd/timbrefiscaldigital/TimbreFiscalDigitalv11.xsd",
                                "VERSION": "1.1",
                                "UUID": "4B2B511C-A29E-4CDF-8AD3-143515CF6152",
                                "FECHATIMBRADO": "2019-10-11T02:43:34",
                                "SELLOCFD": "CjGJ6Uz6jWQ6wFyn8SEvRVCOCZq2sRtTAsUkLJjrs8vPSHfeEs+bMBhNnZ+7gLE5gSO+FUz6jWQ6wFyn8SEvRVCOCZq2sWPxRGvX7SON4p47Ygna/rCWynyjCw8kYqRtBHxypwh0HGFLoNx+ulK+WcOXG7F3Nx2I+EPTg6jn1Vvz6jWQ6wFyn8SEvRVCOCZq2s3cRFOBhNONocr+qAF8zTtHSoJNYmolOlyIC9akyIPfrNl/ALgni4k1KwpEcr4HsyqVabz6jWQ6wFyn8SEvRVCOCZq2sf7FLdTBn8So984+vbomWg/rP7OCBALI/u/+kIkgotm4TF/ImuGjUeKITw==",
                                "NOCERTIFICADOSAT": "00001000000407657133",
                                "SELLOSAT": "W82etl3ZwAEDP7qT705tkK7jryQU5WCFUIOw1nmDyy5/iaxsOVuDPVyhK7fQMRG6A6x9WHGYjkMDZ0DkOnq1vcClr2Sn5yOLdWd9VYf6hg6e/RiLTn1mSUni/47rWNbyODfcom3hmKasclgBEYZL6unymQD2bsUfMc0L5ODZ5/AfK36/bYVeakauL1NxXMZ2Zc4RrX5Zd5AkF04VB6UWqDL2/zuCGDccA6/MsJR2BeXnq/hQ1I8WNSNGcy1OzBukQqRbhe9hIRit58F/ZtTtFnzyeT3Tknu9MIZFq/7D+tw0TxUziC+g2n86iiKRXnRJQvlowqKmkPGO8jMefrx8pg==",
                                "RFCPROVCERTIF": "ACM100625MC0"
                            }
                        }
                    ]
                }
            ]
        }
    ]
}
```

## Entorno
 _Versions used by the time this file was writted_
 - [**PHP**](https://www.php.net/): 7.1.23
 - [**Composer**](https://getcomposer.org): 1.10
 - [**rmccue/requests**](https://packagist.org/packages/rmccue/requests):^1.7

## Enlaces de interes
1. [Documentación oficial][sync-doc-intro] de Paybook Sync.
2. [Parametros para cada recurso][sync-doc-endpoint].
2. [Códigos de respuesta y su significado][sync-doc-code.response].
2. [Consumiendo el API REST de Sync vía Postman paso a paso][sync-postman-doc]. Ampliamente recomendado para entender la estructura de una cuenta de Paybook Sync.

## Comentarios y aportes

¡Sientete con la confianza de hacer un pull request! :ok_hand:

---
_Made with :blue_heart: by Paybook family._

 [//]: # (These are reference links used in the body of this note and get stripped out when the markdown processor does its job. There is no need to format nicely because it shouldn't be seen. Thanks SO - http://stackoverflow.com/questions/4823468/store-comments-in-markdown-syntax)

[Paybook Sync]: <https://www.paybook.com/sync/es/>
[sync-doc-intro]: <https://www.paybook.com/sync/es/docs/intro>
[sync-doc-endpoint]: <https://www.paybook.com/w/es/sync/site/docs/api?topics=endpoints>
[sync-doc-code.response]: <https://www.paybook.com/w/es/sync/site/docs/api?topics=response&topics=code>
[sync-postman-doc]: <https://github.com/Paybook/sync-rest>
[sync-widget-repo]: <https://github.com/Paybook/sync-widget>

[ngrok]: <https://ngrok.com/>
[logo]: <https://raw.githubusercontent.com/Paybook/sync-php/master/images/syncLogo.svg?sanitize=true>
[sync-model-image]: <https://raw.githubusercontent.com/Paybook/sync-php/master/images/resourceModel.svg?sanitize=true>
[data-flow-img]: <https://raw.githubusercontent.com/Paybook/sync-php/master/images/dataFlow.svg?sanitize=true>
