{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
{strip}
    {assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
    {assign var=ALL_VALUES value=$FIELD_MODEL->getUITypeModel()->getAllValue()}
    {assign var=SEARCH_VALUES value=explode(',',$SEARCH_INFO['searchValue'])}
    <div class="">
        <select class="select2noactive listSearchContributor form-control" title="{vtranslate($FIELD_MODEL->get('label'))}" multiple name="{$FIELD_MODEL->get('name')}"  data-fieldinfo='{$FIELD_INFO|escape}'>
        {foreach item=LABEL key=KEY from=$ALL_VALUES}
                <option value="{$KEY}"  data-parent="{$LABEL[1]}" {if in_array($KEY,$SEARCH_VALUES) && ($KEY neq "") } selected{/if}>{$LABEL[0]}</option>
        {/foreach}
    </select>
    </div>
{/strip}
