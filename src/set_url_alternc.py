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

"""Reset a list's web_page_url attribute to the default setting.

This script is intended to be run as a bin/withlist script, i.e.

% bin/withlist -l -r set_url_alternc listname <url>

Options:
  <url> is the new url, STARTING BY either http:// or https:// ...

If run standalone, it prints this help text and exits.
"""

import sys
import getopt

import paths
from Mailman import mm_cfg
from Mailman.i18n import _



def set_url_alternc(mlist, args):

    web_page_url = sys.argv[6]

    if web_page_url:
        web_page_url = mm_cfg.DEFAULT_URL_PATTERN % web_page_url
    else:
        web_page_url = mm_cfg.DEFAULT_URL_PATTERN % mm_cfg.DEFAULT_URL_HOST

    mlist.web_page_url = web_page_url
    mlist.Save()
    mlist.Unlock()


