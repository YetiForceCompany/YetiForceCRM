{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal-header">
		<h5 class="modal-title">{\App\Language::translate('LBL_RECALCULATE_PERMISSION_TITLE', $MODULE)}</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="{\App\Language::translate('LBL_CLOSE')}">
			<span aria-hidden="true" title="{\App\Language::translate('LBL_CLOSE')}">&times;</span>
		</button>
	</div>
	<form class="sendByAjax">
		<input type="hidden" name="action" value="RecalculatePermission" />
		<input type="hidden" name="module" value="{$MODULE_NAME}" />
		<input type="hidden" name="parent" value="Settings" />
		<div class="modal-body row">
			<div class="col-12">
				<div class="col-12">
					<div class="alert alert-info">
						{\App\Language::translate('LBL_RECALCULATE_CRON_INFO', $MODULE)}
					</div>
				</div>
				<div class="col-12">
					<b>{\App\Language::translate('LBL_MODULES_LIST', $MODULE)}</b>
					<select class="select2" name="moduleName">
						{foreach from=$LIST_MODULES key=TABID item=MODULE_INFO}
							<option value="{$MODULE_INFO['name']}">{\App\Language::translate($MODULE_INFO['name'], $MODULE_INFO['name'])}</option>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
		{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
	</form>
{/strip}
