<?php

/**
 * Configuration file.
 * This file is auto-generated.
 *
 * @package Config
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

namespace Config;

/**
 * Configuration Class.
 */
class Performance
{
	/** Data caching is about storing some PHP variables in cache and retrieving it later from cache. Drivers: Base, Apcu */
	public static $CACHING_DRIVER = 'Base';

	/** Enable caching of user data */
	public static $ENABLE_CACHING_USERS = false;

	/** Enable caching database instance, accelerate time database connection */
	public static $ENABLE_CACHING_DB_CONNECTION = false;

	/**
	 * Should the caller information be captured in SQL Logging?
	 * It adds little overhead for performance but will be useful to debug.
	 * All data can be found in the table 'l_yf_sqltime'
	 */
	public static $SQL_LOG_INCLUDE_CALLER = false;

	/** If database default charset is UTF-8, set this to true This avoids executing the SET NAMES SQL for each query! */
	public static $DB_DEFAULT_CHARSET_UTF8 = true;

	/** Compute list view record count while loading listview everytime. Recommended value false */
	public static $LISTVIEW_COMPUTE_PAGE_COUNT = false;

	/** Enable automatic records list refreshing while changing the value of the selection list */
	public static $AUTO_REFRESH_RECORD_LIST_ON_SELECT_CHANGE = true;

	/** Show in search engine/filters only users and groups available in records list. It might result in a longer search time. */
	public static $SEARCH_SHOW_OWNER_ONLY_IN_LIST = false;

	/** Time to update number of notifications in seconds */
	public static $INTERVAL_FOR_NOTIFICATION_NUMBER_CHECK = 100;

	/** Search owners by AJAX. We recommend selecting the "true" value if there are numerous users in the system. */
	public static $SEARCH_OWNERS_BY_AJAX = false;

	/** Search roles by AJAX */
	public static $SEARCH_ROLES_BY_AJAX = false;

	/** Search reference by AJAX. We recommend selecting the "true" value if there are numerous users in the system. */
	public static $SEARCH_REFERENCE_BY_AJAX = false;

	/** Max number of exported records */
	public static $MAX_NUMBER_EXPORT_RECORDS = 500;

	/** Max number of mass deleted records */
	public static $maxMassDeleteRecords = 1000;

	/** Max number of mass deleted records */
	public static $maxMassTransferOwnershipRecords = 1000;

	/** Minimum number of characters to search for record owner */
	public static $OWNER_MINIMUM_INPUT_LENGTH = 2;

	/** Minimum number of characters to search for role */
	public static $ROLE_MINIMUM_INPUT_LENGTH = 2;

	/** The numbers of emails downloaded during one scanning */
	public static $NUMBERS_EMAILS_DOWNLOADED_DURING_ONE_SCANNING = 100;

	/** In how many records should the global search permissions be updated in cron */
	public static $CRON_MAX_NUMBERS_RECORD_PRIVILEGES_UPDATER = 1000000;

	/** In how many records should the address book be updated in cron */
	public static $CRON_MAX_NUMBERS_RECORD_ADDRESS_BOOK_UPDATER = 10000;

	/** In how many records should the label be updated in cron */
	public static $CRON_MAX_NUMBERS_RECORD_LABELS_UPDATER = 1000;

	/** In how many mails should the send in cron (Mailer). */
	public static $CRON_MAX_NUMBERS_SENDING_MAILS = 1000;

	/** In how many sms should the send in cron. */
	public static $CRON_MAX_NUMBERS_SENDING_SMS = 10;

	/** In how many atachments should the delete in cron. */
	public static $CRON_MAX_ATACHMENTS_DELETE = 1000;

	/** Time to execute batch methods [min]. */
	public static $CRON_BATCH_METHODS_LIMIT = 15;

	/**
	 * Parameter that allows to disable file overwriting.
	 * After enabling it the system will additionally check whether the file exists in the custom directory. Ex. custom/modules/Assets/Assets.php
	 */
	public static $LOAD_CUSTOM_FILES = false;

	/** Parameter that determines whether admin panel should be available to admin by default */
	public static $SHOW_ADMIN_PANEL = false;

	/** Display administrators in the list of users (Assigned To) */
	public static $SHOW_ADMINISTRATORS_IN_USERS_LIST = true;

	/** Global search: true/false */
	public static $GLOBAL_SEARCH = true;

	/** Browsing history working if true */
	public static $BROWSING_HISTORY_WORKING = true;

	/** Number of browsing history steps */
	public static $BROWSING_HISTORY_VIEW_LIMIT = 20;

	/** Days after browsing history has deleted */
	public static $BROWSING_HISTORY_DELETE_AFTER = 7;

	/** Session handler name, handler dir: app/Session/ */
	public static $SESSION_DRIVER = 'File';

	/** Charts multi filter limit */
	public static $CHART_MULTI_FILTER_LIMIT = 5;

	/** Charts multi filter maximum db value length */
	public static $CHART_MULTI_FILTER_STR_LEN = 50;

	/** Additional filters limit for ChartFilter's */
	public static $CHART_ADDITIONAL_FILTERS_LIMIT = 6;

	/** Maximum number of merged records */
	public static $MAX_MERGE_RECORDS = 4;

	/** Can CRM have access to the Internet? */
	public static $ACCESS_TO_INTERNET = true;

	/** Change the locale for sort the data */
	public static $CHANGE_LOCALE = true;

	/** Is divided layout style on edit view in modules with products */
	public static $INVENTORY_EDIT_VIEW_LAYOUT = true;

	/**
	 * Any modifications of this parameter require the vendor's consent.
	 * Any unauthorised modification breaches the terms and conditions of YetiForce Public License.
	 */
	public static $LIMITED_INFO_IN_FOOTER = false;

	/**
	 * Any modifications of this parameter require the vendor's consent.
	 * Any unauthorised modification breaches the terms and conditions of YetiForce Public License.
	 */
	public static $LIMITED_INFO_SUPPORT = false;

	/** Popover record's trigger delay in ms */
	public static $RECORD_POPOVER_DELAY = 500;

	/** Empty value when is not selected item in picklist dependency */
	public static $PICKLIST_DEPENDENCY_DEFAULT_EMPTY = true;

	/** If there is no translation in the chosen language, then get from the default language. */
	public static $recursiveTranslate = false;
}
