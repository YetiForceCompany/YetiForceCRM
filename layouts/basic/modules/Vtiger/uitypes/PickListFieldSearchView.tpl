{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
   *Contributor(s): YetiForce.com
 ********************************************************************************/
-->*}
{strip}
    {assign var="FIELD_INFO" value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
    {assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues(true)}
    {assign var=SEARCH_VALUES value=explode(',',$SEARCH_INFO['searchValue'])}
    <div class="picklistSearchField">
        <select class="select2noactive listSearchContributor form-control" name="{$FIELD_MODEL->get('name')}" multiple title="{vtranslate($FIELD_MODEL->get('label'), $MODULE)}" data-fieldinfo='{$FIELD_INFO|escape}'>
        {foreach item=PICKLIST_LABEL key=PICKLIST_KEY from=$PICKLIST_VALUES}
                <option value="{$PICKLIST_KEY}" {if in_array($PICKLIST_KEY,$SEARCH_VALUES) && ($PICKLIST_KEY neq "") } selected{/if}>{$PICKLIST_LABEL}</option>
        {/foreach}
    </select>
    </div>
{/strip}
