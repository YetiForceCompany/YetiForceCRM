<?php
//
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2001 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Tomas V.V.Cox <cox@idecnet.com>                             |
// |          Richard Heyes <richard@phpguru.org>                         |
// |                                                                      |
// +----------------------------------------------------------------------+
//
// $Id$

require_once 'PEAR.php';
require_once 'Mail/mimePart.php';

/*
* Mime mail composer class. Can handle: text and html bodies, embedded html
* images and attachments.
* Documentation and example of this class is avaible here:
* http://vulcanonet.com/soft/mime/
*
* @notes This class is based on HTML Mime Mail class from
*   Richard Heyes <richard.heyes@heyes-computing.net> which was based also
*   in the mime_mail.class by Tobias Ratschiller <tobias@dnet.it> and
*   Sascha Schumann <sascha@schumann.cx>.
*
* @author Tomas V.V.Cox <cox@idecnet.com>
* @author Richard Heyes <richard.heyes@heyes-computing.net>
* @package Mail
* @access public
*/
class Mail_mime extends Mail
{
    /**
    * Contains the plain text part of the email
    * @var string
    */
    var $_txtbody;
    /**
    * Contains the html part of the email
    * @var string
    */
    var $_htmlbody;
    /**
    * contains the mime encoded text
    * @var string
    */
    var $_mime;
    /**
    * contains the multipart content
    * @var string
    */
    var $_multipart;
    /**
    * list of the attached images
    * @var array
    */
    var $_html_images = array();
    /**
    * list of the attachements
    * @var array
    */
    var $_parts = array();
    /**
    * Build parameters
    * @var array
    */
    var $_build_params = array();
    /**
    * Headers for the mail
    * @var array
    */
    var $_headers = array();


    /*
    * Constructor function
    *
    * @access public
    */
    function Mail_mime($crlf = "\r\n")
    {
        if (!defined('MAIL_MIME_CRLF')) {
            define('MAIL_MIME_CRLF', $crlf, true);
        }

        $this->_boundary = '=_' . md5(uniqid(time()));

        $this->_build_params = array(
                                     'text_encoding' => '7bit',
                                     'html_encoding' => 'quoted-printable',
                                     '7bit_wrap'     => 998,
                                     'html_charset'  => 'iso-8859-1',
                                     'text_charset'  => 'iso-8859-1'
                                    );
    }

    /*
    * Accessor function to set the body text. Body text is used if
    * it's not an html mail being sent or else is used to fill the
    * text/plain part that emails clients who don't support
    * html should show.
    *
    * @param string $data Either a string or the file name with the
    *        contents
    * @param bool $isfile If true the first param should be trated
    *        as a file name, else as a string (default)
    * @return mixed true on success or PEAR_Error object
    * @access public
    */
    function setTXTBody($data, $isfile = false)
    {
        if (!$isfile) {
            $this->_txtbody = $data;
        } else {
            $cont = $this->_file2str($data);
            if (PEAR::isError($cont)) {
                return $cont;
            }
            $this->_txtbody = $cont;
        }
        return true;
    }

    /*
    * Adds a html part to the mail
    *
    * @param string $data Either a string or the file name with the
    *        contents
    * @param bool $isfile If true the first param should be trated
    *        as a file name, else as a string (default)
    * @return mixed true on success or PEAR_Error object
    * @access public
    */
    function setHTMLBody($data, $isfile = false)
    {
        if (!$isfile) {
            $this->_htmlbody = $data;
        } else {
            $cont = $this->_file2str($data);
            if (PEAR::isError($cont)) {
                return $cont;
            }
            $this->_htmlbody = $cont;
        }

        return true;
    }

    /*
    * Adds an image to the list of embedded images.
    *
    * @param string $file The image file name OR image data itself
    * @param string $c_type The content type
    * @param string $name The filename of the image. Only use if $file is the image data
    * @param bool $isfilename Whether $file is a filename or not. Defaults to true
    * @return mixed true on success or PEAR_Error object
    * @access public
    */
    function addHTMLImage($file, $c_type='application/octet-stream', $name = '', $isfilename = true)
    {
        $filedata = ($isfilename === true) ? $this->_file2str($file) : $file;
        $filename = ($isfilename === true) ? basename($file) : basename($name);
        if (PEAR::isError($filedata)) {
            return $filedata;
        }
        $this->_html_images[] = array(
                                      'body'   => $filedata,
                                      'name'   => $filename,
                                      'c_type' => $c_type,
                                      'cid'    => md5(uniqid(time()))
                                     );
        return true;
    }

    /*
    * Adds a file to the list of attachments.
    *
    * @param string $file The file name of the file to attach OR the file data itself
    * @param string $c_type The content type
    * @param string $name The filename of the attachment. Only use if $file is the file data
    * @param bool $isFilename Whether $file is a filename or not. Defaults to true
    * @return mixed true on success or PEAR_Error object
    * @access public
    */
    function addAttachment($file, $c_type='application/octet-stream', $name = '', $isfilename = true, $encoding = 'base64')
    {
        $filedata = ($isfilename === true) ? $this->_file2str($file) : $file;
        $filename = ($isfilename === true) ? basename($file) : basename($name);
        if (PEAR::isError($filedata)) {
            return $filedata;
        }

        $this->_parts[] = array(
                                'body'     => $filedata,
                                'name'     => $filename,
                                'c_type'   => $c_type,
                                'encoding' => $encoding
                               );
        return true;
    }

    /*
    * Returns the contents of the given file name as string
    * @param string $file_name
    * @return string
    * @acces private
    */
    function & _file2str($file_name)
    {
        if (!is_readable($file_name)) {
            return $this->raiseError('File is not readable ' . $file_name);
        }
        if (!$fd = fopen($file_name, 'rb')) {
            return $this->raiseError('Could not open ' . $file_name);
        }
        $cont = fread($fd, filesize($file_name));
        fclose($fd);
        return $cont;
    }

    /*
    * Adds a text subpart to the mimePart object and 
    * returns it during the build process.
    *
    * @param mixed    The object to add the part to, or
    *                 null if a new object is to be created.
    * @param string   The text to add.
    * @return object  The text mimePart object
    * @access private
    */
    function &_addTextPart(&$obj, $text){

        $params['content_type'] = 'text/plain';
        $params['encoding']     = $this->_build_params['text_encoding'];
        $params['charset']      = $this->_build_params['text_charset'];
        if (is_object($obj)) {
            return $obj->addSubpart($text, $params);
        } else {
            return new Mail_mimePart($text, $params);
        }
    }

    /*
    * Adds a html subpart to the mimePart object and
    * returns it during the build process.
    *
    * @param mixed    The object to add the part to, or
    *                 null if a new object is to be created.
    * @return object  The html mimePart object
    * @access private
    */
    function &_addHtmlPart(&$obj){

        $params['content_type'] = 'text/html';
        $params['encoding']     = $this->_build_params['html_encoding'];
        $params['charset']      = $this->_build_params['html_charset'];
        if (is_object($obj)) {
            return $obj->addSubpart($this->_htmlbody, $params);
        } else {
            return new Mail_mimePart($this->_htmlbody, $params);
        }
    }

    /*
    * Creates a new mimePart object, using multipart/mixed as
    * the initial content-type and returns it during the
    * build process.
    *
    * @return object  The multipart/mixed mimePart object
    * @access private
    */
    function &_addMixedPart(){

        $params['content_type'] = 'multipart/mixed';
        return new Mail_mimePart('', $params);
    }

    /*
    * Adds a multipart/alternative part to a mimePart
    * object, (or creates one), and returns it  during
    * the build process.
    *
    * @param mixed    The object to add the part to, or
    *                 null if a new object is to be created.
    * @return object  The multipart/mixed mimePart object
    * @access private
    */
    function &_addAlternativePart(&$obj){

        $params['content_type'] = 'multipart/alternative';
        if (is_object($obj)) {
            return $obj->addSubpart('', $params);
        } else {
            return new Mail_mimePart('', $params);
        }
    }

    /*
    * Adds a multipart/related part to a mimePart
    * object, (or creates one), and returns it  during
    * the build process.
    *
    * @param mixed    The object to add the part to, or
    *                 null if a new object is to be created.
    * @return object  The multipart/mixed mimePart object
    * @access private
    */
    function &_addRelatedPart(&$obj){

        $params['content_type'] = 'multipart/related';
        if (is_object($obj)) {
            return $obj->addSubpart('', $params);
        } else {
            return new Mail_mimePart('', $params);
        }
    }

    /*
    * Adds an html image subpart to a mimePart object
    * and returns it during the build process.
    *
    * @param  object  The mimePart to add the image to
    * @param  array   The image information
    * @return object  The image mimePart object
    * @access private
    */
    function &_addHtmlImagePart(&$obj, $value){

        $params['content_type'] = $value['c_type'];
        $params['encoding']     = 'base64';
        $params['disposition']  = 'inline';
        $params['dfilename']    = $value['name'];
        $params['cid']          = $value['cid'];
        $obj->addSubpart($value['body'], $params);
    }

    /*
    * Adds an attachment subpart to a mimePart object
    * and returns it during the build process.
    *
    * @param  object  The mimePart to add the image to
    * @param  array   The attachment information
    * @return object  The image mimePart object
    * @access private
    */
    function &_addAttachmentPart(&$obj, $value){

        $params['content_type'] = $value['c_type'];
        $params['encoding']     = $value['encoding'];
        $params['disposition']  = 'attachment';
        $params['dfilename']    = $value['name'];
        $obj->addSubpart($value['body'], $params);
    }

    /*
    * Builds the multipart message from the list ($this->_parts) and
    * returns the mime content.
    *
    * @param  array  Build parameters that change the way the email
    *                is built. Should be associative. Can contain:
    *                text_encoding  -  What encoding to use for plain text
    *                                  Default is 7bit
    *                html_encoding  -  What encoding to use for html
    *                                  Default is quoted-printable
    *                7bit_wrap      -  Number of characters before text is
    *                                  wrapped in 7bit encoding
    *                                  Default is 998
    *                html_charset   -  The character set to use for html.
    *                                  Default is iso-8859-1
    *                text_charset   -  The character set to use for text.
    *                                  Default is iso-8859-1
    * @return string The mime content
    * @access public
    */
    function &get($params = null)
    {
        if (isset($build_params)) {
            while (list($key, $value) = each($build_params)) {
                $this->_build_params[$key] = $value;
            }
        }

        if (!empty($this->_html_images) AND isset($this->_htmlbody)) {
            foreach ($this->_html_images as $value) {
                $this->_htmlbody = str_replace($value['name'], 'cid:'.$value['cid'], $this->_htmlbody);
            }
        }

        $null        = null;
        $attachments = !empty($this->_parts)                ? TRUE : FALSE;
        $html_images = !empty($this->_html_images)          ? TRUE : FALSE;
        $html        = !empty($this->_htmlbody)             ? TRUE : FALSE;
        $text        = (!$html AND !empty($this->_txtbody)) ? TRUE : FALSE;

        switch (TRUE) {
            case $text AND !$attachments:
                $message =& $this->_addTextPart($null, $this->text);
                break;

            case !$text AND !$html AND $attachments:
                $message =& $this->_addMixedPart();

                for ($i = 0; $i < count($this->_parts); $i++) {
                    $this->_addAttachmentPart($message, $this->_parts[$i]);
                }
                break;

            case $text AND $attachments:
                $message =& $this->_addMixedPart();
                $this->_addTextPart($message, $this->_txtbody);

                for ($i = 0; $i < count($this->_parts); $i++) {
                    $this->_addAttachmentPart($message, $this->_parts[$i]);
                }
                break;

            case $html AND !$attachments AND !$html_images:
                if (isset($this->_txtbody)) {
                    $message =& $this->_addAlternativePart($null);
   	                $this->_addTextPart($message, $this->_txtbody);
					$this->_addHtmlPart($message);
                    
                } else {
                    $message =& $this->_addHtmlPart($null);
                }
                break;

            case $html AND !$attachments AND $html_images:
                if (isset($this->_txtbody)) {
                    $message =& $this->_addAlternativePart($null);
                    $this->_addTextPart($message, $this->_txtbody);
                    $related =& $this->_addRelatedPart($message);
                } else {
                    $message =& $this->_addRelatedPart($null);
					$related =& $message;
                }
                $this->_addHtmlPart($related);
                for ($i = 0; $i < count($this->_html_images); $i++) {
                    $this->_addHtmlImagePart($related, $this->_html_images[$i]);
                }
                break;

            case $html AND $attachments AND !$html_images:
                $message =& $this->_addMixedPart();
                if (isset($this->_txtbody)) {
                    $alt =& $this->_addAlternativePart($message);
                    $this->_addTextPart($alt, $this->_txtbody);
                    $this->_addHtmlPart($alt);
                } else {
                    $this->_addHtmlPart($message);
                }
                for ($i = 0; $i < count($this->_parts); $i++) {
                    $this->_addAttachmentPart($message, $this->_parts[$i]);
                }
                break;

            case $html AND $attachments AND $html_images:
                $message =& $this->_addMixedPart();
                if (isset($this->_txtbody)) {
                    $alt =& $this->_addAlternativePart($message);
                    $this->_addTextPart($alt, $this->_txtbody);
                    $rel =& $this->_addRelatedPart($alt);
                } else {
                    $rel =& $this->_addRelatedPart($message);
                }
                $this->_addHtmlPart($rel);
                for ($i = 0; $i < count($this->_html_images); $i++) {
                    $this->_addHtmlImagePart($rel, $this->_html_images[$i]);
                }
                for ($i = 0; $i < count($this->_parts); $i++) {
                    $this->_addAttachmentPart($message, $this->_parts[$i]);
                }
                break;

        }

        if (isset($message)) {
            $output = $message->encode();
            $this->_headers = $output['headers'];

            return $output['body'];

        } else {
            return FALSE;
        }
    }


    /*
    * Returns an array with the headers needed to prepend to the email
    * (MIME-Version and Content-Type). Format of argument is:
    * $array['header-name'] = 'header-value';
    *
    * @param  array Assoc array with any extra headers. Optional.
    * @return array Assoc array with the mime headers
    * @access public
    */
    function & headers($xtra_headers = null)
    {
        // Content-Type header should already be present, 
        // So just add mime version header
        $headers['MIME-Version'] = '1.0';
        if (isset($xtra_headers)) {
            $headers = array_merge($headers, $xtra_headers);
        }
        $this->_headers = array_merge($headers, $this->_headers);

        return $this->_headers;
    }
}
?>