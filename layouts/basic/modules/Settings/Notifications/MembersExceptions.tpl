{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<form id="exceptionsView" class="form-horizontal">
		<input type="hidden" id="srcModule" name="srcModule" value="{$SRC_MODULE}" />
		<input type="hidden" id="member" name="member" value={$MEMBER} />
		<input type="hidden" id="mode" name="mode" value="exceptions" />
		<div class="modal-header">
			<h5 class="modal-title">{\App\Language::translate('LBL_EXCEPTIONS', $QUALIFIED_MODULE)}</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="{\App\Language::translate('LBL_CLOSE')}">
				<span aria-hidden="true" title="{\App\Language::translate('LBL_CLOSE')}">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<div class="form-group">
				<div class="col-12">
					<label class="col-form-label">
						{\App\Language::translate('LBL_SELECT_USER', $QUALIFIED_MODULE)}
					</label>
					<select id="exceptions" class="select2 form-control" multiple="true" name="exceptions[]">
						{foreach from=\App\PrivilegeUtil::getUserByMember($MEMBER) item=USER_ID}
							<option value="{$USER_ID}" {if in_array($USER_ID, $MEMBERS)}selected{/if}>{\App\Fields\Owner::getUserLabel($USER_ID)}</option>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
		{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
	</form>
{/strip}
