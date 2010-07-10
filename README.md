VirtualHost Manager
===================

Simples comando para criar VirtualHosts no teu LAMP, feito em php-cli.

### Até agora só temos um comando que cria
Futuramente estarei fazendo um upgrade com o comando que remove o dominio.

### Tambem estarei colocando as configurações para um arquivo ~/.vhm


Install:
--------
`chmod +x [path-to-vhm-directory]/vhm`

`sudo ln -s [path-to-vhm-directory]/vhm /usr/bin/vhm`


Usage:
------
**Criar novo VirtualHost**

vhm [new (nw)] [domain name]

`vhm new example.com.br`


**Restart do Apache2**

`vhm up`

