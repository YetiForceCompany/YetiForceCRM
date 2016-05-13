<?php

namespace Sabre\CalDAV\Xml\Property;

use Sabre\Xml\Element;
use Sabre\Xml\Reader;
use Sabre\Xml\Writer;
use Sabre\CalDAV\Plugin;
use Sabre\CalDAV\SharingPlugin;

/**
 * Invite property
 *
 * This property encodes the 'invite' property, as defined by
 * the 'caldav-sharing-02' spec, in the http://calendarserver.org/ns/
 * namespace.
 *
 * @see https://trac.calendarserver.org/browser/CalendarServer/trunk/doc/Extensions/caldav-sharing-02.txt
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class Invite implements Element {

    /**
     * The list of users a calendar has been shared to.
     *
     * @var array
     */
    protected $users;

    /**
     * The organizer contains information about the person who shared the
     * object.
     *
     * @var array
     */
    protected $organizer;

    /**
     * Creates the property.
     *
     * Users is an array. Each element of the array has the following
     * properties:
     *
     *   * href - Often a mailto: address
     *   * commonName - Optional, for example a first and lastname for a user.
     *   * status - One of the SharingPlugin::STATUS_* constants.
     *   * readOnly - true or false
     *   * summary - Optional, description of the share
     *
     * The organizer key is optional to specify. It's only useful when a
     * 'sharee' requests the sharing information.
     *
     * The organizer may have the following properties:
     *   * href - Often a mailto: address.
     *   * commonName - Optional human-readable name.
     *   * firstName - Optional first name.
     *   * lastName - Optional last name.
     *
     * If you wonder why these two structures are so different, I guess a
     * valid answer is that the current spec is still a draft.
     *
     * @param array $users
     */
    function __construct(array $users, array $organizer = null) {

        $this->users = $users;
        $this->organizer = $organizer;

    }

    /**
     * Returns the list of users, as it was passed to the constructor.
     *
     * @return array
     */
    function getValue() {

        return $this->users;

    }

    /**
     * The xmlSerialize metod is called during xml writing.
     *
     * Use the $writer argument to write its own xml serialization.
     *
     * An important note: do _not_ create a parent element. Any element
     * implementing XmlSerializble should only ever write what's considered
     * its 'inner xml'.
     *
     * The parent of the current element is responsible for writing a
     * containing element.
     *
     * This allows serializers to be re-used for different element names.
     *
     * If you are opening new elements, you must also close them again.
     *
     * @param Writer $writer
     * @return void
     */
    function xmlSerialize(Writer $writer) {

        $cs = '{' . Plugin::NS_CALENDARSERVER . '}';

        if (!is_null($this->organizer)) {

            $writer->startElement($cs . 'organizer');
            $writer->writeElement('{DAV:}href', $this->organizer['href']);

            if (isset($this->organizer['commonName']) && $this->organizer['commonName']) {
                $writer->writeElement($cs . 'common-name', $this->organizer['commonName']);
            }
            if (isset($this->organizer['firstName']) && $this->organizer['firstName']) {
                $writer->writeElement($cs . 'first-name', $this->organizer['firstName']);
            }
            if (isset($this->organizer['lastName']) && $this->organizer['lastName']) {
                $writer->writeElement($cs . 'last-name', $this->organizer['lastName']);
            }
            $writer->endElement(); // organizer

        }

        foreach ($this->users as $user) {

            $writer->startElement($cs . 'user');
            $writer->writeElement('{DAV:}href', $user['href']);
            if (isset($user['commonName']) && $user['commonName']) {
                $writer->writeElement($cs . 'common-name', $user['commonName']);
            }
            switch ($user['status']) {

                case SharingPlugin::STATUS_ACCEPTED :
                    $writer->writeElement($cs . 'invite-accepted');
                    break;
                case SharingPlugin::STATUS_DECLINED :
                    $writer->writeElement($cs . 'invite-declined');
                    break;
                case SharingPlugin::STATUS_NORESPONSE :
                    $writer->writeElement($cs . 'invite-noresponse');
                    break;
                case SharingPlugin::STATUS_INVALID :
                    $writer->writeElement($cs . 'invite-invalid');
                    break;
            }

            $writer->startElement($cs . 'access');
            if ($user['readOnly']) {
                $writer->writeElement($cs . 'read');
            } else {
                $writer->writeElement($cs . 'read-write');
            }
            $writer->endElement(); // access

            if (isset($user['summary']) && $user['summary']) {
                $writer->writeElement($cs . 'summary', $user['summary']);
            }

            $writer->endElement(); //user

        }

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

        $cs = '{' . Plugin::NS_CALENDARSERVER . '}';

        $users = [];

        foreach ($reader->parseInnerTree() as $elem) {

            if ($elem['name'] !== $cs . 'user')
                continue;

            $user = [
                'href'       => null,
                'commonName' => null,
                'readOnly'   => null,
                'summary'    => null,
                'status'     => null,
            ];

            foreach ($elem['value'] as $userElem) {

                switch ($userElem['name']) {
                    case $cs . 'invite-accepted' :
                        $user['status'] = SharingPlugin::STATUS_ACCEPTED;
                        break;
                    case $cs . 'invite-declined' :
                        $user['status'] = SharingPlugin::STATUS_DECLINED;
                        break;
                    case $cs . 'invite-noresponse' :
                        $user['status'] = SharingPlugin::STATUS_NORESPONSE;
                        break;
                    case $cs . 'invite-invalid' :
                        $user['status'] = SharingPlugin::STATUS_INVALID;
                        break;
                    case '{DAV:}href' :
                        $user['href'] = $userElem['value'];
                        break;
                    case $cs . 'common-name' :
                        $user['commonName'] = $userElem['value'];
                        break;
                    case $cs . 'access' :
                        foreach ($userElem['value'] as $accessHref) {
                            if ($accessHref['name'] === $cs . 'read') {
                                $user['readOnly'] = true;
                            }
                        }
                        break;
                    case $cs . 'summary' :
                        $user['summary'] = $userElem['value'];
                        break;

                }

            }
            if (!$user['status']) {
                throw new \InvalidArgumentException('Every user must have one of cs:invite-accepted, cs:invite-declined, cs:invite-noresponse or cs:invite-invalid');
            }

            $users[] = $user;

        }

        return new self($users);

    }
}
