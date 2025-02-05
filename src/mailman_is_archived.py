#!/usr/bin/python3
from mailman.config import config
import requests

def main(listname):
    api_root = 'http://localhost:8001/3.0'
    user, pas = config.webservice.admin_user, config.webservice.admin_pass

    resp = requests.get('%s/lists/%s/config' % (api_root, listname), auth=(user, pas))
    res = resp.json()['archive_policy']
    print(res)
