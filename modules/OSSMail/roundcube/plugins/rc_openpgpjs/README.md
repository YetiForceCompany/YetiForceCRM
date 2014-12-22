rc_openpgpjs
================
[![Flattr this git repo](http://api.flattr.com/button/flattr-badge-large.png)](https://flattr.com/submit/auto?user_id=qnrq&url=https://github.com/qnrq/rc_openpgpjs/&title=rc_openpgpjs&language=&tags=github&category=software)

rc_openpgpjs is an open source (GPLv2) extension adding OpenPGPs functionality
to the Roundcube webmail project. rc_openpgpjs is written with the intention to
be as user friendly as possible for everyday PGP use. See
[Why do you need PGP?][why], [OpenPGP.js][openpgpjs] and [Roundcube][roundcube]
for more info.

Features
--------
- E-mail PGP signing
- E-mail PGP encryption and decryption
- Secure key storage (HTML5 local storage)
- Key generation
- Key lookups against PGP Secure Key Servers

Key storage
-----------
The keys are stored client side using HTML5 web storage. Private keys are never
transferred from the user's local storage. Private and public keys can be
exported from the web storage and be used outside of Roundcube and equally
externally generated keys can be imported and used inside Roundcube.

Key lookups
-----------
Public keys can be imported from PGP Secure Key Servers, i.e. pool.sks-keyservers.net and
any other Public Key Server which follows the [OpenPGP HTTP Keyserver Protocol 
(HKP)][draft], i.e pgp.mit.edu.

Installation
------------
1. Copy plugin to 'plugins' folder
2. Add 'rc_openpgpjs' to plugins array in your Roundcube config (config/main.inc.php)

Contact
-------
For any bug reports or feature requests please refer to the [tracking system][issues].

Questions? Please see the [FAQ][faq].

[roundcube]: http://www.roundcube.net/
[openpgpjs]: https://openpgpjs.org/
[issues]: https://github.com/qnrq/rc_openpgpjs/issues
[why]: http://www.pgpi.org/doc/whypgp/en/
[draft]: https://tools.ietf.org/html/draft-shaw-openpgp-hkp-00
[faq]: https://github.com/qnrq/rc_openpgpjs/wiki/FAQ
