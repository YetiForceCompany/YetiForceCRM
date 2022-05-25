{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=DEFAULT_SMTP value=App\Mail::getDefaultSmtp()}
	{assign var=IS_EMAIL value=false}
	{assign var=EMAILS_NUMBER value=array_sum($EMAILS_BY_FIELD)}
	<div class="modal-header align-items-center">
		<h5 class="modal-title"><span class="fas fa-envelope mr-2"></span>{\App\Language::translate('LBL_MASS_SEND_EMAIL', $MODULE_NAME)}</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="{\App\Language::translate('LBL_CLOSE')}">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	<div class="modal-body">
		<div class="alert alert-info" role="alert">
			<span class="fas fa-info-circle"></span>&nbsp;&nbsp;
			{\App\Language::translate('LBL_MASS_SEND_EMAIL_INFO', $MODULE_NAME)}
			<button type="button" class="close" data-dismiss="alert" aria-label="{\App\Language::translate('LBL_CLOSE')}">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<form class="form-horizontal validateForm">
			<div class="form-group form-row">
				<label class="col-sm-7 col-form-label">
					{\App\Language::translate('LBL_NUMBER_OF_SELECTED_RECORDS', $MODULE_NAME)}:
				</label>
				<div class="col-sm-5">
					<p class="form-control-plaintext">{$RECORDS_NUMBER}</p>
				</div>
			</div>
			{if $RECORDS_NUMBER neq $EMAILS_NUMBER}
				<div class="form-group form-row">
					<label class="col-sm-7 col-form-label">
						{\App\Language::translate('LBL_NUMBER_OF_FOUND_MAIL_ADDRESSES', $MODULE_NAME)}:
					</label>
					<div class="col-sm-5">
						<div class="form-control-plaintext">{$EMAILS_NUMBER}</div>
					</div>
				</div>
			{/if}
			{if $DUPLICATES}
				<div class="form-group form-row">
					<label class="col-sm-7 col-form-label">
						{\App\Language::translate('LBL_NUMBER_OF_FOUND_DUPLICATE_MAIL', $MODULE_NAME)}:
					</label>
					<div class="col-sm-5">
						<div class="form-control-plaintext">{$DUPLICATES}</div>
					</div>
				</div>
			{/if}
			<div class="form-group form-row{if count($FIELDS) === 1} d-none{/if}">
				<label class="col-sm-4 col-form-label">{\App\Language::translate('LBL_EMAIL_ADRESS')}</label>
				<div class="col-sm-8">
					<select class="select2" id="field" data-validation-engine="validate[required]">
						{foreach item=FIELD_MODEL key=NAME from=$FIELDS}
							{if isset($EMAILS_BY_FIELD[$FIELD_MODEL->getName()])}
								<option value="{$FIELD_MODEL->getName()}">{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModuleName())}
									&nbsp;({$EMAILS_BY_FIELD[$FIELD_MODEL->getName()]})
								</option>
								{if $EMAILS_BY_FIELD[$FIELD_MODEL->getName()] > 0}
									{assign var=IS_EMAIL value=true}
								{/if}
							{/if}
						{/foreach}
					</select>
				</div>
			</div>
			{if $EMAIL_LIST}
				<div class="form-group form-row" {\App\Utils::getLocksContent(['copy', 'cut', 'paste', 'contextmenu', 'selectstart'])}>
					<label class="col-sm-4 col-form-label">{\App\Language::translate('LBL_EMAIL_LIST')}</label>
					<div class="col-sm-8">
						<div class="js-scrollbar bg-light u-h-120px card card-body p-1 pl-3" {\App\Utils::getLocksContent(['copy', 'cut', 'paste', 'contextmenu', 'selectstart'])}>
							{foreach key=EMAIL item=COUNT from=$EMAIL_LIST name=emails}
								{if $smarty.foreach.emails.index gt 100}...{break}{/if}
								{$EMAIL}<br>
							{/foreach}
						</div>
					</div>
				</div>
			{/if}
			<div class="form-group form-row">
				<label class="col-sm-4 col-form-label">{\App\Language::translate('LBL_EMAIL_TEMPLATE')}</label>
				<div class="col-sm-8">
					<select class="select2" id="template" data-validation-engine="validate[required]">
						{foreach item=ROW from=$TEMPLATE_LIST}
							<option value="{$ROW['id']}">{$ROW['name']}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group form-row">
				<label class="col-sm-4 col-form-label">{\App\Language::translate('LBL_MASS_MAIL_NOTES')}
					<span class="js-popover-tooltip ml-1" data-toggle="popover"
						data-placement="top"
						data-content="{\App\Language::translate('LBL_MASS_MAIL_NOTES_INFO')}" data-js="popover">
						<span class="fas fa-info-circle"></span>
					</span>
				</label>
				<div class="col-sm-8">
					<textarea class="form-control js-editor js-editor--basic" name="mail_notes" id="mail_notes" data-toolbar="Micro" data-purify-mode="Html" data-js="ckeditor"></textarea>
				</div>
			</div>
		</form>
		{if !$DEFAULT_SMTP}
			<div class="alert alert-danger" role="alert">
				<span class="fas fa-exclamation-circle"></span>&nbsp;&nbsp;
				{\App\Language::translate('ERR_NO_DEFAULT_SMTP')}
			</div>
		{/if}
	</div>
	<div class="modal-footer">
		{if $DEFAULT_SMTP && $TEMPLATE_LIST && $IS_EMAIL}
			<button class="btn btn-success" type="submit" name="saveButton">
				<span class="fas fa-check mr-1"></span>
				<strong>{\App\Language::translate('LBL_SEND')}</strong>
			</button>
		{/if}
		<button class="btn btn-danger" type="reset" data-dismiss="modal">
			<span class="fas fa-times mr-1"></span>
			<strong>{\App\Language::translate('LBL_CANCEL')}</strong>
		</button>
	</div>
{/strip}
