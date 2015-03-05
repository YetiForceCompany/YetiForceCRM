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
<div class="row-fluid">
	<div class="span5 marginLeftZero">
		<div class="pull-left pushDown2per marginLeftZero" >{vtranslate('Language',$QUALIFIED_MODULE)}:</div>
		<div class="pull-left">
			<select multiple="multiple" class="chzn-select span4" id="langs_list">
				{foreach from=$LANGS item=LANG key=ID}
					<option value="{$LANG['prefix']}" {if $MODULE_MODEL->parse_data($LANG['prefix'],$REQUEST->get('lang'))}selected{/if}>{$LANG['label']}</option>
				{/foreach}
			</select>
		</div>
	</div>
	<div class="span5 marginLeftZero">
		<div class="pull-left pushDown2per marginLeftZero" >{vtranslate('Modules',$QUALIFIED_MODULE)}:</div>
		<div class="pull-left">
			{assign var=PICKLIST_VALUES value=Vtiger_Field_Model::getModulesListValues()}
			<select class="chzn-select span3 mods_list" name="mods_list" id="mods_list" data-target="HelpInfo">
				{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
					<option value="{$PICKLIST_VALUE.name}" {if $PICKLIST_VALUE.name eq $REQUEST->get('mod')}selected{/if}>{$PICKLIST_VALUE.label}</option>
				{/foreach}
			</select>
		</div>
	</div>
	<div class="span0 marginLeftZero" style="width: 90px;">
		<input type="checkbox" class="show_differences" name="show_differences" {if $SD eq 1}checked{/if} value="1">{vtranslate('LBL_SHOW_EMPTY_VALUES', $QUALIFIED_MODULE)}
	</div>
</div>
{if $DATA}
<div class="">
	<table class="table table-bordered table-condensed listViewEntriesTable" >
		<thead>
			<tr class="blockHeader">
				<th class="span1"><strong>{vtranslate('LBL_FIELD_NAME',$QUALIFIED_MODULE)}</strong></th>
				<th class="span2"><strong>{vtranslate('LBL_SHOW_IN',$QUALIFIED_MODULE)}</strong></th>
				{foreach from=$DATA['langs'] item=item}
					<th><strong>{$item}</strong></th>
				{/foreach}
			</tr>
		</thead>
		<tbody>
		{if $DATA['php']}
			{foreach from=$DATA['php'] item=langs key=lang_key}
				{assign var=TEMPDATA value = 1}
				{if $SD == 1}
					{assign var=TEMPDATA value = 0}
					{foreach from=$langs item=item key=key}
						{if ($key neq 'label' || $key neq 'view') && $item == NULL}
							{assign var=TEMPDATA value = 1}
						{/if}
					{/foreach}
				{/if}
				{if $TEMPDATA == 1}
					<tr data-langkey="{$lang_key}">
						{foreach from=$langs item=item key=key}
							{if $key eq 'label'}
								<td class="span1">{$item}</td>
							{elseif $key eq 'info'}
								<td  class="">
									<select class="chzn-select helpInfoView span2" id="helpInfoView" name="helpInfoView" multiple data-fieldid="{$item.fieldid}" placeholder="{vtranslate('LBL_SELECT_OPTION',$QUALIFIED_MODULE)}">
											<option value="Edit" {if in_array('Edit',$item.view)}selected{/if}>{vtranslate('LBL_EDIT',$QUALIFIED_MODULE)}</option>
											<option value="Detail" {if in_array('Detail',$item.view)}selected{/if}>{vtranslate('LBL_DETAIL',$QUALIFIED_MODULE)}</option>
											<option value="QuickCreateAjax" {if in_array('QuickCreateAjax',$item.view)}selected{/if}>{vtranslate('LBL_QUICKCREATE',$QUALIFIED_MODULE)}</option>
									</select>
								</td>
							{else}
								<td class="showText">
								<div>
								<button class="btn editButton" style="margin-left:5px;">{vtranslate('LBL_EDIT_RECORD', $QUALIFIED_MODULE)}</button>
								<a style="margin-top:5px;" href="#" class="HelpInfoPopover pull-left" title="" data-placement="left" data-content="{htmlspecialchars(decode_html($item))}" data-original-title='{$langs.label}'><i class="icon-info-sign"></i></a>
								</div>
								<textarea id="{$key}_{$lang_key}"
									data-lang="{$key}"
									data-type="php"
									name="{$lang_key}" 
									class="translation {if $item == NULL}empty_value {/if}ckEditorSource ckEditorSmall hide" 
									{if $item == NULL} placeholder="{vtranslate('LBL_NoTranslation',$QUALIFIED_MODULE)}" {/if} 
									>{$item}</textarea>
								</td>
							{/if}
						{/foreach}
					</tr>
				{/if}
			{/foreach}
		{/if}
		</tbody>
	</table>
</div>
{/if}