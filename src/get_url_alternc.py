#! /usr/bin/python
#
# Copyright (C) 2001,2002 by the Free Software Foundation, Inc.
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
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.

"""Print a list's web_page_url attribute

This script is intended to be run as a bin/withlist script, i.e.

% bin/withlist -l -r get_url_alternc listname

"""

import sys
import getopt

import paths
from Mailman import mm_cfg
from Mailman.i18n import _



def get_url_alternc(mlist, *args):

    print mlist.web_page_url 
    mlist.Unlock()


