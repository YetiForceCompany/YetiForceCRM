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
<div class="">
	<div class="form-horizontal">
		<div class="form-group row">
			<label class="control-label col-md-1" >{vtranslate('Language',$QUALIFIED_MODULE)}:</label>
			<div class="col-md-3">
				<select multiple="multiple" class="form-control" id="langs_list">
					{foreach from=$LANGS item=LANG key=ID}
						<option value="{$LANG['prefix']}" {if $MODULE_MODEL->parse_data($LANG['prefix'],$REQUEST->get('lang'))}selected{/if}>{$LANG['label']}</option>
					{/foreach}
				</select>
			</div>
			<label class="control-label col-md-1" >{vtranslate('Modules',$QUALIFIED_MODULE)}:</label>
			<div class="col-md-3">
				<select class="form-control mods_list" name="mods_list" id="mods_list" data-target="HelpInfo">
					{foreach item=MODULE_INFO from=\vtlib\Functions::getAllModules(true, false, 0)}
						<option value="{$MODULE_INFO['name']}" {if $MODULE_INFO['name'] eq $REQUEST->get('mod')}selected{/if}>{App\Language::translate($MODULE_INFO['name'], $MODULE_INFO['name'])}</option>
					{/foreach}
				</select>
			</div>
			<div class="checkbox col-md-2">
				<label class=""><input type="checkbox" class="show_differences" name="show_differences" {if $SD eq 1}checked{/if} value="1">{vtranslate('LBL_SHOW_EMPTY_VALUES', $QUALIFIED_MODULE)}</label>
			</div>
		</div>
	</div>
</div>
{if $DATA}
<div class="">
	<table class="table table-bordered table-condensed listViewEntriesTable" >
		<thead>
			<tr class="blockHeader">
				<th class="col-md-1"><strong>{vtranslate('LBL_FIELD_NAME',$QUALIFIED_MODULE)}</strong></th>
				<th class="col-md-2"><strong>{vtranslate('LBL_SHOW_IN',$QUALIFIED_MODULE)}</strong></th>
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
								<td class="col-md-1">{$item}</td>
							{elseif $key eq 'info'}
								<td  class="">
									<select class="helpInfoView form-control" id="helpInfoView" name="helpInfoView" multiple data-fieldid="{$item.fieldid}" placeholder="{vtranslate('LBL_SELECT_OPTION',$QUALIFIED_MODULE)}">
											<option value="Edit" {if in_array('Edit',$item.view)}selected{/if}>{vtranslate('LBL_EDIT',$QUALIFIED_MODULE)}</option>
											<option value="Detail" {if in_array('Detail',$item.view)}selected{/if}>{vtranslate('LBL_DETAIL',$QUALIFIED_MODULE)}</option>
											<option value="QuickCreateAjax" {if in_array('QuickCreateAjax',$item.view)}selected{/if}>{vtranslate('LBL_QUICKCREATE',$QUALIFIED_MODULE)}</option>
									</select>
								</td>
							{else}
								<td class="showText">
								<div>
								<button class="btn btn-default editButton">{vtranslate('LBL_EDIT_RECORD', $QUALIFIED_MODULE)}</button>
								<a href="#" class="HelpInfoPopover pull-left padding10" title="" data-placement="left" data-content="{htmlspecialchars(decode_html($item))}" data-original-title='{$langs.label}'><i class="glyphicon glyphicon-info-sign"></i></a>
								</div>
								<textarea id="{$key}_{$lang_key}"
									data-lang="{$key}"
									data-type="php"
									name="{$lang_key}" 
									class="translation form-control {if $item == NULL}empty_value {/if}ckEditorSource ckEditorSmall hide" 
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
