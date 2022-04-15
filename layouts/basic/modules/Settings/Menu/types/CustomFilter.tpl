{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}

<input type="hidden" name="module" value="">
<div class="form-group row">
	<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		<select name="filterId" class="select2 form-control type">
			{foreach from=$MODULE_MODEL->getCustomViewList() item=ITEM}
				<option value="{$ITEM.cvid}" {if $RECORD && $ITEM['cvid'] == $RECORD->get('dataurl')} selected="" {/if} data-tabid="{$ITEM['tabid']}">{\App\Language::translate($ITEM['entitytype'], $ITEM['entitytype'])}: {\App\Language::translate($ITEM['viewname'], $ITEM['entitytype'])}</option>
			{/foreach}
		</select>
	</div>
</div>
{include file=\App\Layout::getTemplatePath('fields/Newwindow.tpl', $QUALIFIED_MODULE)}
{include file=\App\Layout::getTemplatePath('fields/CountEntries.tpl', $QUALIFIED_MODULE)}
{include file=\App\Layout::getTemplatePath('fields/Hotkey.tpl', $QUALIFIED_MODULE)}
<div class="form-group row">
	<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_ICON_NAME', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		<div class="input-group">
			<input name="icon" class="form-control" type="text" value="{if $RECORD}{$RECORD->get('icon')}{/if}" />
			<span class="input-group-btn">
				<button id="selectIconButton" class="btn btn-light" title="{\App\Language::translate('LBL_SELECT_ICON',$QUALIFIED_MODULE)}" type="button"><span class="fas fa-info-circle"></span></button>
			</span>
		</div>
	</div>
</div>
