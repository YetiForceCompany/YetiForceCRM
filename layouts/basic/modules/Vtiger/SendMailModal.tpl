{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal-header">
		<button type="button" class="btn btn-warning btn-sm pull-right" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h4 class="modal-title">{vtranslate('LBL_MASS_SEND_EMAIL', $MODULE)}</h4>
	</div>
	<div class="modal-body">
		<div class="alert alert-info" role="alert">
			<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>&nbsp;&nbsp;
			{\App\Language::translate('LBL_MASS_SEND_EMAIL_INFO', $MODULE)}
		</div>
		<form class="form-horizontal validateForm">
			<div class="form-group">
				<label class="col-sm-6 control-label">
					{\App\Language::translate('LBL_NUMBER_OF_SELECTED_RECORDS', $MODULE)}:
				</label>
				<div class="col-sm-6">
					<p class="form-control-static">{$RECORDS['all']}</p>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-6 control-label">
					{\App\Language::translate('LBL_NUMBER_OF_FOUND_MAIL_ADDRESSES', $MODULE)}:
				</label>
				<div class="col-sm-6">
					<div class="form-control-static">{$RECORDS['emails']}</div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label">{\App\Language::translate('LBL_EMAIL_ADRESS')}</label>
				<div class="col-sm-8">
					<select class="select2" id="field" data-validation-engine="validate[required]">
						{foreach item=COUNT key=NAME from=$RECORDS}
							{if $NAME != 'all' && $NAME != 'emails' && $COUNT > 0}
								<option value="{$FIELDS[$NAME]->getName()}">{\App\Language::translate($FIELDS[$NAME]->getFieldLabel(), $MODULE)} ({$COUNT})</option>
							{/if}
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label">{\App\Language::translate('LBL_EMAIL_TEMPLATE')}</label>
				<div class="col-sm-8">
					<select class="select2" id="template" data-validation-engine="validate[required]">
						{foreach item=ROW from=App\Mail::getTempleteList($TEMPLATE_MODULE)}
							<option value="{$ROW['id']}">{$ROW['name']}</option>
						{/foreach}
					</select>
				</div>
			</div>
		</form>
	</div>
	<div class="modal-footer">
		<button class="btn btn-success" type="submit" name="saveButton"><strong>{\App\Language::translate('LBL_SEND')}</strong></button>
		<button class="btn btn-warning" type="reset" data-dismiss="modal"><strong>{\App\Language::translate('LBL_CANCEL')}</strong></button>
	</div>
{/strip}
