#!/bin/bash

if [ "$(id -u)" == 0 ];
then
	rm -rf /var/www/html/*
	#rm -rf /var/www/html/config /var/www/html/einschreibung /var/www/html/style /var/www/html/Javascript /var/www/html/icons
	cp -rf images/ /var/www/html
	cp -rf einschreibung/ /var/www/html/
	cp -rf config/ /var/www/html/
	cp -rf style/ /var/www/html/
	cp -rf Javascript/ /var/www/html/
	cp -rf wallpapers/ /var/www/html/
	cp -rf fonts/ /var/www/html/
else
	echo "Please run as root"
fi
