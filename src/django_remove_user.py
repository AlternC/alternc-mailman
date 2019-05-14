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
		opts, args = getopt.getopt(argv,"e:")
	except getopt.GetoptError as err:
		print(err)
		print(sys.argv[0],'-e <email>')
		sys.exit(2)
	for opt, arg in opts:
		if opt == '-e':
			mail = arg
	try:
		user = User.objects.get(email=mail)
		#print(user.username)
		user.delete()
	except NameError:
		print('var undifined')
		sys.exit(4)


if __name__ == "__main__":
	main(sys.argv[1:])
