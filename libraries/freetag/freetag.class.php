<?php
/**
 *  Gordon Luk's Freetag - Generalized Open Source Tagging and Folksonomy.
 *  Copyright (C) 2004-2005 Gordon D. Luk <gluk AT getluky DOT net>
 *
 *  Released under both BSD license and Lesser GPL library license.  Whenever
 *  there is any discrepancy between the two licenses, the BSD license will
 *  take precedence. See License.txt.  
 *
 */
/**
 *  Freetag API Implementation
 *
 *  Freetag is a generic PHP class that can hook-in to existing database
 *  schemas and allows tagging of content within a social website. It's fun,
 *  fast, and easy!  Try it today and see what all the folksonomy fuss is
 *  about.
 * 
 *  Contributions and/or donations are welcome.
 *
 *  Author: Gordon Luk
 *  http://www.getluky.net
 *  
 *  Version: 0.240
 *  Last Updated: 12/26/2005 
 * 
 */ 

class freetag {

	/**#@+
	 *  @access private
	 *  @param string
	 */ 
	/**#@-*/

	/**
	 * @access private
	 * @param ADOConnection The ADODB Database connection instance.
	 */
	//var $_db;
	/**
	 * @access private
	 * @param bool Prints out limited debugging information if true, not fully implemented yet.
	 */
	var $_debug = FALSE;
	/**
	 * @access private
	 * @param string The prefix of freetag database vtiger_tables.
	 */
	var $_table_prefix = 'vtiger_';
	/**
	 * @access private
	 * @param string The regex-style set of characters that are valid for normalized tags.
	 */
	var $_normalized_valid_chars = 'a-zA-Z0-9';
	/**
	 * @access private
	 * @param string Whether to normalize tags at all.
	 * value 0 saves the tag in case insensitive mode
	 * value 1 save the tag in lower case
	 */
	var $_normalize_tags = 0;
	/**
	 * @access private
	 * @param string Whether to prevent multiple vtiger_users from tagging the same object. By default, set to block (ala Upcoming.org)
	 */
	var $_block_multiuser_tag_on_object =0;
	/**
	 * @access private
	 * @param bool Whether to use persistent ADODB connections. False by default.
	 */
	//var $_PCONNECT = FALSE;
	/**
	 * @access private
	 * @param int The maximum length of a tag.
	 */ 
	var $_MAX_TAG_LENGTH = 30;
	/**
	 * @access private
	 * @param string The file path to the installation of ADOdb used.
	 */ 
	//var $_ADODB_DIR = 'adodb/';

	/**
	 * freetag
	 *
	 * Constructor for the freetag class. 
	 *
	 * @param array An associative array of options to pass to the instance of Freetag.
	 * The following options are valid:
	 * - debug: Set to TRUE for debugging information. [default:FALSE]
	 * - db: If you've already got an ADODB ADOConnection, you can pass it directly and Freetag will use that. [default:NULL]
	 * - db_user: Database username
	 * - db_pass: Database password
	 * - db_host: Database hostname [default: localhost]
	 * - db_name: Database name
	 * - vtiger_table_prefix: If you wish to create multiple Freetag databases on the same database, you can put a prefix in front of the vtiger_table names and pass separate prefixes to the constructor. [default: '']
	 * - normalize_tags: Whether to normalize (lowercase and filter for valid characters) on tags at all. [default: 1]
	 * - normalized_valid_chars: Pass a regex-style set of valid characters that you want your tags normalized against. [default: 'a-zA-Z0-9' for alphanumeric]
	 * - block_multiuser_tag_on_object: Set to 0 in order to allow individual vtiger_users to all tag the same object with the same tag. Default is 1 to only allow one occurence of a tag per object. [default: 1]
	 * - MAX_TAG_LENGTH: maximum length of normalized tags in chars. [default: 30]
	 * - ADODB_DIR: directory in which adodb is installed. Change if you don't want to use the bundled version. [default: adodb/]
	 * - PCONNECT: Whether to use ADODB persistent connections. [default: FALSE]
	 * 
	 */ 
	function freetag($options = NULL) {
/*
		$available_options = array('debug', 'db', 'db_user', 'db_pass', 'db_host', 'db_name', 'table_prefix', 'normalize_tags', 'normalized_valid_chars', 'block_multiuser_tag_on_object', 'MAX_TAG_LENGTH', 'ADODB_DIR', 'PCONNECT');
		if (is_array($options)) {
			foreach ($options as $key => $value) {
				$this->debug_text("Option: $key");

				if (in_array($key, $available_options) ) {
					$this->debug_text("Valid Config options: $key");
					$property = '_'.$key;
					$this->$property = $value;
					$this->debug_text("Setting $property to $value");
				} else {
					$this->debug_text("ERROR: Config option: $key is not a valid option");
				}
			}
		}*/
/*
		require_once($this->_ADODB_DIR . "/adodb.inc.php");
		if (is_object($this->_db)) {
			$this->db = &$this->_db;
			$this->debug_text("DB Instance already exists, using this one.");
		} else {
			$this->db = ADONewConnection("mysql");
			$this->debug_text("Connecting to db with:" . $this->_db_host . " " . $this->_db_user . " " . $this->_db_pass . " " . $this->_db_name);
			if ($this->_PCONNECT) {
				$this->db->PConnect($this->_db_host, $this->_db_user, $this->_db_pass, $this->_db_name);
			} else {
				$this->db->Connect($this->_db_host, $this->_db_user, $this->_db_pass, $this->_db_name);
			}
		}
		$this->db->debug = $this->_debug;
		// Freetag uses ASSOC for ease of maintenance and compatibility with people who choose to modify the schema.
		// Feel free to convert to NUM if performance is the highest concern.
		$this->db->SetFetchMode(ADODB_FETCH_ASSOC);*/
	}

	/**
	 * get_objects_with_tag
	 *
	 * Use this function to build a page of results that have been tagged with the same tag.
	 * Pass along a tagger_id to collect only a certain user's tagged objects, and pass along
	 * none in order to get back all user-tagged objects. Most of the get_*_tag* functions
	 * operate on the normalized form of tags, because most interfaces for navigating tags
	 * should use normal form.
	 *
	 * @param string - Pass the normalized tag form along to the function.
	 * @param int (Optional) - The numerical offset to begin display at. Defaults to 0.
	 * @param int (Optional) - The number of results per page to show. Defaults to 100.
	 * @param int (Optional) - The unique ID of the 'user' who tagged the object.
	 *
	 * @return An array of Object ID numbers that reference your original objects.
	 */ 
	function get_objects_with_tag($tag, $offset = 0, $limit = 100, $tagger_id = NULL) {
		if(!isset($tag)) {
			return false;
		}		
		global $adb;
		
		$where = "tag = ? ";
		$params = array($tag);

		if(isset($tagger_id) && ($tagger_id > 0)) {
			$where .= "AND tagger_id = ? ";
			array_push($params, $tagger_id);
		} 
		
		$prefix = $this->_table_prefix;

		$sql = "SELECT DISTINCT object_id
			FROM ${prefix}freetagged_objects INNER JOIN ${prefix}freetags ON (tag_id = id)
			WHERE $where
			ORDER BY object_id ASC
			LIMIT $offset, $limit";
        echo $sql;
		$rs = $adb->pquery($sql, $params) or die("Error: $sql");
		$retarr = array();
		while(!$rs->EOF) {
			$retarr[] = $rs->fields['object_id'];
			$rs->MoveNext();
		}
		return $retarr;
	}

	/**
	 * get_objects_with_tag_all
	 *
	 * Use this function to build a page of results that have been tagged with the same tag.
	 * This function acts the same as get_objects_with_tag, except that it returns an unlimited
	 * number of results. Therefore, it's more useful for internal displays, not for API's.
	 * Pass along a tagger_id to collect only a certain user's tagged objects, and pass along
	 * none in order to get back all user-tagged objects. Most of the get_*_tag* functions
	 * operate on the normalized form of tags, because most interfaces for navigating tags
	 * should use normal form.
	 *
	 * @param string - Pass the normalized tag form along to the function.
	 * @param int (Optional) - The unique ID of the 'user' who tagged the object.
	 *
	 * @return An array of Object ID numbers that reference your original objects.
	 */ 
	function get_objects_with_tag_all($tag, $tagger_id = NULL) {
		if(!isset($tag)) {
			return false;
		}		
		global $adb;
		
		$where = "tag = ? ";
		$params = array($tag);

		if(isset($tagger_id) && ($tagger_id > 0)) {
			$where .= "AND tagger_id = ? ";
			array_push($params, $tagger_id);
		} 
		$prefix = $this->_table_prefix;

		$sql = "SELECT DISTINCT object_id
			FROM ${prefix}freetagged_objects INNER JOIN ${prefix}freetags ON (tag_id = id)
			WHERE $where
			ORDER BY object_id ASC
			";
        	//echo $sql;
		$rs = $adb->pquery($sql, $params) or die("Error: $sql");
		$retarr = array();
		while(!$rs->EOF) {
			$retarr[] = $rs->fields['object_id'];
			$rs->MoveNext();
		}
		return $retarr;
	}

	/**
	 * get_objects_with_tag_combo
	 *
	 * Returns an array of object ID's that have all the tags passed in the
	 * tagArray parameter. Use this to provide tag combo services to your vtiger_users.
	 *
	 * @param array - Pass an array of normalized form tags along to the function.
	 * @param int (Optional) - The numerical offset to begin display at. Defaults to 0.
	 * @param int (Optional) - The number of results per page to show. Defaults to 100.
	 * @param int (Optional) - Restrict the result to objects tagged by a particular user.
	 *
	 * @return An array of Object ID numbers that reference your original objects.
	 */
	 function get_objects_with_tag_combo($tagArray, $offset = 0, $limit = 100, $tagger_id = NULL) {
		if (!isset($tagArray) || !is_array($tagArray)) {
			return false;
		}
		global $adb;
		//$db = &$this->db;
		$retarr = array();
		if (count($tagArray) == 0) {
			return $retarr;
		}
		$params = array($tagArray);
		if(isset($tagger_id) && ($tagger_id > 0)) {
			$tagger_sql = "AND tagger_id = ?";
			array_push($params, $tagger_id);
		} else {
			$tagger_sql = "";
		}

		foreach ($tagArray as $key => $value) {
			$tagArray[$key] = $adb->qstr($value, get_magic_quotes_gpc());
		}

		$tagArray = array_unique($tagArray);
		$numTags = count($tagArray);
		$prefix = $this->_table_prefix;

		// We must adjust for duplicate normalized tags appearing multiple times in the join by 
		// counting only the distinct tags. It should also work for an individual user.

		$sql = "SELECT ${prefix}freetagged_objects.object_id, tag, COUNT(DISTINCT tag) AS uniques
			FROM ${prefix}freetagged_objects 
			INNER JOIN ${prefix}freetags ON (${prefix}freetagged_objects.tag_id = ${prefix}freetags.id)
			WHERE ${prefix}freetags.tag IN (". generateQuestionMarks($tagArray) .")
			$tagger_sql
			GROUP BY ${prefix}freetagged_objects.object_id
			HAVING uniques = $numTags
			LIMIT $offset, $limit";
		$this->debug_text("Tag combo: " . join("+", $tagArray) . " SQL: $sql");
		$rs = $adb->pquery($sql, $params) or die("Error: $sql");
		while(!$rs->EOF) {
			$retarr[] = $rs->fields['object_id'];
			$rs->MoveNext();
		}
		return $retarr;
	}

	/**
	 * get_objects_with_tag_id
	 *
	 * Use this function to build a page of results that have been tagged with the same tag.
	 * This function acts the same as get_objects_with_tag, except that it accepts a numerical
	 * tag_id instead of a text tag.
	 * Pass along a tagger_id to collect only a certain user's tagged objects, and pass along
	 * none in order to get back all user-tagged objects.
	 *
	 * @param int - Pass the ID number of the tag.
	 * @param int (Optional) - The numerical offset to begin display at. Defaults to 0.
	 * @param int (Optional) - The number of results per page to show. Defaults to 100.
	 * @param int (Optional) - The unique ID of the 'user' who tagged the object.
	 *
	 * @return An array of Object ID numbers that reference your original objects.
	 */ 
	function get_objects_with_tag_id($tag_id, $offset = 0, $limit = 100, $tagger_id = NULL) {
		if(!isset($tag_id)) {
			return false;
		}		
		global $adb;

		$where = "id = ? ";
		$params = array($tag_id);
		
		if(isset($tagger_id) && ($tagger_id > 0)) {
			$where .= "AND tagger_id = ?";
			array_push($params, $tagger_id);
		} 
	
		$prefix = $this->_table_prefix;

		$sql = "SELECT DISTINCT object_id
			FROM ${prefix}freetagged_objects INNER JOIN ${prefix}freetags ON (tag_id = id)
			WHERE $where
			ORDER BY object_id ASC
			LIMIT $offset, $limit ";
		$rs = $adb->pquery($sql, $params) or die("Error: $sql");
		$retarr = array();
		while(!$rs->EOF) {
			$retarr[] = $rs->fields['object_id'];
			$rs->MoveNext();
		}
		return $retarr;
	}


	/**
	 * get_tags_on_object
	 *
	 * You can use this function to show the tags on an object. Since it supports both user-specific
	 * and general modes with the $tagger_id parameter, you can use it twice on a page to make it work
	 * similar to upcoming.org and flickr, where the page displays your own tags differently than
	 * other vtiger_users' tags.
	 *
	 * @param int The unique ID of the object in question.
	 * @param int The offset of tags to return.
	 * @param int The size of the tagset to return. Use a zero size to get all tags.
	 * @param int The unique ID of the person who tagged the object, if user-level tags only are preferred.
	 *
	 * @return array Returns a PHP array with object elements ordered by object ID. Each element is an associative
	 * array with the following elements:
	 *   - 'tag' => Normalized-form tag
	 *	 - 'raw_tag' => The raw-form tag
	 *	 - 'tagger_id' => The unique ID of the person who tagged the object with this tag.
	 */ 
	function get_tags_on_object($object_id, $offset = 0, $limit = 10, $tagger_id = NULL) {
		if(!isset($object_id)) {
			return false;
		}	
		
		$where = "object_id = ? ";
		$params = array($object_id);
			
		if(isset($tagger_id) && ($tagger_id > 0)) {
			$where .= "AND tagger_id = ? ";
			array_push($params, $tagger_id);
		} 

		if($limit <= 0) {
			$limit_sql = "";
		} else {
			$limit_sql = "LIMIT $offset, $limit";
		}
		$prefix = $this->_table_prefix;

		global $adb;

		$sql = "SELECT DISTINCT tag, raw_tag, tagger_id, id
			FROM ${prefix}freetagged_objects INNER JOIN ${prefix}freetags ON (tag_id = id)
			WHERE $where
			ORDER BY id ASC
			$limit_sql
			";
			//echo ' <br><br>get_tags_on_object sql is ' .$sql;
		$rs = $adb->pquery($sql, $params) or die("Error: $sql");
		$retarr = array();
		while(!$rs->EOF) {
			$retarr[] = array(
					'tag' => $rs->fields['tag'],
					'raw_tag' => $rs->fields['raw_tag'],
					'tagger_id' => $rs->fields['tagger_id']
					);
			$rs->MoveNext();
		}
		return $retarr;
	}

	/**
	 * safe_tag
	 *
	 * Pass individual tag phrases along with object and person ID's in order to 
	 * set a tag on an object. If the tag in its raw form does not yet exist,
	 * this function will create it.
	 * Fails transparently on duplicates, and checks for dupes based on the 
	 * block_multiuser_tag_on_object constructor param.
	 *
	 * @param int The unique ID of the person who tagged the object with this tag.
	 * @param int The unique ID of the object in question.
	 * @param string A raw string from a web form containing tags.
	 *
	 * @return boolean Returns true if successful, false otherwise. Does not operate as a transaction.
	 */ 

	function safe_tag($tagger_id, $object_id, $tag, $module) {
		if(!isset($tagger_id)||!isset($object_id)||!isset($tag)) {
			die("safe_tag argument missing");
			return false;
		}
		global $adb;

		$normalized_tag = $this->normalize_tag($tag);
		$prefix = $this->_table_prefix;
		$params = array();
		// First, check for duplicate of the normalized form of the tag on this object.
		// Dynamically switch between allowing duplication between vtiger_users on the constructor param 'block_multiuser_tag_on_object'.
		// If it's set not to block multiuser tags, then modify the existence
		// check to look for a tag by this particular user. Otherwise, the following
		// query will reveal whether that tag exists on that object for ANY user.
		if ($this->_block_multiuser_tag_on_object == 0) {
			$tagger_sql = " AND tagger_id = ? ";
			array_push($params, $tagger_id);
		} else $tagger_sql = "";
		$sql = "SELECT COUNT(*) as count 
			FROM ${prefix}freetagged_objects INNER JOIN ${prefix}freetags ON (tag_id = id)
			WHERE 1=1 
			$tagger_sql
			AND object_id = ?
			AND tag = ? ";
			
		array_push($params, $object_id, $normalized_tag);
		$rs = $adb->pquery($sql, $params) or die("Syntax Error: $sql");
		if($rs->fields['count'] > 0) {
			return true;
		}
		// Then see if a raw tag in this form exists.
		$sql = "SELECT id 
			FROM ${prefix}freetags 
			WHERE raw_tag = ? ";
		$rs = $adb->pquery($sql, array($tag)) or die("Syntax Error: $sql");
		if(!$rs->EOF) {
			$tag_id = $rs->fields['id'];
		} else {
			// Add new tag! 
			$tag_id = $adb->getUniqueId('vtiger_freetags');
			$sql = "INSERT INTO ${prefix}freetags (id, tag, raw_tag) VALUES (?,?,?)";
			$params = array($tag_id, $normalized_tag, $tag);
			$rs = $adb->pquery($sql, $params) or die("Syntax Error: $sql");
			
		}
		if(!($tag_id > 0)) {
			return false;
		}
		$sql = "INSERT INTO ${prefix}freetagged_objects
			(tag_id, tagger_id, object_id, tagged_on, module) VALUES (?,?,?, NOW(),?)";
		$params = array($tag_id, $tagger_id, $object_id, $module);
		$rs = $adb->pquery($sql, $params) or die("Syntax error: $sql");

		return true;
	}

	/**
	 * normalize_tag
	 *
	 * This is a utility function used to take a raw tag and convert it to normalized form.
	 * Normalized form is essentially lowercased alphanumeric characters only, 
	 * with no spaces or special characters.
	 *
	 * Customize the normalized valid chars with your own set of special characters
	 * in regex format within the option 'normalized_valid_chars'. It acts as a filter
	 * to let a customized set of characters through.
	 * 
	 * After the filter is applied, the function also lowercases the characters using strtolower 
	 * in the current locale.
	 *
	 * The default for normalized_valid_chars is a-zA-Z0-9, or english alphanumeric.
	 *
	 * @param string An individual tag in raw form that should be normalized.
	 *
	 * @return string Returns the tag in normalized form.
	 */ 
	function normalize_tag($tag) {
		if ($this->_normalize_tags) {
			$normalized_valid_chars = $this->_normalized_valid_chars;
			$normalized_tag = preg_replace("/[^$normalized_valid_chars]/", "", $tag);
			return strtolower($normalized_tag);
		} else {
			return $tag;
		}

	}

	/**
	 * delete_object_tag
	 *
	 * Removes a tag from an object. This does not delete the tag itself from
	 * the database. Since most applications will only allow a user to delete
	 * their own tags, it supports raw-form tags as its tag parameter, because
	 * that's what is usually shown to a user for their own tags.
	 *
	 * @param int The unique ID of the person who tagged the object with this tag.
	 * @param int The ID of the object in question.
	 * @param string The raw string form of the tag to delete. See above for vtiger_notes.
	 *
	 * @return string Returns the tag in normalized form.
	 */ 
	function delete_object_tag($tagger_id, $object_id, $tag) {
		if(!isset($tagger_id)||!isset($object_id)||!isset($tag)) {
			die("delete_object_tag argument missing");
			return false;
		}
		global $adb;
		$tag_id = $this->get_raw_tag_id($tag);
		$prefix = $this->_table_prefix;
		if($tag_id > 0) {

			$sql = "DELETE FROM ${prefix}freetagged_objects
				WHERE tagger_id = ? AND object_id = ? AND tag_id = ? LIMIT 1";
			$params = array($tagger_id, $object_id, $tag_id);
			$rs = $adb->pquery($sql, $params) or die("Syntax Error: $sql");	
			return true;
		} else {
			return false;	
		}
	}

	/**
	 * delete_all_object_tags
	 *
	 * Removes all tag from an object. This does not
	 * delete the tag itself from the database. This is most useful for
	 * cleanup, where an item is deleted and all its tags should be wiped out
	 * as well.
	 *
	 * @param int The ID of the object in question.
	 *
	 * @return boolean Returns true if successful, false otherwise. It will return true if the tagged object does not exist.
	 */ 
	function delete_all_object_tags($object_id) {
		global $adb;
		$prefix = $this->_table_prefix;
		if($object_id > 0) {
			$sql = "DELETE FROM ${prefix}freetagged_objects
				WHERE object_id = ? ";	
				$rs = $adb->pquery($sql, array($object_id)) or die("Syntax Error: $sql");	
			return true;
		} else {
			return false;	
		}
	}


	/**
	 * delete_all_object_tags_for_user
	 *
	 * Removes all tag from an object for a particular user. This does not
	 * delete the tag itself from the database. This is most useful for
	 * implementations similar to del.icio.us, where a user is allowed to retag
	 * an object from a text box. That way, it becomes a two step operation of
	 * deleting all the tags, then retagging with whatever's left in the input.
	 *
	 * @param int The unique ID of the person who tagged the object with this tag.
	 * @param int The ID of the object in question.
	 *
	 * @return boolean Returns true if successful, false otherwise. It will return true if the tagged object does not exist.
	 */ 

	function delete_all_object_tags_for_user($tagger_id, $object_id) {
		if(!isset($tagger_id)||!isset($object_id)) {
			die("delete_all_object_tags_for_user argument missing");
			return false;
		}
		global $adb;
		$prefix = $this->_table_prefix;
		if($object_id > 0) {

			$sql = "DELETE FROM ${prefix}freetagged_objects
				WHERE tagger_id = ? AND object_id = ?";	
			$rs = $adb->pquery($sql, array($tagger_id, $object_id)) or die("Syntax Error: $sql");	
			return true;
		} else {
			return false;	
		}
	}

	/**
	 * get_tag_id
	 *
	 * Retrieves the unique ID number of a tag based upon its normal form. Actually,
	 * using this function is dangerous, because multiple tags can exist with the same
	 * normal form, so be careful, because this will only return one, assuming that
	 * if you're going by normal form, then the individual tags are interchangeable.
	 *
	 * @param string The normal form of the tag to fetch.
	 *
	 * @return string Returns the tag in normalized form.
	 */ 
	function get_tag_id($tag) {
		if(!isset($tag)) {
			die("get_tag_id argument missing");
			return false;
		}
		global $adb;
		
		$prefix = $this->_table_prefix;

		$sql = "SELECT id FROM ${prefix}freetags
			WHERE tag = ? LIMIT 1 ";	
			$rs = $adb->pquery($sql, array($tag)) or die("Syntax Error: $sql");	
		return $rs->fields['id'];

	}

	/**
	 * get_raw_tag_id
	 *
	 * Retrieves the unique ID number of a tag based upon its raw form. If a single
	 * unique record is needed, then use this function instead of get_tag_id, 
	 * because raw_tags are unique.
	 *
	 * @param string The raw string form of the tag to fetch.
	 *
	 * @return string Returns the tag in normalized form.
	 */ 

	function get_raw_tag_id($tag) {
		if(!isset($tag)) {
			die("get_tag_id argument missing");
			return false;
		}
		global $adb;
		$prefix = $this->_table_prefix;

		$sql = "SELECT id FROM ${prefix}freetags
			WHERE raw_tag = ? LIMIT 1 ";	
			$rs = $adb->pquery($sql, array($tag)) or die("Syntax Error: $sql");	
		return $rs->fields['id'];

	}

	/**
	 * tag_object
	 *
	 * This function allows you to pass in a string directly from a form, which is then
	 * parsed for quoted phrases and special characters, normalized and converted into tags.
	 * The tag phrases are then individually sent through the safe_tag() method for processing
	 * and the object referenced is set with that tag. 
	 *
	 * This method has been refactored to automatically look for existing tags and run
	 * adds/updates/deletes as appropriate.
	 *
	 * @param int The unique ID of the person who tagged the object with this tag.
	 * @param int The ID of the object in question.
	 * @param string The raw string form of the tag to delete. See above for vtiger_notes.
	 * @param int Whether to skip the update portion for objects that haven't been tagged. (Default: 1)
	 *
	 * @return string Returns the tag in normalized form.
	 */
	function tag_object($tagger_id, $object_id, $tag_string, $module, $skip_updates = 1) {
		if($tag_string == '') {
			// If an empty string was passed, just return true, don't die.
			// die("Empty tag string passed");
			return true;
		}
		$tagArray = $this->_parse_tags($tag_string);

		$oldTags = $this->get_tags_on_object($object_id, 0, 0, $tagger_id);

		$preserveTags = array();

		if (($skip_updates == 0) && (count($oldTags) > 0)) {
			foreach ($oldTags as $tagItem) {
				if (!in_array($tagItem['raw_tag'], $tagArray)) {
					// We need to delete old tags that don't appear in the new parsed string.
					$this->delete_object_tag($tagger_id, $object_id, $tagItem['raw_tag']);
				} else {
					// We need to preserve old tags that appear (to save timestamps)
					$preserveTags[] = $tagItem['raw_tag'];
				}
			}
		}
		$newTags = array_diff($tagArray, $preserveTags);

		$this->_tag_object_array($tagger_id, $object_id, $newTags, $module);

		return true;
	}

	/**
	 * _tag_object_array
	 *
	 * Private method to add tags to an object from an array.
	 *
	 * @param int Unique ID of tagger
	 * @param int Unique ID of object
	 * @param array Array of tags to add.
	 *
	 * @return boolean True if successful, false otherwise.
	 */
	function _tag_object_array($tagger_id, $object_id, $tagArray, $module) {
		foreach($tagArray as $tag) {
			$tag = trim($tag);
			if(($tag != '') && (strlen($tag) <= $this->_MAX_TAG_LENGTH)) {
				if(get_magic_quotes_gpc()) {
					$tag = addslashes($tag);
				}
				$this->safe_tag($tagger_id, $object_id, $tag, $module);
			}
		}
		return true;
	}

	/**
	 * _parse_tags
	 *
	 * Private method to parse tags out of a string and into an array.
	 *
	 * @param string String to parse.
	 *
	 * @return array Returns an array of the raw "tags" parsed according to the freetag settings.
	 */

	function _parse_tags($tag_string) {
		$newwords = array();
		if ($tag_string == '') {
			// If the tag string is empty, return the empty set.
			return $newwords;
		}
		# Perform tag parsing
		if(get_magic_quotes_gpc()) {
			$query = stripslashes(trim($tag_string));
		} else {
			$query = trim($tag_string);
		}
		$words = preg_split('/(")/', $query,-1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
		$delim = 0;
		foreach ($words as $key => $word)
		{
			if ($word == '"') {
				$delim++;
				continue;
			}
			if (($delim % 2 == 1) && $words[$key - 1] == '"') {
				$newwords[] = $word;
			} else {
				$newwords = array_merge($newwords, preg_split('/\s+/', $word, -1, PREG_SPLIT_NO_EMPTY));
			}
		}
		return $newwords;
	}

	/**
	 * update_tags
	 *
	 * This method supports a user updating their set of all tags on an object
	 * in a streamlined manner. Very useful for interfaces where all tags on an
	 * object from a user may be edited through a single text box.
	 */

	/**
	 * get_most_popular_tags
	 *
	 * This function returns the most popular tags in the freetag system, with
	 * offset and limit support for pagination. It also supports restricting to 
	 * an individual user. Call it with no parameters for a list of 25 most popular
	 * tags.
	 * 
	 * @param int The unique ID of the person to restrict results to.
	 * @param int The offset of the tag to start at.
	 * @param int The number of tags to return in the result set.
	 *
	 * @return array Returns a PHP array with tags ordered by popularity descending. 
	 * Each element is an associative array with the following elements:
	 *   - 'tag' => Normalized-form tag
	 *	 - 'count' => The number of objects tagged with this tag.
	 */

	function get_most_popular_tags($tagger_id = NULL, $offset = 0, $limit = 25) {
		global $adb;
		$params = array();
		if(isset($tagger_id) && ($tagger_id > 0)) {
			$tagger_sql = "AND tagger_id = ?";
			array_push($params, $tagger_id);
		} else {
			$tagger_sql = "";
		}
		$prefix = $this->_table_prefix;

		$sql = "SELECT tag, COUNT(*) as count
			FROM ${prefix}freetags INNER JOIN ${prefix}freetagged_objects ON (id = tag_id)
			WHERE 1
			$tagger_sql
			GROUP BY tag
			ORDER BY count DESC, tag ASC
			LIMIT $offset, $limit";

		$rs = $adb->pquery($sql, $params) or die("Syntax Error: $sql");
		$retarr = array();
		while(!$rs->EOF) {
			$retarr[] = array(
					'tag' => $rs->fields['tag'],
					'count' => $rs->fields['count']
					);
			$rs->MoveNext();
		}

		return $retarr;

	}

	/**
	 * count_tags
	 *
	 * Returns the total number of tag->object links in the system.
	 * It might be useful for pagination at times, but i'm not sure if I actually use
	 * this anywhere. Restrict to a person's tagging by using the $tagger_id parameter.
	 *
	 * @param int The unique ID of the person to restrict results to.
	 *
	 * @return int Returns the count 
	 */
	function count_tags($tagger_id = NULL) {
		global $adb;
		$params = array();
		if(isset($tagger_id) && ($tagger_id > 0)) {
			$tagger_sql = "AND tagger_id = ?";
			array_push($params, $tagger_id);
		} else {
			$tagger_sql = "";
		}
		$prefix = $this->_table_prefix;

		$sql = "SELECT COUNT(*) as count
			FROM ${prefix}freetags INNER JOIN ${prefix}freetagged_objects ON (id = tag_id)
			WHERE 1
			$tagger_sql
			";

		$rs = $adb->pquery($sql, $params) or die("Syntax Error: $sql");
		if(!$rs->EOF) {
			return $rs->fields['count'];
		}
		return false;

	}

	/**
	 * get_tag_cloud_html
	 *
	 * This is a pretty straightforward, flexible method that automatically
	 * generates some html that can be dropped in as a tag cloud.
	 * It uses explicit font sizes inside of the style attribute of SPAN 
	 * elements to accomplish the differently sized objects.
	 *
	 * It will also link every tag to $tag_page_url, appended with the 
	 * normalized form of the tag. You should adapt this value to your own
	 * tag detail page's URL.
	 *
	 * @param int The maximum number of tags to return. (default: 100)
	 * @param int The minimum font size in the cloud. (default: 10)
	 * @param int The maximum number of tags to return. (default: 20)
	 * @param string The "units" for the font size (i.e. 'px', 'pt', 'em') (default: px)
	 * @param string The class to use for all spans in the cloud. (default: cloud_tag)
	 * @param string The tag page URL (default: /tag/)
	 *
	 * @return string Returns an HTML snippet that can be used directly as a tag cloud.
	 */

	function get_tag_cloud_html($module="",$tagger_id = NULL,$obj_id= NULL,$num_tags = 100, $min_font_size = 10, $max_font_size = 20, $font_units = 'px', $span_class = '', $tag_page_url = '/tag/') {
		global $theme;
		$theme_path="themes/".$theme."/";
		$image_path=$theme_path."images/";	
		$tag_list = $this->get_tag_cloud_tags($num_tags, $tagger_id,$module,$obj_id);
		if (count($tag_list[0])) {
			// Get the maximum qty of tagged objects in the set
			$max_qty = max(array_values($tag_list[0]));
			// Get the min qty of tagged objects in the set
			$min_qty = min(array_values($tag_list[0]));
		} else {
			return '';
		}

		// For ever additional tagged object from min to max, we add
		// $step to the font size.
		$spread = $max_qty - $min_qty;
		if (0 == $spread) { // Divide by zero
			$spread = 1;
		}
		$step = ($max_font_size - $min_font_size)/($spread);

		// Since the original tag_list is alphabetically ordered,
		// we can now create the tag cloud by just putting a span
		// on each element, multiplying the diff between min and qty
		// by $step.
		$cloud_html = '';
		$cloud_spans = array();
		if($module =='')
			$module = 'All';
		if($module != 'All') {
			foreach ($tag_list[0] as $tag => $qty) {
				$size = $min_font_size + ($qty - $min_qty) * $step;
				$cloud_span[] = '<span id="tag_'.$tag_list[1][$tag].'" class="' . $span_class . '" onMouseOver=$("tagspan_'.$tag_list[1][$tag].'").style.display="inline"; onMouseOut=$("tagspan_'.$tag_list[1][$tag].'").style.display="none";><a class="tagit" href="index.php?module=Home&action=UnifiedSearch&search_module='.$module.'&search_tag=tag_search&query_string='. urlencode($tag) . '" style="font-size: '. $size . $font_units . '">' . htmlspecialchars(stripslashes($tag)) . '</a><span class="'. $span_class .'" id="tagspan_'.$tag_list[1][$tag].'" style="display:none;cursor:pointer;" onClick="DeleteTag('.$tag_list[1][$tag].','.$obj_id.');"><img src="' . vtiger_imageurl('del_tag.gif', $theme) . '"></span></span>';

			}
		} else {
			foreach($tag_list[0] as $tag => $qty) {
				$size = $min_font_size + ($qty - $min_qty) * $step;
				$cloud_span[] = '<span class="' . $span_class . '"><a class="tagit" href="index.php?module=Home&action=UnifiedSearch&search_module='.$module.'&search_tag=tag_search&query_string='. urlencode($tag) . '" style="font-size: '. $size . $font_units . '">' . htmlspecialchars(stripslashes($tag)) . '</a></span>';
			}
		}
		$cloud_html = join("\n ", $cloud_span);
		return $cloud_html;

	}

	/*
	 * get_tag_cloud_tags
	 *
	 * This is a function built explicitly to set up a page with most popular tags
	 * that contains an alphabetically sorted list of tags, which can then be sized
	 * or colored by popularity.
	 *
	 * Also known more popularly as Tag Clouds!
	 *
	 * Here's the example case: http://upcoming.org/tag/
	 *
	 * @param int The maximum number of tags to return.
	 *
	 * @return array Returns an array where the keys are normalized tags, and the
	 * values are numeric quantity of objects tagged with that tag.
	 */

	function get_tag_cloud_tags($max = 100, $tagger_id = NULL,$module = "",$obj_id = NULL) {
		global $adb;
		$params = array();
		if(isset($tagger_id) && ($tagger_id > 0)) {
			$tagger_sql = " AND tagger_id = ?";
			array_push($params, $tagger_id);
		} else {
			$tagger_sql = "";
		}

		if($module != "") {
			$tagger_sql .= " AND module = ?";
			array_push($params, $module);
		} else {
			$tagger_sql .= "";
		}

		if(isset($obj_id) && $obj_id > 0) {
  			$tagger_sql .= " AND object_id = ?";
			array_push($params, $obj_id);
		} else {
			$tagger_sql .= "";
		}

		$prefix = $this->_table_prefix;
		$sql = "SELECT tag,tag_id,COUNT(object_id) AS quantity
			FROM ${prefix}freetags INNER JOIN ${prefix}freetagged_objects
			ON (${prefix}freetags.id = tag_id)
			WHERE 1=1
			$tagger_sql
			GROUP BY tag
			ORDER BY quantity DESC LIMIT 0, $max";
        //echo $sql;
		$rs = $adb->pquery($sql, $params) or die("Syntax Error: $sql");
		$retarr = array();
		while(!$rs->EOF) {
			$retarr[$rs->fields['tag']] = $rs->fields['quantity'];
			$retarr1[$rs->fields['tag']] = $rs->fields['tag_id'];
			$rs->MoveNext();
		}
		if($retarr) ksort($retarr);
		if($retarr1) ksort($retarr1);
		$return_value[]=$retarr;
		$return_value[]=$retarr1;
		return $return_value;

	}

	/**
	 * similar_tags
	 *
	 * Finds tags that are "similar" or related to the given tag.
	 * It does this by looking at the other tags on objects tagged with the tag specified.
	 * Confusing? Think of it like e-commerce's "Other vtiger_users who bought this also bought," 
	 * as that's exactly how this works.
	 *
	 * Returns an empty array if no tag is passed, or if no related tags are found.
	 * Hint: You can detect related tags returned with count($retarr > 0)
	 *
	 * It's important to note that the quantity passed back along with each tag
	 * is a measure of the *strength of the relation* between the original tag
	 * and the related tag. It measures the number of objects tagged with both
	 * the original tag and its related tag.
	 *
	 * Thanks to Myles Grant for contributing this function!
	 *
	 * @param string The raw normalized form of the tag to fetch.
	 * @param int The maximum number of tags to return.
	 *
	 * @return array Returns an array where the keys are normalized tags, and the
	 * values are numeric quantity of objects tagged with BOTH tags, sorted by
	 * number of occurences of that tag (high to low).
	 */ 

	function similar_tags($tag, $max = 100) {
		$retarr = array();
		if(!isset($tag)) {
			return $retarr;
		}
		global $adb;

		// This query was written using a double join for PHP. If you're trying to eke
		// additional performance and are running MySQL 4.X, you might want to try a subselect
		// and compare perf numbers.
		$prefix = $this->_table_prefix;

		$sql = "SELECT t1.tag, COUNT( o1.object_id ) AS quantity
			FROM ${prefix}freetagged_objects o1
			INNER JOIN ${prefix}freetags t1 ON ( t1.id = o1.tag_id )
			INNER JOIN ${prefix}freetagged_objects o2 ON ( o1.object_id = o2.object_id )
			INNER JOIN ${prefix}freetags t2 ON ( t2.id = o2.tag_id )
			WHERE t2.tag = ? AND t1.tag != ?
			GROUP BY o1.tag_id
			ORDER BY quantity DESC
			LIMIT 0, ?";

		$rs = $adb->pquery($sql, array($tag, $tag, $max)) or die("Syntax Error: $sql");
		while(!$rs->EOF) {
			$retarr[$rs->fields['tag']] = $rs->fields['quantity'];
			$rs->MoveNext();
		}

		return $retarr;
	}

	/**
	 * similar_objects
	 *
	 * This method implements a simple ability to find some objects in the database
	 * that might be similar to an existing object. It determines this by trying
	 * to match other objects that share the same tags.
	 *
	 * The user of the method has to use a threshold (by default, 1) which specifies
	 * how many tags other objects must have in common to match. If the original object 
	 * has no tags, then it won't match anything. Matched objects are returned in order
	 * of most similar to least similar.
	 *
	 * The more tags set on a database, the better this method works. Since this
	 * is such an expensive operation, it requires a limit to be set via max_objects.
	 *
	 * @param int The unique ID of the object to find similar objects for.
	 * @param int The Threshold of tags that must be found in common (default: 1)
	 * @param int The maximum number of similar objects to return (default: 5).
	 * @param int Optionally pass a tagger id to restrict similarity to a tagger's view.
	 * 
	 * @return array Returns a PHP array with matched objects ordered by strength of match descending. 
	 * Each element is an associative array with the following elements:
	 * - 'strength' => A floating-point strength of match from 0-1.0
	 * - 'object_id' => Unique ID of the matched object
	 *
	 */
	function similar_objects($object_id, $threshold = 1, $max_objects = 5, $tagger_id = NULL) {
		global $adb;	
		$retarr = array();

		$object_id = intval($object_id);
		$threshold = intval($threshold);
		$max_objects = intval($max_objects);
		if (!isset($object_id) || !($object_id > 0)) {
			return $retarr;
		}
		if ($threshold <= 0) {
			return $retarr;
		}
		if ($max_objects <= 0) {
			return $retarr;
		}

		// Pass in a zero-limit to get all tags.
		$tagItems = $this->get_tags_on_object($object_id, 0, 0);

		$tagArray = array();
		foreach ($tagItems as $tagItem) {
			$tagArray[] = $tagItem['tag'];
		}
		$tagArray = array_unique($tagArray);

		$numTags = count($tagArray);
		if ($numTags == 0) {
			return $retarr; // Return empty set of matches
		}

		$prefix = $this->_table_prefix;

		$sql = "SELECT matches.object_id, COUNT( matches.object_id ) AS num_common_tags
			FROM ${prefix}freetagged_objects as matches
			INNER JOIN ${prefix}freetags as tags ON ( tags.id = matches.tag_id )
			WHERE tags.tag IN (". generateQuestionMarks($tagArray) .")
			GROUP BY matches.object_id
			HAVING num_common_tags >= ?
			ORDER BY num_common_tags DESC
			LIMIT 0, ? ";

		$rs = $adb->pquery($sql, array($tagArray, $threshold, $max_objects)) or die("Syntax Error: $sql, Error: " . $adb->ErrorMsg());
		while(!$rs->EOF) {
			$retarr[] = array (
				'object_id' => $rs->fields['object_id'],
				'strength' => ($rs->fields['num_common_tags'] / $numTags)
				);
			$rs->MoveNext();
		}

		return $retarr;
	}


	/*
	 * Prints debug text if debug is enabled.
	 *
	 * @param string The text to output
	 * @return boolean Always returns true
	 */
	function debug_text($text) {
		if ($this->_debug) {
			echo "$text<br>\n";
		}
		return true;
	}

}

