/*
 $Id$
 ----------------------------------------------------------------------
 AlternC - Web Hosting System
 Copyright (C) 2002 by the AlternC Development Team.
 http://alternc.org/
 ----------------------------------------------------------------------
 Based on:
 Valentin Lacambre's web hosting softwares: http://altern.org/
 ----------------------------------------------------------------------
 LICENSE

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License (GPL)
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 To read the license please visit http://www.gnu.org/copyleft/gpl.html
 ----------------------------------------------------------------------
 Original Author of file: Benjamin Sonntag - 2003-01-19
 Purpose of file: Create a mailman mailing-list
 ----------------------------------------------------------------------
*/

/* setgid() */
#include <sys/types.h>
#include <unistd.h>
/* printf() perror() */
#include <stdio.h>

#define M_PATH "/var/lib/mailman/bin/list_members"

int main(int argc,char *argv[]) {
  if (argc!=2) {
    printf("Utilisation : mailman.list <list> \n\n");
    return -1;
  }
  
  setgid(getegid());

  // WARNING : LIST ZONE //
  execl(M_PATH, M_PATH, argv[1], (char*) NULL);

  perror("execl failed");

  return 0;
}
