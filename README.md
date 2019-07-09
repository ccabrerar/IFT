# IFT AGI Script
### Instituto Federal de Telecomunicaciones (http://www.ift.org.mx/)

Este script sirve para integrar la marcación de Asterisk (http://asterisk.org) con la base de datos del IFT Mexicano a través de Asterisk Gateway Interface (AGI). La intención es poder comparar cada número marcado y a través de sus primeros dígitos, rastrear el tipo de número para obtener si se trata de un número fijo o móvil, y marcar acorde.


El script como tal no hace ninguna llamada, pero si cambia el contenido de ciertas variables que pueden ser usadas posteriormente para ejecutar la llamada de manera correcta.


Con un poco de ajustes, también puede usarse para tarificación, ya que a partir del 3/agosto/2019, la marcación en México cambia a ser únicamente 10 dígitos, por lo que se facilitará la marcación hacia el carrier pero se dificultará la tarificación y los permisos de salida ya que todos los números se marcarán de la misma manera.


Los archivos ```ift.sql``` e ```ift_extended.sql``` se actualizarán periódicamente. Puedes crear scripts automatizados que se encarguen de descargarlos automáticamente de Github y con eso actualizar tus propias base de datos.


Este script se publicó por primera vez en el blog de AsteriskMX en la liga https://asteriskmx.org/valida-tus-telefonos-moviles-y-fijos-con-la-base-de-datos-del-ift/

Para resolución de bugs puedes usar el Issue Tracker de Github. Si requieres alguna personalización, ajuste o desarrollo personalizado que se base en este script, puedes contactarnos en https://enlaza.mx/contacto/ 