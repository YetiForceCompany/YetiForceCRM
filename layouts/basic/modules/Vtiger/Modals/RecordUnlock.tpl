{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<form name="recordState" class="tpl-Base-Modals-RecordState" method="post">
		<input name="module" value="{$MODULE_NAME}" type="hidden">
		<input name="action" value="RecordUnlock" type="hidden">
		<input name="record" value="{$RECORD->getId()}" type="hidden">
		<div class="modal-body">
			{foreach item=FIELD_MODEL from=$LOCK_FIELDS}
				<div class="form-group row">
					<label class="u-text-small-bold col-sm-2 col-form-label">
						{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}
					</label>
					<div class="fieldValue col-sm-10">
						{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME)}
					</div>
				</div>
			{/foreach}
		</div>
		<div class="tpl-Modals-Footer modal-footer">
			<button name="recordState" type="submit" class="btn btn-success">
				<span class="fas fa-check mr-1"></span>
				<strong>
					{\App\Language::translate($BTN_SUCCESS, $MODULE_NAME)}
				</strong>
			</button>
			<button type="reset" class="btn btn-danger" data-dismiss="modal">
				<span class="fas fa-times mr-1"></span>
				<strong>
					{\App\Language::translate($BTN_DANGER, $MODULE_NAME)}
				</strong>
			</button>
		</div>
	</form>
{/strip}
