# trackerm

![alt text](https://github.com/diegargon/trackm/blob/master/screenshots/library-screenshot.png?raw=true)

## Description: 

Probando a realizar una alternativa Sonarr+Radar sobre servidor web
Warning: Codigo/esbozo realizado a correr (el grueso fue programado  en 3 intensos dias), no solo hay que pulirlo y reescribir mucho si no que esta sin 
seguridad y asi continuara hasta que tenga una version con las funciones basicas.
Probablemente de momento no deberias instalarlo.
Puedes ver sceenshots del aspecto actual(posiblemente desactualizado) en /screenshots aunque cambiara que el proyecto esta en fase muy 
muy temprana.

Trying a Sonarr & Radarr alternative over a web server.
Warning: Fast coding (the bulk was done in 3 days) have to polish alot/rewrite alot and came without any security, and 
will remain like this until i have a working code with basic features.
Probably you shouldn't install it at this moment.
You can see screenshots of the current appearance (not latest probably) in /screenshots although it going to change since this proyect 
is in a very very early stage.

## CURRENT STATE

Now we use a sql database (sqllite) instead of plain text, i can't guarantee backwards compatibility between versions yet, but will
not be something frequent. Anyway, all work for setting from 0 is near automatic, just only click on rebuild the library and identify items.

About security, i begin adding checks but ins't not secure yet for expose to internet (and not security passwords for enter), you must still 
use other method (.htaccess) 

About code, after the change from plain/text among other things i have a lot of messy code that need rewrite and rewrite querys to database 
but for this type of application this is not a priority.

I 'fast coding' this app in about 10 days, now for a while i would have less time to update, and going to slow down this focusing for in fix 
the messy code, bugs and security things than add new options.

## WARNING

There are no security mechanisms in any line of code yet, use on your own risk. The code is totally insecure. 
If you expose this code to internet you have a very high security problem. why? want this app "now" and 
have too much time but in few days,the solution was quick code without stopping and without pay attention
to security  details. 
Security and better code will comming more slowly

No hay ningun mecanismo de seguridad en ninguna linea del codigo todavia. El codigo es totalmente inseguro. 
Si expones este codigo a internet tendras un grave problema de seguridad. ¿por que? queria esta aplicación 
"ya" y tenia mucho tiempo pero en pocos dias, la solución fue teclear codigo rapido y sin pararme en detalles 
de seguridad.
Seguridad y mejor codigo vendra mucho mas despacio.

## Requeriments

    Linux - PHP - Web Server - transmission-daemon - Jackett - themoviedb.org account&API key
    composer - sqlite3(not yet but probably)

    Version Compatibility? it's not the momment. I working on:
    Ubuntu 20.04
    Php 7.4 (php7 is necessary)
    Sqlite 3.31.1

## INSTALL

in INSTALL.es (Spanish) or in INSTALL.en, (bad english and probably not update).

## LATEST   

Go LATEST
