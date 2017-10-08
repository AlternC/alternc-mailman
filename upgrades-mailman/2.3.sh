#!/bin/sh

echo "Removing apache2 conf files from former alternc-mailman packages:"

if [ -f "/etc/alternc/apache-panel.d/mailman.conf" ]
then
    rm /etc/alternc/apache-panel.d/mailman.conf
fi

if [ -f "/etc/apache2/conf.d/alternc-mailman.conf" ]
then
    rm /etc/apache2/conf.d/alternc-mailman.conf
fi

if [ -f "/etc/apache2/conf-enabled/alternc-mailman.conf" ]
then
    rm /etc/apache2/conf-enabled/alternc-mailman.conf
fi


