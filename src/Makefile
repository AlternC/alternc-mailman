#
# $Id$
# ----------------------------------------------------------------------
# AlternC - Web Hosting System
# Copyright (C) 2002 by the AlternC Development Team.
# http://alternc.org/
# ----------------------------------------------------------------------
# Based on:
# Valentin Lacambre's web hosting softwares: http://altern.org/
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
# Purpose of file: Makefile for alternc-mailman
# ----------------------------------------------------------------------
#
CC=gcc
CFLAGS=-Wall -Werror
PROGS=mailman.create mailman.delete mailman.list mailman.sub mailman.unsub

all: ${PROGS}

clean: 
	rm -f *.o core *~ ${PROGS}

install:
	chown list.list ${PROGS}
	chmod u+s ${PROGS}




