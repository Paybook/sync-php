     
# Descarga de Facturas en Paralelo

Este es un ejemplo de cómo descargar facturas en paralelo haciendo uso de la biblioteca de Sync para PHP.

### Importante

Este script está hecho para correr sobre una imagen de Docker que cuenta con las siguientes dependencias:

- PHP 7.0.16
- Biblioteca para manejo de Threads en PHP [pthreads](http://php.net/manual/en/book.pthreads.php)

Tu puedes elegir correrlo haciendo uso de Docker, o bien, instalar dichas dependencias en tu equipo. La documentación necesaria para instalar [pthreads](http://php.net/manual/en/book.pthreads.php) en tu sistema operativo y para tu versión de PHP van más allá del alcance de este ejemplo, puedes consultar la documentación directamente de [pthreads](http://php.net/manual/en/book.pthreads.php).

### Requerimientos

- [Instalar Docker 1.10.0+](https://docs.docker.com/engine/getstarted/step_one/).
- [Instalar PHP Composer](https://getcomposer.org/doc/00-intro.md)
- Se utiliza la imagen de Docker [php:7.0-zts](https://github.com/docker-library/php/blob/0792ba42f0ea7435ceb26b42a066274e028b30e3/7.0/zts/Dockerfile) publicada en [PHP - Docker registry](https://hub.docker.com/_/php/)

### Ejecución

Clonar este repositorio en tu computadora:
	
	git clone https://github.com/Paybook/sync-php.git

Moverse al directorio de este ejemplo:

	cd sync-php/examples/download_invoices_parallel

Instalar la biblioteca de Paybook:

	composer install

Configurar el archivo *main.php*. Abrir el archivo y agregar los valores correspondientes a las siguientes variables:
	
	$api_key = 'YOUR_API_KEY';
	$id_user = 'SOME_ID_USER';
	$id_credential = 'SOME_ID_CREDENTIAL';

##### Ejecutar localmente:
	
Si no harás uso de la imagen de Docker puedes correr el archivo localmente:
	
	php main.php

##### Ejecutar en contenedor (Docker):

Si harás uso de la imagen de Docker, primero hay que construir la imagen:

	docker build -t download_invoices .

Correr el script en el contenedor:

	docker run -it -v $PWD:/usr/src/example download_invoices php /usr/src/example/main.php

Las facturas seran descargadas en la carpeta *downloads* dentro de este mismo directorio

	cd downloads

Aquí estarán todos los archivos pdf y xml descargados.

El script está acotado a descargar a lo más 500 archivos. 





