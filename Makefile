#!/usr/bin/make -f
# ----------------------------------------------------------------------
# AlternC - Web Hosting System
# Copyright (C) 2000-2013 by the AlternC Development Team.
# https://alternc.org/
# ----------------------------------------------------------------------
# LICENSE
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License (GPL)
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# To read the license please visit http://www.gnu.org/copyleft/gpl.html
# ----------------------------------------------------------------------
# Purpose of file: Global Makefile 
# ----------------------------------------------------------------------
MAJOR=$(shell sed -ne 's/^[^(]*(\([^)]*\)).*/\1/;1p' debian/changelog)
REV=`git describe --tags | sed 's/^v//; s/-/./g'`
VERSION="${MAJOR}~git${REV}"
export VERSION

build:

install: 
	-mkdir -p $(DESTDIR)/usr/share/alternc/panel \
	   $(DESTDIR)/usr/lib/mailman/bin \
	   $(DESTDIR)/usr/lib/alternc \
	   $(DESTDIR)/usr/share/alternc/install \
	   $(DESTDIR)/usr/lib/alternc/install.d \
	   $(DESTDIR)/usr/share/alternc/install/upgrades-mailman \
	   $(DESTDIR)/usr/sbin \
	   $(DESTDIR)/etc/apache2/conf-available
	cp -r bureau/* $(DESTDIR)/usr/share/alternc/panel/
	# 1999 is alterncpanel (TODO: ask Debian for a static uid/gid ?)
	chown 1999:1999 -R $(DESTDIR)/usr/share/alternc/panel/
	install -m 0644 -o root -g root src/get_url_alternc.py src/set_url_alternc.py \
		$(DESTDIR)/usr/lib/mailman/bin/
	install -m 0755 src/update_mailman.sh \
		$(DESTDIR)/usr/lib/alternc/
	install -m 0755 src/list_migrate $(DESTDIR)/usr/sbin
	install -m 0755 src/alternc_msgfmt $(DESTDIR)/usr/sbin
	install -m 0644 mailman.sql \
		$(DESTDIR)/usr/share/alternc/install/
	install -m 750 alternc-mailman-install $(DESTDIR)/usr/lib/alternc/install.d/
	install -m 0644 debian/apache.conf $(DESTDIR)/etc/apache2/conf-available/alternc-mailman3.conf

	rm -f $(DESTDIR)/usr/share/alternc/panel/locales/Makefile
	install -m 0755 upgrade_mailman_check.sh $(DESTDIR)/usr/share/alternc/install/
	install -m 0644 upgrades-mailman/* $(DESTDIR)/usr/share/alternc/install/upgrades-mailman/
