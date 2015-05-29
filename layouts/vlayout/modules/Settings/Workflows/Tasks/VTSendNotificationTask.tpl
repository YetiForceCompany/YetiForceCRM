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
{assign var=TEMPLATELIST value=OSSMailTemplates_Record_Model::getTempleteList($SOURCE_MODULE)}
<div class="well" id="VtVTEmailTemplateTaskContainer">
	<div class="row">
		<div class="row padding-bottom1per">
			<span class="col-md-4">{vtranslate('EmailTempleteList', $QUALIFIED_MODULE)}</span>
			<select class="chzn-select col-md-4" name="template" data-validation-engine='validate[required]'>
				<option value="">{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
				{foreach from=$TEMPLATELIST key=key item=item}
					<option {if $TASK_OBJECT->template eq $key}selected=""{/if} value="{$key}">{$item.name}</option>
				{/foreach}	
			</select>
		</div>
	</div>
</div>	
{/strip}	