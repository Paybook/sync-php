     
# Descarga de Facturas en formato JSON

Este es un ejemplo de cómo descargar las facturas que Sync sincroniza en formato JSON. 

### Requerimientos

- [PHP](http://php.net/)
- [Instalar PHP Composer](https://getcomposer.org/doc/00-intro.md)
- Una API key de Sync
- Una usario de Sync con transacciones del SAT (facturas) previamente sincronizadas.

### Ejecución

Abrir tu CLI. 

Clonar este repositorio en tu computadora:
	
	git clone https://github.com/Paybook/sync-php.git

Moverse al directorio de este ejemplo:

	cd sync-php/examples/json_invoices

Instalar la biblioteca de Paybook:

	composer install

Modifica los valores de las variables API key y ID user dentro del script:
	
	$api_key = 'YOUR_API_KEY';
    $id_user = 'SOME_SYNC_ID_USER';

Una vez hecho esto ejecuta el script:
	
	php json_invoices.php

Observarás en tu CLI lo siguiente:
	
	1. 3992153FCC6C4640AAA289FD8F68D700.xml obtenido
   	2. 38B34374E0724756B796A98C10E76B38.xml obtenido
   	3. 0C6213C4F8004D72AEF118BF24DD5FDC.xml obtenido
   	4. F3BB2522AA7D494FADFB13188CC7D021.xml obtenido
   	5. 271A1587FE584960BC7A3F81C3DAD8C6.xml obtenido


 	Las facturas fueron descargadas en formato JSON y guardadas en el archivo: 

   	/Users/hugo/Documents/paybook/dev/libraries/sync-php/examples/attachments_extra/facturas.json

 	\0/ Script ejecutado exitósamente. Gracias por usar Sync

Puedes observar que las facturas descargadas en formato JSON fueron guardadas en un archivo en este mismo directorio:

	facturas.json
	
Este script descaga únicamente 5 facturas dentro del periodo comprendido entre el 1 de febrero y 1 de marzo de 2017. Sin embargo, puedes modificarlo para descargar el número de facturas que desees en el periodo de tu elección.








