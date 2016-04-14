Wescape RESTful service
=======================

# Installazione
Consultare la relativa [sezione](https://github.com/ilario-pierbattista/wescape-api/wiki/Installazione) della wiki.

# Servizi esposti
 * **wescape.dev:80** Servizio rest.
 * **wescape.dev:81** Applicazione _Kibana_ per la lettura dei log.
 * **wescape.db.service:3306** Server MySQL per la gestione del database.

Per un report dettagliato, è sufficiente eseguire `docker ps -a`: saranno elencati i container ed i relativi mapping di rete con l'host.

docker-symfony
==============

Just a litle Docker POC in order to have a complete stack for running Symfony into Docker containers using docker-compose tool.

# Installation

First, clone this repository:

```bash
$ git clone git@github.com:eko/docker-symfony.git
```

Next, put your Symfony application into `symfony` folder and do not forget to add `symfony.dev` in your `/etc/hosts` file.

Then, run:

```bash
$ docker-compose up
```

You are done, you can visite your Symfony application on the following URL: `http://symfony.dev` (and access Kibana on `http://symfony.dev:81`)

_Note :_ you can rebuild all Docker images by running:

```bash
$ docker-compose build
```

# How it works?

Here are the `docker-compose` built images:

* `application`: This is the Symfony application code container,
* `db`: This is the MySQL database container (can be changed to postgresql or whatever in `docker-compose.yml` file),
* `php`: This is the PHP-FPM container in which the application volume is mounted,
* `nginx`: This is the Nginx webserver container in which application volume is mounted too,
* `elk`: This is a ELK stack container which uses Logstash to collect logs, send them into Elasticsearch and visualize them with Kibana.

This results in the following running containers:

```bash
> $ docker-compose ps
        Name                      Command               State              Ports
        -------------------------------------------------------------------------------------------
        docker_application_1   /bin/bash                        Up
        docker_db_1            /entrypoint.sh mysqld            Up      0.0.0.0:3306->3306/tcp
        docker_elk_1           /usr/bin/supervisord -n -c ...   Up      0.0.0.0:81->80/tcp
        docker_nginx_1         nginx                            Up      443/tcp, 0.0.0.0:80->80/tcp
        docker_php_1           php5-fpm -F                      Up      9000/tcp
```

# Read logs

You can access Nginx and Symfony application logs in the following directories into your host machine:

* `logs/nginx`
* `logs/symfony`

# Use Kibana!

You can also use Kibana to visualize Nginx & Symfony logs by visiting `http://symfony.dev:81`.
