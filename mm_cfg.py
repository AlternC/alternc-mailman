# -*- python -*-

# WARNING!  DO NOT EDIT THIS FILE!
# 
# This file has been generated by alternc-mailman configuration.
#
# If you want to change anything, you should modify
#   /etc/alternc/templates/mailman/mm_cfg.py
# and use
#   dpkg-reconfigure alternc-mailman
# to update this file.

# Copyright (C) 1998,1999,2000 by the Free Software Foundation, Inc.
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software 
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.


"""This is the module which takes your site-specific settings.

From a raw distribution it should be copied to mm_cfg.py.  If you
already have an mm_cfg.py, be careful to add in only the new settings
you want.  The complete set of distributed defaults, with annotation,
are in ./Defaults.  In mm_cfg, override only those you want to
change, after the

  from Defaults import *

line (see below).

Note that these are just default settings - many can be overridden via the
admin and user interfaces on a per-list or per-user basis.

Note also that some of the settings are resolved against the active list
setting by using the value as a format string against the
list-instance-object's dictionary - see the distributed value of
DEFAULT_MSG_FOOTER for an example."""


# This is some trickery to reverse proxies to work with list creation
# and when restarting mailman.
import os
if os.environ.get('REQUEST_URI') is not None:
    os.environ["REQUEST_URI"] = os.environ["REQUEST_URI"].replace("/cgi/", "/cgi-bin/")

#######################################################
#    Here's where we get the distributed defaults.    #

from Defaults import *

##############################################################
# Put YOUR site-specific configuration below, in mm_cfg.py . #
# See Defaults.py for explanations of the values.	     #


MAILMAN_SITE_LIST = 'mailman'
DEFAULT_EMAIL_HOST = '%%fqdn%%'
DEFAULT_URL_HOST   = '%%fqdn%%'
add_virtualhost(DEFAULT_URL_HOST, DEFAULT_EMAIL_HOST)

ACCEPTABLE_LISTNAME_CHARACTERS ='[-+_.= a-z0-9@]'
DEFAULT_HOST_NAME = '%%fqdn%%'
DEFAULT_URL_PATTERN = 'http://%s/cgi-bin/mailman/'
IMAGE_LOGOS       = '/images/mailman/'
USE_ENVELOPE_SENDER = 0
DEFAULT_SEND_REMINDERS = 0
DEFAULT_DEFAULT_MEMBER_MODERATION = 1

MAILMAN_OWNER     = 'root@%%fqdn%%'

PUBLIC_ARCHIVE_URL = '/pipermail/%(listname)s'
PRIVATE_ARCHIVE_URL = '/cgi-bin/mailman/private/%(listname)s'
DEFAULT_ARCHIVE_PRIVATE    = 1                    # 0=public, 1=private

DEFAULT_SERVER_LANGUAGE = 'fr'

# Note - if you're looking for something that is imported from mm_cfg, but you
# didn't find it above, it's probably in Defaults.py.

# So we don't generate unnecessary mails saying we've to create aliases
# Alternc-mailman does the job of creating aliases for us.
MTA = None # So that mailman skips aliases generation

VIRTUAL_HOST_OVERVIEW = False
