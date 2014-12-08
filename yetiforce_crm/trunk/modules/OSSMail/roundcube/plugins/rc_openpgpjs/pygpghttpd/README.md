pygpghttpd
==========
pygpghttpd exposes an API enabling GnuPG's cryptographic functionality to be
used in web browsers and other software which allows HTTP requests. pygpghttpd
runs on the client's localhost and allows calling GnuPG binaries from the
user's browser securely without exposing cryptograhically sensitive data to
hostile environments. pygpghttpd bridges the required elements of GnuPG to HTTP
allowing its cryptographic functionality to be called without the need to trust
JavaScript based PGP/GPG ports. As pygpghttpd calls local GnuPG binaries it is
also using local keyrings and relying on it entirely for strength. In short
pygpghttpd is just a dummy task router between browser and GnuPG binary.

How it works
------------
pygpghttpd acts as a HTTPS server listening on port 11337 for POST requests
containing operation commands and parameters to execute. When a request is
received it checks the "Origin", or if missing the "Referer", HTTP header to
find out which domain served the content that is contacting it. It then
detects if the domain is added to the "accepted\_domains.txt" file by the user
to ensure that it is only operational for pre accepted domains. If the
referring domain is accepted it treats the request and serves the result
from the local GnuPG binary to the client. In the response a [Cross-origin
resource sharing HTTP][cors] header is sent to inform the user's browser that
the request should be permitted. If the referring domain is missing from
accepted_domains.txt the user's browser forbids the request in accordance with
the [same origin security policy][same origin].

The HTTPS certificate used by pygpghttpd is self signed and is not used with
the intention to enhance security since all traffic is isolated to the
local network interface. It uses HTTPS to ensure that both HTTPS and HTTP
delivered content can interact with it.

pygpghttpd exposes metadata for both private and public keys but only
allows public keys to be exported from the local keyring. The metadata for private keys is
enough for performing cryptographic actions. Complete keypairs can be
generated and imported into the local keyring.

For example, generating a keypair with cURL:

*curl -k --data "cmd=keygen&type=RSA&length=2048&name=Alice&email=alice@foo.com&passphrase=foobar" -H "Origin: https://accepted.domain.com" https://localhost:11337/*

Please see the [API documentation][api] for full details.

Installation
------------
Compile and install all dependencies. If you are running Windows then check the
[python-gnupg][python-gnupg] documentation notes for Windows usage. Windows
binaries will be distributed later.

Before using pygphttpd with content delivered from other than localhost you
need to open the accepted\_domains.txt file and add them separated by newlines.

After you have started pygphttpd ensure that the browser that you are using
supports TLSv1.1 or TLSv1.2, otherwise you'll get SSL failures. Then visit
https://localhost:11337/ and add a permanent exception for the used certificate
so your browser can communicate with it properly. If you for some reason wish
to replace the included certificate file you can do that by:

*openssl req -new -x509 -days 365 -nodes -out cert.pem -keyout cert.pem*

Dependencies
------------

[Python](http://www.python.org/)

[GnuPG](http://www.gnupg.org/)

[python-gnupg](https://code.google.com/p/python-gnupg/)

[cors]: https://en.wikipedia.org/wiki/Cross-origin_resource_sharing
[same origin]: https://en.wikipedia.org/wiki/Same_origin_policy
[python-gnupg]: https://pythonhosted.org/python-gnupg/
[api]: https://raw.github.com/qnrq/pygphttpd/master/DOCS/API.txt
