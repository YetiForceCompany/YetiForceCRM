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
	<div class="" id="VtVTEmailTemplateTaskContainer">
		<div class="">
			<div class="row">
				<label class="control-label col-md-4">{vtranslate('EmailTempleteList', $QUALIFIED_MODULE)}</label>
				<div class="col-md-7">
					<select class="chzn-select form-control" name="template" data-validation-engine='validate[required]'>
						<option value="">{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}</option>
						{foreach from=App\Mail::getTempleteList($SOURCE_MODULE,'PLL_RECORD') key=key item=item}
							<option {if $TASK_OBJECT->template eq $item['id']}selected=""{/if} value="{$item['id']}">{\App\Language::translate($item['name'], $QUALIFIED_MODULE)}</option>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
	</div>	
{/strip}	
