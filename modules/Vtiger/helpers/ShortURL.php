<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Helper methods to work with ShortURLs
 */
class Vtiger_ShortURL_Helper {
    /*
     * @param options array(
     * 'handler_path'     => 'path/to/TrackerClass.php',
     * 'handler_class'    => 'TrackerClass',
     * 'handler_function' => 'trackingFunction',
     * 'handler_data'     => array(
     * 			'key1' => 'value1',
     * 			'key2' => 'value2'
     * 		)
     * 	));
     */

    static function generateURL(array $options) {
        global $site_URL;
        if (!isset($options['onetime']))
            $options['onetime'] = 0;
        $uid = self::generate($options);
        return rtrim($site_URL, '/') . "/shorturl.php?id=" . $uid;
    }

    static function generate(array $options) {
        $db = PearDatabase::getInstance();

        // TODO Review the random unique ID generation
        $uid = uniqid("", true);

        $handlerPath = $options['handler_path'];
        $handlerClass = $options['handler_class'];
        $handlerFn = $options['handler_function'];
        $handlerData = $options['handler_data'];

        if (empty($handlerPath) || empty($handlerClass) || empty($handlerFn)) {
            throw new Exception("Invalid options for generate");
        }

        $sql = "INSERT INTO vtiger_shorturls(uid, handler_path, handler_class, handler_function, handler_data, onetime) VALUES (?,?,?,?,?,?)";
        $params = array($uid, $handlerPath, $handlerClass, $handlerFn, json_encode($handlerData), $options['onetime']);

        $db->pquery($sql, $params);
        return $uid;
    }

    static function handle($uid) {
        $db = PearDatabase::getInstance();

        $rs = $db->pquery('SELECT * FROM vtiger_shorturls WHERE uid=?', array($uid));
        if ($rs && $db->num_rows($rs)) {
            $record = $db->fetch_array($rs);
            $handlerPath = decode_html($record['handler_path']);
            $handlerClass = decode_html($record['handler_class']);
            $handlerFn = decode_html($record['handler_function']);
            $handlerData = json_decode(decode_html($record['handler_data']), true);

            checkFileAccessForInclusion($handlerPath);
            require_once $handlerPath;

            $handler = new $handlerClass();

            // Delete onetime URL
            if ($record['onetime'])
                $db->pquery('DELETE FROM vtiger_shorturls WHERE id=?', array($record['id']));
            call_user_func(array($handler, $handlerFn), $handlerData);
        } else {
            echo '<h3>Link you have used is invalid or has expired. .</h3>';
        }
    }

    /**
     * Function will send tracker image of 1X1 pixel transparent Image 
     */
    static function sendTrackerImage() {
        header('Content-Type: image/png');
        echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=');
    }

    function getInstance($id) {
        $db = PearDatabase::getInstance();
        $self = new self();
        $rs = $db->pquery('SELECT * FROM vtiger_shorturls WHERE uid=?', array($id));
        if ($rs && $db->num_rows($rs)) {
            $row = $db->fetch_array($rs);
            $self->id = $row['id'];
            $self->uid = $row['uid'];
            $self->handler_path = $row['handler_path'];
            $self->handler_class = $row['handler_class'];
            $self->handler_function = $row['handler_function'];
            $self->handler_data = json_decode(decode_html($row['handler_data']), true);
        }
        return $self;
    }

    function compareEquals($data) {
        $valid = true;
        if ($this->handler_data) {
            foreach ($this->handler_data as $key => $value) {
                if ($data[$key] != $value) {
                    $valid = false;
                    break;
                }
            }
        } else {
            $valid = false;
        }
        return $valid;
    }

    function delete() {
        $db = PearDatabase::getInstance();
        $db->pquery('DELETE FROM vtiger_shorturls WHERE id=?', array($this->id));
    }

}
