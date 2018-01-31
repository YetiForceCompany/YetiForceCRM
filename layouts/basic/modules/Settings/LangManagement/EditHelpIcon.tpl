{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="">
	<div class="form-horizontal">
		<div class="form-group row">
			<label class="control-label col-md-1" >{\App\Language::translate('Language',$QUALIFIED_MODULE)}:</label>
			<div class="col-md-3">
				<select multiple="multiple" class="form-control" id="langs_list">
					{foreach from=$LANGS item=LABEL key=PREFIX}
						<option value="{$PREFIX}" {if in_array($PREFIX,$SELECTED_LANGS)}selected{/if}>{$LABEL}</option>
					{/foreach}
				</select>
			</div>
			<label class="control-label col-md-1" >{\App\Language::translate('Modules',$QUALIFIED_MODULE)}:</label>
			<div class="col-md-3">
				<select class="form-control mods_list" name="mods_list" id="mods_list" data-target="HelpInfo">
					{foreach item=MODULE_INFO from=\vtlib\Functions::getAllModules(true, false, 0)}
						<option value="{$MODULE_INFO['name']}" {if $MODULE_INFO['name'] eq $SELECTED_MODE}selected{/if}>{App\Language::translate($MODULE_INFO['name'], $MODULE_INFO['name'])}</option>
					{/foreach}
				</select>
			</div>
			<div class="checkbox col-md-2">
				<label class=""><input type="checkbox" class="show_differences" name="show_differences" {if $SD eq 1}checked{/if} value="1">{\App\Language::translate('LBL_SHOW_EMPTY_VALUES', $QUALIFIED_MODULE)}</label>
			</div>
		</div>
	</div>
</div>
{if $DATA}
<div class="">
	<table class="table table-bordered table-condensed listViewEntriesTable" >
		<thead>
			<tr class="blockHeader">
				<th class="col-md-1"><strong>{\App\Language::translate('LBL_FIELD_NAME',$QUALIFIED_MODULE)}</strong></th>
				<th class="col-md-2"><strong>{\App\Language::translate('LBL_SHOW_IN',$QUALIFIED_MODULE)}</strong></th>
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
									<select class="helpInfoView form-control" id="helpInfoView" name="helpInfoView" multiple data-fieldid="{$item.fieldid}" placeholder="{\App\Language::translate('LBL_SELECT_OPTION',$QUALIFIED_MODULE)}">
											<option value="Edit" {if in_array('Edit',$item.view)}selected{/if}>{\App\Language::translate('LBL_EDIT',$QUALIFIED_MODULE)}</option>
											<option value="Detail" {if in_array('Detail',$item.view)}selected{/if}>{\App\Language::translate('LBL_DETAIL',$QUALIFIED_MODULE)}</option>
											<option value="QuickCreateAjax" {if in_array('QuickCreateAjax',$item.view)}selected{/if}>{\App\Language::translate('LBL_QUICKCREATE',$QUALIFIED_MODULE)}</option>
									</select>
								</td>
							{else}
								<td class="showText">
								<div>
								<button class="btn btn-light editButton">{\App\Language::translate('LBL_EDIT_RECORD', $QUALIFIED_MODULE)}</button>
								<a href="#" class="HelpInfoPopover float-left padding10" title="" data-placement="left" data-content="{htmlspecialchars(App\Purifier::decodeHtml($item))}" data-original-title='{$langs.label}'><i class="fas fa-info-circle"></i></a>
								</div>
								<textarea id="{$key}_{$lang_key}"
									data-lang="{$key}"
									data-type="php"
									name="{$lang_key}" 
									class="translation form-control {if $item == NULL}empty_value {/if}ckEditorSource ckEditorSmall hide" 
									{if $item == NULL} placeholder="{\App\Language::translate('LBL_NoTranslation',$QUALIFIED_MODULE)}" {/if} 
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
