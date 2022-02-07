{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}

<div class="form-group row">
	<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		<select name="module" class="select2 type form-control" asfsadf>
			{foreach from=$MODULE_MODEL->getModulesList() item=ITEM}
				<option value="{$ITEM['tabid']}" {if $RECORD && $ITEM['tabid'] == $RECORD->get('module')} selected="" {/if}>{\App\Language::translate($ITEM['name'], $ITEM['name'])}</option>
			{/foreach}
		</select>
	</div>
</div>
<div class="form-group row">
	<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_LABEL_NAME', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		<input name="label" class="form-control" type="text" value="{if $RECORD}{$RECORD->get('label')}{/if}" />
	</div>
</div>
{include file=\App\Layout::getTemplatePath('fields/Newwindow.tpl', $QUALIFIED_MODULE)}
{include file=\App\Layout::getTemplatePath('fields/Hotkey.tpl', $QUALIFIED_MODULE)}
{assign var=FILTERS value=explode(',',$RECORD->get('filters'))}
<div class="form-group row">
	<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_AVAILABLE_FILTERS', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		<div class="input-group">
			<select name="filters" multiple class="select2 type form-control">
				{foreach from=$MODULE_MODEL->getCustomViewList() item=ITEM}
					<option value="{$ITEM.cvid}" {if $RECORD && in_array($ITEM['cvid'],$FILTERS)} selected="" {/if} data-tabid="{$ITEM['tabid']}">{\App\Language::translate($ITEM['viewname'], $ITEM['entitytype'])}</option>
				{/foreach}
			</select>
			<span class="input-group-append">
				<button class="btn btn-light js-popover-tooltip" type="button" data-content="{\App\Language::translate('LBL_CUSTOM_VIEW_FILTER_RESTRICTIONS_DESC', $QUALIFIED_MODULE)}">
					<span class="fas fa-info-circle"></span>
				</button>
			</span>
		</div>
	</div>
</div>
<div class="form-group row">
	<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_ICON_NAME', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		<div class="input-group">
			<input name="icon" class="form-control" type="text" value="{if $RECORD}{$RECORD->get('icon')}{/if}" />
			<span class="input-group-append">
				<button id="selectIconButton" class="btn btn-light" title="{\App\Language::translate('LBL_SELECT_ICON',$QUALIFIED_MODULE)}" type="button"><span class="fas fa-info-circle"></span></button>
			</span>
		</div>
	</div>
</div>
