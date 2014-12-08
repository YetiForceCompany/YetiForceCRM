#!/usr/bin/env python
# -*- coding: utf-8 -*-
###############################################################################
# This HTTPD acts bridge to make OpenPGP functionality accessible for         #
# JavaScript through locally installed GnuPG binaries and keyrings.           #
#                                                                             #
# Copyright (C) Niklas Femerstrand <nik@qnrq.se>                              #
#                                                                             #
# This program is free software; you can redistribute it and/or modify it     #
# under the terms of the GNU General Public License version 2 as published by #
# the Free Software Foundation.                                               #
#                                                                             #
# This program is distributed in the hope that it will be useful, but WITHOUT #
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or       #
# FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for    #
# more details.                                                               #
#                                                                             #
# You should have received a copy of the GNU General Public License along     #
# with this program; if not, write to the Free Software Foundation, Inc.,     #
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.                 #
###############################################################################
# iM4G1N3 A FR33 W0RLD Wh3R3 0n3 W0uLDN'T N33D K0P1R19H7 2 S4Y N0 2 K0PiFi9H7 #
###############################################################################

import re, socket, ssl, sys, gnupg, json
import _thread as thread
from os.path import expanduser
import urllib.parse

home = expanduser("~")
home += "/.gnupg/"
gpg = gnupg.GPG(gnupghome = home)
gpg.encoding = "utf-8"

def deal_with_client(connstream):
	data = connstream.recv(8192).decode("utf-8")
	m = re.search("^(GET|POST)", data)
	if not m:
		data += connstream.recv(8192).decode("utf-8")

	while data:
		dd      = data.split("\n")
		cmdstr  = ""
		origin  = ""
		referer = ""

		for header in dd:
			print(header)
			m = re.search("^Origin: (.*)$", header)
			if m:
				origin = m.groups()[0].rstrip()

			# Fetch domain from referer header, some browsers always provide origin.
			m = re.search("^Referer: (.*)/login", header)
			if m:
				referer = m.groups()[0].rstrip()

			if "=" in header:
				cmdstr = header

		# Fall back on referer
		if not origin:
			origin = referer

		if not do_something(connstream, data, origin, cmdstr):
			break

def do_something(connstream, data, origin, cmdstr):
	allow_request = ""
	response = ""

	cors = ["null"]
	with open("accepted_domains.txt", 'r') as f:
		lines = f.readlines()
		for line in lines:
			if not line.startswith("#"):
				cors.append(line.replace("\r", "").replace("\n", ""))

	o = origin.replace("http://", "").replace("https://", "")
	if o not in cors:
		response = "Illegal origin"
	else:
		allow_request = 1
		response = do_gpg(cmdstr)

	content_length = str(len(response))

	if allow_request:
		connstream.write("HTTP/1.1 200 OK\r\n".encode())
	else:
		connstream.write("HTTP/1.1 403 Forbidden\r\n".encode())
	connstream.write(("Content-Length: " + content_length + "\r\n").encode())
	connstream.write("Content-Type: text/html\r\n".encode())
	connstream.write("Server: pygpghttpd\r\n".encode())

	if allow_request:
		connstream.write(("Access-Control-Allow-Origin: " + origin + "\r\n").encode())
	connstream.write("\r\n".encode())
	try:
		connstream.write(response.encode())
	except:
		connstream.write(response) # already utf8

def do_gpg(cmdstr):
	c       = {}
	cmds_ok = ["keygen", "keylist", "keydel", "keyexport", "keyimport", "encrypt", "decrypt", "sign", "verify"]

	if "&" in cmdstr: # Multiple params
		cmds = cmdstr.split("&")
		for cmd in cmds:
			cc = cmd.split("=")
			if cc[0] and cc[1]:
				c[cc[0]] = urllib.parse.unquote(cc[1])
	else: # Single param
		cc = cmdstr.split("=")
		if cc[0] and cc[1]:
			c[cc[0]] = urllib.parse.unquote(cc[1])

	if "cmd" not in c:
		return("Missing cmdstr for GPG op")

	for cmd_ok in cmds_ok:
		if cmd_ok == c["cmd"]:
			return(globals()[c["cmd"]](c))

	return("Unsupported cmdstr")

def keylist(cmd):
	if "private" not in cmd:
		cmd["private"] = False
	else:
		if cmd["private"] == "true" or cmd["private"] == "1":
			cmd["private"] = True
		else:
			cmd["private"] = False

	keys = gpg.list_keys(cmd["private"])
	return(json.dumps(keys))

def keygen(cmd):
	required    = ["type", "length", "name", "email", "passphrase"]
	key_types   = ["RSA", "DSA"]
	key_lengths = ["2048", "4096"]

	for req in required:
		if req not in cmd:
			return("Insufficient parameters: %s" % (req))

	if cmd["type"] not in key_types:
		return("Incorrect: type")

	if cmd["length"] not in key_lengths:
		return("Incorrect: length")

	input_data = gpg.gen_key_input(key_type = cmd["type"], key_length = cmd["length"], name_real = cmd["name"], name_email = cmd["email"], passphrase = cmd["passphrase"], name_comment = "pygpghttpd")
	key = gpg.gen_key(input_data)

	if key:
		return("1")
	return("0")

def keydel(cmd):
	if "private" not in cmd:
		cmd["private"] = False
	else:
		if cmd["private"] == "true" or cmd["private"] == "1":
			cmd["private"] = True
		else:
			cmd["private"] = False

	if "fingerprint" not in cmd:
		return("Insufficient parameters: fingerprint")

	return(str(gpg.delete_keys(cmd["fingerprint"], cmd["private"])))

# Allow only pubkey export for security
def keyexport(cmd):
	if "id" not in cmd:
		return("Insufficient parameters: id")

	return(gpg.export_keys(cmd["id"]))

def keyimport(cmd):
	if "key" not in cmd:
		return("Insufficient parameters: key")

	return(gpg.import_keys(cmd["key"]))

def encrypt(cmd):
	required = ["data", "recipients"]

	for req in required:
		if req not in cmd:
			return("Insufficient parameters: %s" % (req))

	try:
		cmd["sign"]
	except:
		cmd["sign"] = None
		cmd["passphrase"] = None
		pass
	else:
		if "passphrase" not in cmd:
			return("Insufficient parameters: passphrase (needed since sign is set")

	encrypted = gpg.encrypt(cmd["data"], recipients = cmd["recipients"], sign = cmd["sign"], passphrase = cmd["passphrase"])
	print(encrypted.stderr)
	return(str(encrypted))

def decrypt(cmd):
	required = ["data", "passphrase"]

	for req in required:
		if req not in cmd:
			return("Insufficient parameters: %s" % (req))

	# TODO s/+/ / on these specific lines + comment
	cmd["data"] = cmd["data"].replace("BEGIN+PGP+MESSAGE", "BEGIN PGP MESSAGE")
	cmd["data"] = cmd["data"].replace("END+PGP+MESSAGE", "END PGP MESSAGE")
	cmd["data"] = cmd["data"].replace("Version:+GnuPG+v2.0.20+(GNU/Linux)", "Version: GnuPG v2.0.20 (GNU/Linux)")

	decrypted = gpg.decrypt(message = cmd["data"], passphrase = cmd["passphrase"])
	print(decrypted.stderr)
	return(decrypted.data)

def sign(cmd):
	required = ["data", "keyid", "passphrase"]

	for req in required:
		if req not in cmd:
			return("Insufficient parameters: %s" % (req))

	return(gpg.sign(cmd["data"], keyid = cmd["keyid"], passphrase = cmd["passphrase"]))

def verify(cmd):
	if "data" not in cmd:
		return("Insufficient parameters: data")

	return(gpg.verify(cmd["data"]))

def threadHandler(client, addr):
	connstream = ""

	try:
		connstream = ssl.wrap_socket(client,
									 server_side = True,
									 certfile = "./cert.pem",
									 keyfile = "./cert.pem",
									 ssl_version = ssl.PROTOCOL_SSLv23)
	except Exception as exception:
		print(exception)

	if not connstream:
		return
	try:
		deal_with_client(connstream)
	finally:
		connstream.shutdown(socket.SHUT_RDWR)

try:
	sock = socket.socket(socket.AF_INET)
	sock.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
	sock.bind(("127.0.0.1", 11337))
	sock.listen(0)
except socket.error:
	print("Failed to create socket")
	sys.exit()

while True:
	client, addr = sock.accept()
	thread.start_new_thread(threadHandler, (client, addr))
