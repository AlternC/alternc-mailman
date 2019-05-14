#!/usr/bin/env python3
import os
import sys
import getopt
import getpass
import urllib.request

os.environ.setdefault("DJANGO_SETTINGS_MODULE", "settings")

from django.core.wsgi import get_wsgi_application
application = get_wsgi_application()

from django.contrib.auth.models import User

from mailmanclient import Client
client = Client('http://localhost:8001/3.1', 'restadmin', 'bRaPCVVUJCbl+uZNuEnxASvni3LhQgFSPqDUtEmj1K5tmIm6')


def main(argv):
	try:
		opts, args = getopt.getopt(argv,"u:e:p:")
	except getopt.GetoptError as err:
		print(err)
		print(sys.argv[0],'-u <username> -e <email> -p [<password>]')
		sys.exit(2)
	for opt, arg in opts:
		if opt == '-u':
			username = arg
		elif opt == '-e':
			email = arg
			#check if user exist
			try:
				us = User.objects.get(email=email)
				sys.exit(3)
			except Exception:
				continue
		elif opt == '-p':
			if arg:
				password = arg
			else: 
				password = getpass.getpass('password: ')
	try:
		user = User.objects.create_user(username,email,password)
		#print "username ", username ," email ", email, " password ", password
	except NameError:
		print('var undifined')
		sys.exit(4)


if __name__ == "__main__":
	main(sys.argv[1:])
