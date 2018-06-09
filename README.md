# Device Monitor Daemon
PHP script that scan local network devices and save them on Firebase Realtime Database

## Install

Clone this repository

```sh
$ git clone https://github.com/ibonkonesa/device-monitor-daemon.git
```

Install composer dependencies

```sh
$ composer install
```

## Configure

There is a .env file where you can set some parameters. Firstly copy or rename the .env.example file

```sh
$ cp .env.example .env
```

The avalaible parameters are:

-DELETE_INACTIVE_DEVICES (boolean): delete devices after a time

-DELETE_INACTIVE_DEVICES_AFTER (string):  relative time. Example: 5 minutes, 1 hour, 2 days

-INTERFACE: network interface. Example: eth0, wlan0

-ARP_SCAN_PATH: absolute path for arp-scan binary (cron needs that path is absolute)

-NOTIFY_NEW_DEVICE (boolean): send push notification to deviceNew topic

-NOTIFY_DELETE_DEVICE (boolean): send push notification to deviceDelete topic

## Firebase

You also need to start a Firebase project and download the google-services-account.json

Directions are avalaible here: https://firebase.google.com/docs/admin/setup?hl=es-419

Once you have created the project, you should put the google-services-account in the root project path

## EXECUTION

You must launch this script as a regular user using sudo

```sh
$ sudo php daemon.php
```
or you can set a task in the root user's crontab.


```
* * * * * php /path/to/script/daemon.php
````




