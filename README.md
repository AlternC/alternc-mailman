Mailman module for AlternC
==========================

This package is an extension for [AlternC hosting software control panel](https://alternc.com) to manage mailman-based mailing and discussion lists.

it allows AlternC users to create / manage / destroy their mailing lists installed on their own domain name using postfix and mailman on any AlternC 3.X+

Usage:

 * Create a list using alternc panel, then optionnaly create a postorius/hyperkitty user account using the web interface

Installation notes:

 * Mailman site owner email address and default language are set in /etc/mailman3/mailman.cfg
 * Site name (example.com by defaut) is defined at https://site.url/mailman3/admin/sites/site/1/change/
 * You may need to adapt /etc/apache2/conf-enabled/mailman3.conf and remove the
   trailing slash (see https://bugs.debian.org/cgi-bin/bugreport.cgi?bug=996105) 
 * Backports for python3-django-hyperkitty and python3-authheaders are required for debian buster

Alternc-mail versions:

 * alternc-mailman >=3.6 (this version) installs mailman3, eventually on top of
   an existing mailman2 allowing users to select the lists they want to
   migrate, in that case both mailman versions work concurently until mailman2 is
   removed.
 * alternc-mailman <3.6 installs mailman2, optionally with a patch to allow
   multiple domains to use the same list name.
