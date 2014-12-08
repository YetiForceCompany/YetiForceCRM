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
    {assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
    <div class="row-fluid">
        <input type="text" name="{$FIELD_MODEL->get('name')}" class="span9 listSearchContributor" value="{$SEARCH_INFO['searchValue']}" data-fieldinfo='{$FIELD_INFO|escape}'/>
    </div>
{/strip}