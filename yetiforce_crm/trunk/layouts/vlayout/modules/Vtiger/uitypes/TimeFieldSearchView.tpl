{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{strip}
{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_MODEL->getFieldInfo()))}
{assign var="SEARCH_VALUE" value=$SEARCH_INFO['searchValue']}
{if !empty($SEARCH_VALUE)}
    {assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($SEARCH_INFO['searchValue'])}
{else}
    {assign var=FIELD_VALUE value=""}
{/if}
{assign var="TIME_FORMAT" value=$USER_MODEL->get('hour_format')}
<div class="row-fluid">
<input type="text" data-format="{$TIME_FORMAT}" class="span9 timepicker-default listSearchContributor" value="{$FIELD_VALUE}" name="{$FIELD_MODEL->getFieldName()}" data-fieldinfo='{$FIELD_INFO}' />
</div>
{/strip}