{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}

<div class="form-group row">
	<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_NAME', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		<input name="label" class="form-control" type="text" value="{if $RECORD}{$RECORD->get('label')}{/if}" data-validation-engine="validate[required]" />
	</div>
</div>
<div class="form-group row">
	<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_URL', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		<input name="dataurl" class="form-control" type="text" value="{if $RECORD}{\App\Purifier::encodeHtml($RECORD->get('dataurl'))}{/if}" placeholder="https://yetiforce.com" data-validation-engine="validate[required,custom[url]" />
	</div>
</div>
{include file=\App\Layout::getTemplatePath('fields/Newwindow.tpl', $QUALIFIED_MODULE)}
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
