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
    {assign var="FIELD_INFO" value=\includes\utils\Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var=UITYPE_MODEL value=$FIELD_MODEL->getUITypeModel()}
	{assign var=PICKLIST_VALUES value=$UITYPE_MODEL->getLimits()}
    {assign var=SEARCH_VALUES value=explode(',',$SEARCH_INFO['searchValue'])}
    <div class="row">
        <select class="select2noactive listSearchContributor col-md-9" name="{$FIELD_MODEL->get('name')}" title="{vtranslate($FIELD_MODEL->get('label'))}" multiple style="width:150px;" data-fieldinfo='{$FIELD_INFO|escape}'>
            {foreach item=VALUE key=KEY from=$PICKLIST_VALUES}
                <option value="{$KEY}" {if in_array($KEY,$SEARCH_VALUES) && ($KEY neq "")} selected{/if}>{$VALUE['value']} - {$VALUE['name']}</option>
            {/foreach}
        </select>
    </div>
{/strip}
