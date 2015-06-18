#!/usr/bin/python

import requests
import json

hit_url = "http://192.168.1.91/wpg/gridgennew.php?len=8&words=abhay,arpit,varun,sainyam,joel,aman,monk"
grid_len = 8
r = requests.get(hit_url)
if r.status_code == requests.codes.ok :
	url_data = json.loads(r.text)
	for i in range(0,grid_len):
		output_str = ""
		for j in range(0, grid_len):
			p = '(' + str(i) + ',' + str(j) + ')'
			
			if p in url_data["grid"]:
				output_str += url_data["grid"][p]
			else:
				output_str += " "
			output_str += " "
		print output_str


