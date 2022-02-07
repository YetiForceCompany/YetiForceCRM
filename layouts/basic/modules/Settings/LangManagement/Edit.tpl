{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="">
	<div class="form-horizontal">
		<div class="form-group row">
			<label for="langs_list" class="col-form-label col-md-1">{\App\Language::translate('Language',$QUALIFIED_MODULE)}:</label>
			<div class="col-md-3">
				<select multiple="multiple" class="form-control select2" id="langs_list">
					{foreach from=$LANGS item=LABEL key=PREFIX}
						<option value="{$PREFIX}" {if in_array($PREFIX,$SELECTED_LANGS)}selected{/if}>{$LABEL}</option>
					{/foreach}
				</select>
			</div>
			<label class="col-md-1 col-form-label">{\App\Language::translate('Module',$QUALIFIED_MODULE)}:</label>
			<div class="col-md-3">
				<select class="form-control mods_list select2" id="mods_list">
					<optgroup label="{\App\Language::translate('Modules',$QUALIFIED_MODULE)}">
						{foreach from=$MODS['mods'] item=MOD key=ID}
							<option value="{$ID}" {if $ID == $SELECTED_MODE}selected{/if}>{\App\Language::translate($MOD,$MOD)}</option>
						{/foreach}
					</optgroup>
					<optgroup label="{\App\Language::translate('LBL_SYSTEM_SETTINGS','Vtiger')}">
						{foreach from=$MODS['settings'] item=MOD key=ID}
							<option value="{$ID}" {if $ID == $SELECTED_MODE}selected{/if}>{\App\Language::translate($MOD,$MOD)}</option>
						{/foreach}
					</optgroup>
				</select>
			</div>
			<div class="checkbox col-md-2">
				<label class="">
					<input type="checkbox" class="show_differences" name="show_differences" {if $SD == 1}checked{/if} value="1">&nbsp;{\App\Language::translate('LBL_SHOW_MISSING_TRANSLATIONS', $QUALIFIED_MODULE)}
				</label>
			</div>
			<div>
				<button class="btn btn-primary js-add-translation {if empty($SELECTED_LANGS)}d-none{/if}" type="button" data-js="click">{\App\Language::translate('LBL_ADD_Translate', $QUALIFIED_MODULE)}</button>
			</div>
		</div>
	</div>
</div>
<br />
{if $DATA}
	<div class="">
		<table class="table table-bordered table-sm listViewEntriesTable">
			<thead>
				<tr class="blockHeader">
					<th><strong>{\App\Language::translate('LBL_variable',$QUALIFIED_MODULE)}</strong></th>
					{foreach from=$DATA['langs'] item=item}
						<th><strong>{$item}</strong></th>
					{/foreach}
					<th></th>
				</tr>
			</thead>
			<tbody>
				{if $DATA['php']}
					{foreach from=$DATA['php'] item=langs key=LANG_KEY}
						{assign var=TEMPDATA value = 1}
						{if $SD == 1}
							{assign var=TEMPDATA value = 0}
							{foreach from=$langs item=item key=lang}
								{if $item == NULL}
									{assign var=TEMPDATA value = 1}
								{/if}
							{/foreach}
						{/if}
						{if $TEMPDATA == 1}
							<tr data-langkey="{$LANG_KEY}" data-type="php">
								<td>{$LANG_KEY}</td>
								{foreach from=$langs item=item key=lang}
									<td><input
											data-lang="{$lang}"
											data-type="php"
											name="{$LANG_KEY}"
											class="translation form-control {if $item == NULL}empty_value{/if}"
											{if $item == NULL} placeholder="{\App\Language::translate('LBL_NoTranslation',$QUALIFIED_MODULE)}" {/if}
											type="text"
											value="{$item}" />
									</td>
								{/foreach}
								<td>
									{if isset($CUSTOM_DATA['php'][$LANG_KEY])}
										<button type="button" class="btn btn-light js-delete" data-js="click" title="{\App\Language::translate('LBL_DELETE')}">
											<span class="fas fa-trash-alt"></span>
										</button>
									{/if}
								</td>
							</tr>
						{/if}
					{/foreach}
				{/if}
				{if $DATA['js']}
					{foreach from=$DATA['js'] item=langs key=LANG_KEY}
						{assign var=TEMPDATA value = 1}
						{if $SD == 1}
							{assign var=TEMPDATA value = 0}
							{foreach from=$langs item=item key=lang}
								{if $item == NULL}
									{assign var=TEMPDATA value = 1}
								{/if}
							{/foreach}
						{/if}
						{if $TEMPDATA == 1}
							<tr data-langkey="{$LANG_KEY}" data-type="js">
								<td>{$LANG_KEY}</td>
								{foreach from=$langs item=item key=lang}
									<td><input
											data-lang="{$lang}"
											data-type="js"
											name="{$LANG_KEY}"
											class="translation form-control {if $item == NULL}empty_value{/if}"
											{if $item == NULL} placeholder="{\App\Language::translate('LBL_NoTranslation',$QUALIFIED_MODULE)}" {/if}
											type="text"
											value="{$item}" />
									</td>
								{/foreach}
								<td>
									{if isset($CUSTOM_DATA['js'][$LANG_KEY])}
										<button type="button" class="btn btn-light js-delete" data-js="click" title="{\App\Language::translate('LBL_DELETE')}">
											<span class="fas fa-trash-alt"></span>
										</button>
									{/if}
								</td>
							</tr>
						{/if}
					{/foreach}
				{/if}
			</tbody>
		</table>
	</div>
{/if}
