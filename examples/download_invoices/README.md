     
# Descarga de Facturas

Este es un ejemplo de cómo descargar facturas haciendo uso de Sync y de la librería de Sync para PHP.

### Requerimientos

- [PHP](http://php.net/) (versión recomendada posterior o igual a 5.6.X)
- [Composer](https://getcomposer.org/doc/00-intro.md)
- Un [API key de Paybook Sync](https://www.paybook.com/signup/es).

### Contenido

1. [Asistente/Wizard para descarga de facturas](#download_invoices_wizard)
2. [Script ejemplo para descarga de facturas](#download_invoices_example)

<div id='download_invoices_wizard'/>

##### 1. Asistente/Wizard para descarga de facturas

Este proyecto contiene un asistente que se ejecutará en la terminal. Éste nos ayudará a comunicarnos con Sync para la descarga de facturas y al final nos generará un *script* personalizado donde nos va a mostrar paso a paso cómo descargar las facturas haciendo uso de la librería de Sync para PHP.

1. Si no has clonado este repositorio en tu maquina primero hay que clonarlo:

		git clone https://github.com/Paybook/sync-php.git
	
2. Ahora hay que movernos al directorio donde se encuentra este ejemplo:

		cd <your-sync-php-path>/examples/download_invoices/
	
	Dentro del directorio debes observar el siguiente contenido:
		
		README.md	composer.json	template.php    wizard.php

3. Instala la librería de Sync para PHP haciendo uso de *composer*:
	
		composer install

4. Ejecuta el *Wizard*:
	
		php wizard.php

5. Introduce la información que el wizard/asistente solicita. El asistente te llevará paso a paso y te pedirá la información necesaria para la descarga de facturas. El *Wizard* se observará de la siguiente manera:

<p align="center"><img src="https://github.com/Paybook/sync-php/blob/master/examples/download_invoices/wizard.png"></p>

<div id='download_invoices_example'/>

##### 2. Script ejemplo para descarga de facturas

Una vez que has ejecutado el *Wizard*, éste te habrá generado un archivo *example.php*. Ahora puedes correr este *script*:
		
	php example.php		
	
Verás un mensaje como el siguiente:
	
	Archivo 65B537C5314247768610C5EF455DF6A4.xml descargado exitósamente. El archivo fue guardado en: 
	-> <path_to_sync-php>/examples/download_invoices/downloads/example/<some_uuid>.xml
   
El archivo *example.php* es un ejemplo en PHP de cómo descargar las facturas haciendo uso de la librería de Sync para PHP. Es imporante notar qué este script:
	
- hace uso del API key con el que ejecutaste el *Wizard*
- hace uso del usuario con el que ejecutaste el *Wizard*
- descarga las transacciones del sitio que hayas sincronizado haciendo uso del *Wizard*

Si quieres generar otro archivo ejemplo con otra API key u otro usuario solo basta que ejecutes el asistente nuevamente:
	
	php wizard.php
	
Esperamos este recurso te haya sido útil :)













