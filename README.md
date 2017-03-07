

# Paybook PHP Library Vdev-master

Esta es la librería de Paybook para PHP. Mediante esta librería usted puede implementar el API REST de Paybook de manera rapida y sencilla a través de sus clases y métodos.

### Requerimientos

1. [PHP](http://php.net/) (versión recomendada posterior o igual a 5.6.X)
2. [Composer](https://getcomposer.org/doc/00-intro.md)

### Instalación

En tu proyecto donde desees incorporar la librería de Paybook para PHP debes agregar el archivo composer.json con el siguiente contenido:

```
{
    "require": {
        "paybook/paybook": "dev-master"
    }
}
```

**Importante: ** Es posible que tu proyecto ya tenga un archivo composer.json en este caso únicamente debes agregar "paybook/paybook" : "dev-master" en el contenido de la llave "require".

Una vez hecho esto ejecutar (dentro del directorio donde se encuentra el archivo composer.json):

```
	composer install
```

En caso de que no hayas creado un proyecto nuevo y estés modificando un proyecto ya existente puedes ejecutar

```
	composer update
```

## Quickstart:

Antes de consultar la documentación puedes tomar alguno de nuestros tutoriales:

- [Wizard/Ejemplo para descarga de facturas](https://github.com/Paybook/sync-php/tree/master/examples/download_invoices_wizard) **Recomendado si es tu primera vez usando el API de Sync)**

- [Quickstart para sincronizar al SAT](https://github.com/Paybook/sync-php/blob/master/doc/quickstart_sat.md)

- [Quickstart para sincronizar una cuenta bancaria con credenciales sencillas (usuario y contraseña)](https://github.com/Paybook/sync-php/blob/master/doc/quickstart_normal_bank.md)

- [Quickstart para sincronizar una cuenta bancaria con token (usuario y contraseña)](https://github.com/Paybook/sync-php/blob/master/doc/quickstart_token_bank.md)

## Recordatorios:

- Puedes consultar la documentación del API REST [aquí](https://www.paybook.com/sync/docs#api-Overview)
- Puedes consultar todos los recursos que tenemos para ti [aquí](https://github.com/Paybook)

## Documentación:

Cada método está documentado tomando como base la siguiente estructura:

```php
method_type returned_value_type x = class_or_instance::get(attr1=attr1_type,...,attrn=attrN_type)
```

1. method_type: indica si el método es estático, en caso de no estar indica que el método es de instancia, o bien, es un constructor.
2. returned_value_type: indica el tipo de dato regresado por el método
3. x: es una representación del valor retornado por el método
4. class_or_instance: es la Clase o una instancia de la clase que contiene el método a ejecutar
5. attrX: es el nombre del atributo X
6. attrX_type: es el tipo de dato del atributo X

### Accounts

Estructura de los atributos de la clase:

| Account         |                                  
| -------------- | 
| + str id_account <br> + str id_external <br> + str id_user <br> + str id_credential <br> + str id_site <br> + str id_site_organization <br> + str name <br> + str number <br> + float balance <br> + str site <br> + str dt_refresh  |
				
Descripción de los métodos de la clase:

| Action         | REST API ENDPOINT                                 | LIBRARY METHOD                                  |
| -------------- | ---------------------------------------- | ------------------------------------ |
| Requests accounts of a user | GET https://sync.paybook.com/v1/accounts | ```static list [Account] = Account::get(session=Session,id_user=str)```          |

### Attachments

Estructura de los atributos de la clase:

| Attachments         |                                  
| -------------- | 
| + str id_account <br> + str id_external <br> + str id_user <br> + str id_attachment_type <br> + str id_transaction <br> + str file <br> + str extra <br> + str url <br> + str dt_refresh |
		
Descripción de los métodos de la clase:

| Action         | REST API ENDPOINT                                 | LIBRARY METHOD                                  |
| -------------- | ---------------------------------------- | ------------------------------------ |
| Requests attachments | GET https://sync.paybook.com/v1/attachments <br> GET https://sync.paybook.com/v1/attachments/:id_attachment <br> GET https://sync.paybook.com/v1/attachments/:id_attachment/extra | ```static list [Attachment] = Attachment::get(session=Session,id_user=str,id_attachment=str,extra=bool)```          |
| Request the number of attachments | GET https://sync.paybook.com/v1/attachments/counts | ```static int attachments_count = Attachment::get_count(session=Session,id_user=str)```          |

### Catalogues

Estructura de los atributos de las clases:

| Account_type         | Attachment_type | Country |                                 
| -------------- | -------------- | -------------- | 
| + str id_account_type <br> + str name | + str id_attachment_type <br> + str name | + str id_country <br> + str name <br> + str code |

| Site         | Credential_structure | Site_organization |                                 
| -------------- | -------------- | -------------- | 
| + str id_site <br> + str id_site_organization <br> + str id_site_organization_type <br> + str name <br> + list credentials | + str name <br> + str type <br> + str label <br> + bool required <br> + str username | + str id_site_organization <br> + str id_site_organization_type <br> + str id_country <br> + str name <br> + str avatar <br> + str small_cover <br> + str cover |

Descripción de los métodos de la clase:

| Action         | REST API ENDPOINT                                 | LIBRARY METHOD                                  |
| -------------- | ---------------------------------------- | ------------------------------------ |
| Request account types | GET https://sync.paybook.com/v1/catalogues/account_types   | ```static list [Account_type] = Catalogues::get_account_types(session=Session,id_user=str)```          |
| Request attachment types | GET https://sync.paybook.com/v1/catalogues/attachment_types   | ```static list [Attachment_type] = Catalogues::get_attachment_types(session=Session,id_user=str)```          |
| Request available countries | GET https://sync.paybook.com/v1/catalogues/countries   | ```static list [Country] = Catalogues::get_countries(session=Session,id_user=str)```          |
| Request available sites | GET https://sync.paybook.com/v1/catalogues/sites   | ```static list [Site] = Catalogues::get_sites(session=Session,id_user=str)```          |
| Request site organizations | GET https://sync.paybook.com/v1/catalogues/site_organizations   | ```static list [Site_organization] = Catalogues::get_site_organizations(session=Session,id_user=str)```          |

### Credentials

Estructura de los atributos de la clase:

| Credentials         |                                  
| -------------- | 
| + str id_site <br> + str id_credential <br> + str username <br> + str id_site_organization <br> + str id_site_organization_type <br> + str ws <br> + str status <br> + str twofa <br> |
				
Descripción de los métodos de la clase:

| Action         | REST API ENDPOINT                                 | LIBRARY METHOD                                  |
| -------------- | ---------------------------------------- | ------------------------------------ |
| Creates or updates credentials | POST https://sync.paybook.com/v1/credentials | ```Credentials credentials = Credential(session=str,id_user=str,id_site=str,credentials=dict)```          |
| Deletes credentials | DELETE https://sync.paybook.com/v1/credentials/:id_credential | ```static bool deleted Credentials::delete(session=Session,id_user=str,id_credential=str)```          |
| Request status | GET status_url | ```list [Dict] = credentials::get_status(session=Session,id_user=str)```          |
| Set twofa | POST twofa_url | ```bool twofa_set = credentials::set_twofa(session=Session,id_user=str,twofa_value=str)```          |
| Request register credentials | GET https://sync.paybook.com/v1/credentials | ```static list [Credentials] = Credentials::get(session=Session,id_user=str)```          |


### Sessions

Estructura de los atributos de la clase:

| Sessions         |                                  
| -------------- | 
| + User user <br> + str token   |
				
Descripción de los métodos de la clase:


| Action         | REST API ENDPOINT                                 | LIBRARY METHOD                                  |
| -------------- | ---------------------------------------- | ------------------------------------ |
| Creates a session | POST https://sync.paybook.com/v1/sessions   | ```Session session = Session(user=str)```          |
| Verify a session | GET https://sync.paybook.com/v1/sessions/:token/verify | ```bool verified = session::verify()```                  |
| Deletes a session     | DELETE https://sync.paybook.com/v1/sessions/:token    | ```static bool deleted = Session::delete(token=str)```|


### Transactions

Estructura de los atributos de la clase:

| Transactions         |                                  
| -------------- | 
| + str id_transaction <br> + str id_user <br> + str id_external <br> + str id_site <br> + str id_site_organization <br> + str id_site_organization_type <br> + str id_account <br> + str id_account_type <br> + str is_disable <br> + str description <br> + float amount <br> + str dt_transaction <br> + str dt_refresh   |
				
Descripción de los métodos de la clase:


| Action         | REST API ENDPOINT                                 | LIBRARY METHOD                                  |
| -------------- | ---------------------------------------- | ------------------------------------ |
| Requests number of transactions | GET https://sync.paybook.com/v1/transactions/count | ```static int transactions_count = Transaction::get_count(session=Session,id_user=str)```          |
| Requests transactions | GET https://sync.paybook.com/v1/transactions | ```static list [Transaction] = Transaction::get(session=Session,id_user=str)```          |

### Users

Estructura de los atributos de la clase:

| Users         |                                  
| -------------- | 
| + str name <br> + str id_user <br> + str id_external <br> + str dt_create <br> + str dt_modify   |

Descripción de los métodos de la clase:


| Action         | REST API ENDPOINT                                 | LIBRARY METHOD                                 |
| -------------- | ---------------------------------------- | ------------------------------------ |
| Creates a user | POST https://sync.paybook.com/v1/users   | ```User user = User(name=str,id_user=str)```          |
| Deletes a user | DELETE https://sync.paybook.com/v1/users | ```static bool deleted = User::delete(id_user=str)```                  |
| Get users      | GET https://sync.paybook.com/v1/users    | ```static list [User] = User::get()```|









