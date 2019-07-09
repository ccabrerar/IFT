# IFT AGI Script
### Instituto Federal de Telecomunicaciones (http://www.ift.org.mx/)

Este script sirve para integrar la marcación de Asterisk (http://asterisk.org) con la base de datos del IFT Mexicano a través de Asterisk Gateway Interface (AGI). La intención es poder comparar cada número marcado y a través de sus primeros dígitos, rastrear el tipo de número para obtener si se trata de un número fijo o móvil, y marcar acorde.


El script como tal no hace ninguna llamada, pero si cambia el contenido de ciertas variables que pueden ser usadas posteriormente para ejecutar la llamada de manera correcta.


Con un poco de ajustes, también puede usarse para tarificación, ya que a partir del 3/agosto/2019, la marcación en México cambia a ser únicamente 10 dígitos, por lo que se facilitará la marcación hacia el carrier pero se dificultará la tarificación y los permisos de salida ya que todos los números se marcarán de la misma manera.


Los archivos ```ift.sql``` e ```ift_extended.sql``` se actualizarán periódicamente. Puedes crear scripts automatizados que se encarguen de descargarlos automáticamente de Github y con eso actualizar tus propias base de datos.


Este script se publicó por primera vez en el blog de AsteriskMX en la liga https://asteriskmx.org/valida-tus-telefonos-moviles-y-fijos-con-la-base-de-datos-del-ift/

El CSV original del IFT puede descargarse desde https://sns.ift.org.mx:8081/sns-frontend/planes-numeracion/descarga-publica.xhtml

Para resolución de bugs puedes usar el Issue Tracker de Github. Si requieres alguna personalización, ajuste o desarrollo personalizado que se base en este script, puedes contactarnos en https://enlaza.mx/contacto/ 



### Requisitos
- Asterisk
- PHP CLI 5.5+
- Módulo MySQLi para PHP

### Uso
Invocar desde Asterisk como:
```
exten => _X.,n,AGI(ift.php,${EXTEN})
```
        
La variable ${EXTEN} debe ser de al menos 10 dígitos

Este script no hace ningún tipo de llamada. Únicamente llena algunas variables para que podamos utilizarlas en nuestra troncal al momento de marcar. Las variables creadas son:

- ```${MOVIL}``` contiene 1 o 0, indicando si el número ingresado es móvil o fijo
- ```${PREFIJO}``` contiene el prefijo de larga distancia, celular, o celular LDN (01, 044 o 045) que se necesite según el tipo de número obtenido
- ```${COMPLETO}``` contiene el número completo tal como se marca en la mayoría de los carriers:
                    ```XXXXXXX``` para números fijos locales
                    ```01XXXXXXXXXX``` para números fijos de LD
                    ```044XXXXXXXXXX``` para números móviles locales
                    ```045XXXXXXXXXX``` para números móviles de LD


### Cambios recientes
- 2019-07-08 - Primera publicación en Github
- 2019-04-03 - Actualización a uso de MySQLi y apagado de reporteo
