
##QUICKSTART BANCO NORMAL

A lo largo de este tutorial te enseñaremos como sincronizar una institución bancaria normal, es decir, aquella que solo requiere una autenticación (usuario y contraseña), ejemplos de estas instituciones pueden ser Banamex o Santander. En el tutorial asumiremos que ya hemos creado usuarios y por tanto tenemos usuarios ligados a nuestra API KEY, también asumiremos que hemos instalado la librería de python y hecho las configuraciones pertinentes. Si tienes dudas acerca de esto te recomendamos que antes de tomar este tutorial consultes el [Quickstart para sincronizar al SAT](https://github.com/Paybook/sync-py/blob/master/quickstart_sat.md) ya que aquí se abordan los temas de creación de usuarios y sesiones.  

### Requerimientos

1. Haber consultado el tutorial [Quickstart para sincronizar al SAT](https://github.com/Paybook/sync-py/blob/master/quickstart_sat.md)
2. Tener credenciales de alguna institución bancaria que cuente con autenticación simple (usuario y contraseña)

##Ejecución:

Este tutorial está basado en el script [quickstart_normal_bank.py](https://github.com/Paybook/sync-py/blob/master/quickstart_normal_bank.py) por lo que puedes descargar el archivo, configurar los valores YOUR_API_KEY, YOUR_BANK_USERNAME y YOUR_BANK_PASSWORD y ejecutarlo en tu equipo:

```
$ python quickstart_normal_bank.py
```

Una vez que has ejecutado el archivo podemos continuar analizando el código.

####1. Obtenemos un usuario e iniciamos sesión:
El primer paso para realizar la mayoría de las acciones en Paybook es tener un usuario e iniciar una sesión, por lo tanto haremos una consulta de nuestra lista de usuarios y seleccionaremos el usuario con el que deseamos trabajar. Una vez que tenemos al usuario iniciamos sesión con éste.


```python
user_list = paybook.User.get()
user = user_list[0]
print user.name + ' ' + user.id_user
session = paybook.Session(user=user)
print session.token
```

####2. Consultamos el catálogo de las instituciones de Paybook:
Recordemos que Paybook tiene un catálogo de instituciones que podemos seleccionar para sincronizar nuestros usuarios. A continuación consultaremos este catálogo:

```python
sites = paybook.Catalogues.get_sites(session=session)
for site in sites:
	print site.name
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

Para efectos de este tutorial seleccionaremos **SuperNET Particulares (Santander)** pero tu puedes seleccionar la institución de la cual tienes credenciales.

```python
bank_site = None
sites = paybook.Catalogues.get_sites(session=session)
for site in sites:
	print site.name
	if site.name == 'SuperNET Particulares':
	   	bank_site = site
print 'Bank site: ' + bank_site.name + ' ' + bank_site.id_site
```

####3. Registramos las credenciales:

A continuación registraremos las credenciales de nuestro banco, es decir, el usuario y contraseña que nos proporcionó el banco para acceder a sus servicios en línea:

```python
CREDENTIALS = {
	'username' : BANK_USERNAME,
	'password' : BANK_PASSWORD
}#End of CREDENTIALS
bank_credentials = paybook.Credentials(session=session,id_site=bank_site.id_site,credentials=CREDENTIALS)
print bank_credentials.id_credential + ' ' + bank_credentials.username
```
####4. Checamos el estatus

Una vez que has registrado las credenciales de una institución bancaria para un usuario en Paybook el siguiente paso consiste en checar el estatus de las credenciales, el estatus será una lista con los diferentes estados por los que las credenciales han pasado, el último será el estado actual. A continuación se describen los diferentes estados de las credenciales:

| Código         | Descripción                                |                                
| -------------- | ---------------------------------------- | ------------------------------------ |
| 100 | Credenciales registradas   | 
| 101 | Validando credenciales  | 
| 401      | Credenciales inválidas    |
| 102      | La institución se está sincronizando    |
| 200      | La institución ha sido sincronizada    | 

Checamos el estatus de las credenciales:

```python
sync_status = bank_credentials.get_status(session=session)
print sync_status
```
####5. Analizamos el estatus:

El estatus se muestra a continuación:

```
[{u'code': 100}, {u'code': 101}]
```

Esto quiere decir que las credenciales han sido registradas y se están validando. Puesto que la institución bancaria a sincronizar i.e. Santander, no requiere autenticación de dos pasos e.g. token o captcha podemos únicamente checar el estatus buscando que las credenciales hayan sido validadads (código 102) o bien hayan sido inválidas (código 401)

```python
print 'Esperando validacion de credenciales ... '
status_102_or_401 = None
while status_102_or_401 is None:
    print ' . . . '
    time.sleep(3)
    sync_status = bank_credentials.get_status(session=session)
    print sync_status
    for status in sync_status:
        code = status['code']
        if code == 102 or code == 401:
            status_102_or_401 = status
    if status['code'] == 401:
        print 'Error en credenciales'
        sys.exit()
```

####6. Esperamos a que la sincronización termine

Una vez que la sincronización se encuentra en proceso (código 102), podemos construir un bucle para polear y esperar por el estatus de fin de sincronización (código 200).

```python
print 'Esperando sincronizacion ... '
status_200 = None
while status_200 is None:
    print ' . . . '
    time.sleep(3)
    sync_status = bank_credentials.get_status(session=session)
    print sync_status
    for status in sync_status:
        code = status['code']
        if code == 200:
            status_200 = status
```

####7. Consultamos las transacciones de la institución bancaria:

Una vez que la sincronización ha terminado podemos consultar las transacciones:

```python
transactions = paybook.Transaction.get(session=session)
```

Podemos desplegar información de las transacciones:

```python
i = 0
for transaction in transactions:
    i+=1
    print str(i) + '. ' + transaction.description + ' $' + str(transaction.amount) 
```

¡Felicidades! has terminado con este tutorial.

###Siguientes Pasos

- Revisar el tutorial de como sincronizar una institución bancaria con token [aquí](https://github.com/Paybook/sync-py/blob/master/quickstart_token_bank.md)

- Puedes consultar y analizar la documentación completa de la librearía [aquí](https://github.com/Paybook/sync-py/blob/master/readme.md)

- Puedes consultar y analizar la documentación del API REST [aquí](https://www.paybook.com/sync/docs#api-Overview)

- Acceder a nuestro proyecto en Github y checar todos los recursos que Paybook tiene para ti [aquí](https://github.com/Paybook)














