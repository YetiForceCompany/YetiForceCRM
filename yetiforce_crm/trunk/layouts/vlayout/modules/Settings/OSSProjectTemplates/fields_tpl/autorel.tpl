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
<select type="text" name="{$FLD_NAME}" id="{$FLD_ID}" class="chzn-select  chzn-done {if $FLD_REQUIRED}required{/if}" style="width: 250px;">
    <option value="none">{vtranslate('NONE', 'OSSProjectTemplates')}</option>
    <option value="base_on_record">{vtranslate('BASE_ON_RECORD', 'OSSProjectTemplates')}</option>
</select>