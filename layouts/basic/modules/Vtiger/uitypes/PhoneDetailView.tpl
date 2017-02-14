{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
   * Contributor(s): YetiForce.com
 ********************************************************************************/
-->*}
{strip}
{* To check current user has permission to make outbound call. If so, make all the detail view phone fields as links to call *}
{assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
{if $FIELD_VALUE}
	{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId(), $RECORD)}
{/if}
{/strip}
