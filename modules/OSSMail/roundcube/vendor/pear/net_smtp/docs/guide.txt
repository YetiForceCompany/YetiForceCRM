======================
 The Net_SMTP Package
======================

--------------------
 User Documentation
--------------------

:Author:    Jon Parise
:Contact:   jon@php.net

.. contents:: Table of Contents
.. section-numbering::

Dependencies
============

The ``PEAR_Error`` Class
------------------------

The Net_SMTP package uses the `PEAR_Error`_ class for all of its `error
handling`_.

The ``Net_Socket`` Package
--------------------------

The Net_Socket_ package is used as the basis for all network communications.

The ``Auth_SASL`` Package
-------------------------

The `Auth_SASL`_ package is an optional dependency.  If it is available, the
Net_SMTP package will be able to support the DIGEST-MD5_ and CRAM-MD5_ SMTP
authentication methods.  Otherwise, only the LOGIN_ and PLAIN_ methods will
be available.

Error Handling
==============

All of the Net_SMTP class's public methods return a PEAR_Error_ object if an
error occurs.  The standard way to check for a PEAR_Error object is by using
`PEAR::isError()`_::

    if (PEAR::isError($error = $smtp->connect())) {
        die($error->getMessage());
    }

.. _PEAR::isError(): http://pear.php.net/manual/en/core.pear.pear.iserror.php

SMTP Authentication
===================

The Net_SMTP package supports the SMTP authentication standard (as defined
by RFC-2554_).  The Net_SMTP package supports the following authentication
methods, in order of preference:

.. _RFC-2554: http://www.ietf.org/rfc/rfc2554.txt

DIGEST-MD5
----------

The DIGEST-MD5 authentication method uses `RSA Data Security Inc.`_'s MD5
Message Digest algorithm.  It is considered the most secure method of SMTP
authentication.

**Note:** The DIGEST-MD5 authentication method is only supported if the
AUTH_SASL_ package is available.

.. _RSA Data Security Inc.: http://www.rsasecurity.com/

CRAM-MD5
--------

The CRAM-MD5 authentication method has been superseded by the DIGEST-MD5_
method in terms of security.  It is provided here for compatibility with
older SMTP servers that may not support the newer DIGEST-MD5 algorithm.

**Note:** The CRAM-MD5 authentication method is only supported if the
AUTH_SASL_ package is available.

LOGIN
-----

The LOGIN authentication method encrypts the user's password using the
Base64_ encoding scheme.  Because decrypting a Base64-encoded string is
trivial, LOGIN is not considered a secure authentication method and should
be avoided.

.. _Base64: http://www.php.net/manual/en/function.base64-encode.php

PLAIN
-----

The PLAIN authentication method sends the user's password in plain text.
This method of authentication is not secure and should be avoided.

Secure Connections
==================

If `secure socket transports`_ have been enabled in PHP, it is possible to
establish a secure connection to the remote SMTP server::

    $smtp = new Net_SMTP('ssl://mail.example.com', 465);

This example connects to ``mail.example.com`` on port 465 (a common SMTPS
port) using the ``ssl://`` transport.

.. _secure socket transports: http://www.php.net/transports

Sending Data
============

Message data is sent using the ``data()`` method.  The data can be supplied
as a single string or as an open file resource.

If a string is provided, it is passed through the `data quoting`_ system and
sent to the socket connection as a single block.  These operations are all
memory-based, so sending large messages may result in high memory usage.

If an open file resource is provided, the ``data()`` method will read the
message data from the file line-by-line.  Each chunk will be quoted and sent
to the socket connection individually, reducing the overall memory overhead of
this data sending operation.

Header data can be specified separately from message body data by passing it
as the optional second parameter to ``data()``.  This is especially useful
when an open file resource is being used to supply message data because it
allows header fields (like *Subject:*) to be built dynamically at runtime.

::

    $smtp->data($fp, "Subject: My Subject");

Data Quoting
============

By default, all outbound string data is quoted in accordance with SMTP
standards.  This means that all native Unix (``\n``) and Mac (``\r``) line
endings are converted to Internet-standard CRLF (``\r\n``) line endings.
Also, because the SMTP protocol uses a single leading period (``.``) to signal
an end to the message data, single leading periods in the original data
string are "doubled" (e.g. "``..``").

These string transformation can be expensive when large blocks of data are
involved.  For example, the Net_SMTP package is not aware of MIME parts (it
just sees the MIME message as one big string of characters), so it is not
able to skip non-text attachments when searching for characters that may
need to be quoted.

Because of this, it is possible to extend the Net_SMTP class in order to
implement your own custom quoting routine.  Just create a new class based on
the Net_SMTP class and reimplement the ``quotedata()`` method::

    require 'Net_SMTP.php';

    class Net_SMTP_custom extends Net_SMTP
    {
        function quotedata($data)
        {
            /* Perform custom data quoting */
        }
    }

Note that the ``$data`` parameter will be passed to the ``quotedata()``
function `by reference`_.  This means that you can operate directly on
``$data``.  It also the overhead of copying a large ``$data`` string to and
from the ``quotedata()`` method.

.. _by reference: http://www.php.net/manual/en/language.references.pass.php

Server Responses
================

The Net_SMTP package retains the server's last response for further
inspection.  The ``getResponse()`` method returns a 2-tuple (two element
array) containing the server's response code as an integer and the response's
arguments as a string.

Upon a successful connection, the server's greeting string is available via
the ``getGreeting()`` method.

Debugging
=========

The Net_SMTP package contains built-in debugging output routines (disabled by
default).  Debugging output must be explicitly enabled via the ``setDebug()``
method::

    $smtp->setDebug(true);

The debugging messages will be sent to the standard output stream by default.
If you need more control over the output, you can optionally install your own
debug handler.

::

    function debugHandler($smtp, $message)
    {
        echo "[$smtp->host] $message\n";
    }

    $smtp->setDebug(true, "debugHandler");


Examples
========

Basic Use
---------

The following script demonstrates how a simple email message can be sent
using the Net_SMTP package::

    require 'Net/SMTP.php';

    $host = 'mail.example.com';
    $from = 'user@example.com';
    $rcpt = array('recipient1@example.com', 'recipient2@example.com');
    $subj = "Subject: Test Message\n";
    $body = "Body Line 1\nBody Line 2";

    /* Create a new Net_SMTP object. */
    if (! ($smtp = new Net_SMTP($host))) {
        die("Unable to instantiate Net_SMTP object\n");
    }

    /* Connect to the SMTP server. */
    if (PEAR::isError($e = $smtp->connect())) {
        die($e->getMessage() . "\n");
    }

    /* Send the 'MAIL FROM:' SMTP command. */
    if (PEAR::isError($smtp->mailFrom($from))) {
        die("Unable to set sender to <$from>\n");
    }

    /* Address the message to each of the recipients. */
    foreach ($rcpt as $to) {
        if (PEAR::isError($res = $smtp->rcptTo($to))) {
            die("Unable to add recipient <$to>: " . $res->getMessage() . "\n");
        }
    }

    /* Set the body of the message. */
    if (PEAR::isError($smtp->data($subj . "\r\n" . $body))) {
        die("Unable to send data\n");
    }

    /* Disconnect from the SMTP server. */
    $smtp->disconnect();

.. _PEAR_Error: http://pear.php.net/manual/en/core.pear.pear-error.php
.. _Net_Socket: http://pear.php.net/package/Net_Socket
.. _Auth_SASL: http://pear.php.net/package/Auth_SASL

.. vim: tabstop=4 shiftwidth=4 softtabstop=4 expandtab textwidth=78 ft=rst:
