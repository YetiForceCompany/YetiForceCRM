#!@prefix@/bin/php -Cq
<?php
/**
* PHAIL - stands for PHP Mail
* @author Tomas V.V.Cox <cox@idecnet.com>
*/
require_once 'Mail.php';
require_once 'Mail/mime.php';
require_once 'Console/Getopt.php';

$argv = Console_Getopt::readPHPArgv();
$opts = Console_Getopt::getOpt($argv, 'f:c:s:t:a:b:');
if (PEAR::isError($opts)) {
    usage($opts->getMessage());
}

PEAR::setErrorHandling(PEAR_ERROR_DIE);
$mime = &new Mail_Mime;
foreach ($opts[0] as $opt) {
    $param = $opt[1];
    switch ($opt[0]) {
        case 'f':
            $headers['From'] = $param; break;
        case 'c':
            $headers['Cc'] = $param; break;
        case 's':
            $headers['Subject'] = $param; break;
        case 't':
            $to = $param; break;
        case 'a':
            $mime->addAttachment($param); break;
        case 'b':
            $isfile = @is_file($param) ? true : false;
            $mime->setTXTBody($param, $isfile); break;
    }
}

$mbody = $mime->get();
$headers = $mime->headers($headers);
$mail =& Mail::factory('mail');
$mail->send($to, $headers, $mbody);

function usage($error)
{
    die($error);
}
?>