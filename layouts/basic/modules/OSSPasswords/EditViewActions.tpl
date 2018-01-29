{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<div class="formActionsPanel">
	<button class="btn btn-success generatePass" name="save" type="button">
		<strong>{\App\Language::translate($GENERATEPASS, $MODULE)}</strong>
	</button>&nbsp;
	<button class="btn btn-success" type="submit">
		<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>&nbsp;&nbsp;
		<strong>{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE_NAME)}</strong>
	</button>&nbsp;&nbsp;
	<button class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">
		<span class="fas fa-times"></span>&nbsp;&nbsp;
		<strong>{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE_NAME)}</strong>
	</button>
	{foreach item=LINK from=$EDITVIEW_LINKS['EDIT_VIEW_HEADER']}
		{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='editViewHeader'}
		&nbsp;&nbsp;
	{/foreach}
</div>
</form>
</div>
</div>
{/strip}
