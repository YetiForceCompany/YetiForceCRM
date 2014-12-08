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
<select name="{$FLD_NAME}" id="{$FLD_ID}" class="chzn-select  chzn-done {if $FLD_REQUIRED}required{/if}" style="width: 250px;">
    <option value="person_who_created_record">{vtranslate('PRESON_WHO_CREATE_RECORD', 'OSSProjectTemplates')}</option>
    <optgroup label="{vtranslate('LBL_USERS')}">
        {foreach from=$OPTION['User'] item=item key=key}
            {if !empty($item)}
            <option value="{$key}">{$item}</option>
            {/if}
        {/foreach}
    </optgroup>
    <optgroup label="{vtranslate('LBL_GROUPS')}">
        {foreach from=$OPTION['Group'] item=item key=key}
            {if !empty($item)}
            <option value="{$key}">{$item}</option>
            {/if}
        {/foreach}
    </optgroup>
</select>