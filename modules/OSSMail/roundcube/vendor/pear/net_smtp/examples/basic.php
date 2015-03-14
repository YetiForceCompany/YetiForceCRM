<?php

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
$smtp->auth('username','password');
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
