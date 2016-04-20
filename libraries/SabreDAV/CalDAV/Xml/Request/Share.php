<?php

namespace Sabre\CalDAV\Xml\Request;

use Sabre\Xml\Reader;
use Sabre\Xml\XmlDeserializable;
use Sabre\CalDAV\Plugin;

/**
 * Share POST request parser
 *
 * This class parses the share POST request, as defined in:
 *
 * http://svn.calendarserver.org/repository/calendarserver/CalendarServer/trunk/doc/Extensions/caldav-sharing.txt
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class Share implements XmlDeserializable {

    /**
     * The list of new people added or updated.
     *
     * Every element has the following keys:
     * 1. href - An email address
     * 2. commonName - Some name
     * 3. summary - An optional description of the share
     * 4. readOnly - true or false
     *
     * @var array
     */
    public $set = [];

    /**
     * List of people removed from the share list.
     *
     * The list is a flat list of email addresses (including mailto:).
     *
     * @var array
     */
    public $remove = [];

    /**
     * Constructor
     *
     * @param array $set
     * @param array $remove
     */
    function __construct(array $set, array $remove) {

        $this->set = $set;
        $this->remove = $remove;

    }

    /**
     * The deserialize method is called during xml parsing.
     *
     * This method is called statictly, this is because in theory this method
     * may be used as a type of constructor, or factory method.
     *
     * Often you want to return an instance of the current class, but you are
     * free to return other data as well.
     *
     * You are responsible for advancing the reader to the next element. Not
     * doing anything will result in a never-ending loop.
     *
     * If you just want to skip parsing for this element altogether, you can
     * just call $reader->next();
     *
     * $reader->parseInnerTree() will parse the entire sub-tree, and advance to
     * the next element.
     *
     * @param Reader $reader
     * @return mixed
     */
    static function xmlDeserialize(Reader $reader) {

        $elems = $reader->parseInnerTree([
            '{' . Plugin::NS_CALENDARSERVER . '}set'    => 'Sabre\\Xml\\Element\\KeyValue',
            '{' . Plugin::NS_CALENDARSERVER . '}remove' => 'Sabre\\Xml\\Element\\KeyValue',
        ]);

        $set = [];
        $remove = [];

        foreach ($elems as $elem) {
            switch ($elem['name']) {

                case '{' . Plugin::NS_CALENDARSERVER . '}set' :
                    $sharee = $elem['value'];

                    $sumElem = '{' . Plugin::NS_CALENDARSERVER . '}summary';
                    $commonName = '{' . Plugin::NS_CALENDARSERVER . '}common-name';

                    $set[] = [
                        'href'       => $sharee['{DAV:}href'],
                        'commonName' => isset($sharee[$commonName]) ? $sharee[$commonName] : null,
                        'summary'    => isset($sharee[$sumElem]) ? $sharee[$sumElem] : null,
                        'readOnly'   => !array_key_exists('{' . Plugin::NS_CALENDARSERVER . '}read-write', $sharee),
                    ];
                    break;

                case '{' . Plugin::NS_CALENDARSERVER . '}remove' :
                    $remove[] = $elem['value']['{DAV:}href'];
                    break;

            }
        }

        return new self($set, $remove);

    }

}
