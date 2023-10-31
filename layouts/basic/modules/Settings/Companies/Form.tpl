{*<!-- {[The file is published on the basis of YetiForce Public License 6.5 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Companies-Form -->
	<form class="js-validate-form" name="CompanyForm" action="index.php" method="post" id="EditView" enctype="multipart/form-data">
		<input type="hidden" name="parent" value="{$PARENT_MODULE}" />
		<input type="hidden" name="module" value="Companies">
		<input type="hidden" name="action" value="SaveAjax" />
		<div class="alert alert-info" role="alert">
			<span class="u-fs-13px">
				{assign var=CREDITS_LINK value=''}
				{if App\Security\AdminAccess::isPermitted('Dependencies')}
					{assign var=CREDITS_LINK value="<a target=\"_blank\" href=\"index.php?module=Dependencies&view=Credits&parent=Settings&displayLicenseModal=YetiForce\">Link</a>"}
				{/if}
				{App\Language::translateArgs('LBL_CONDITIONS_OF_REGISTRATION', $QUALIFIED_MODULE, $CREDITS_LINK, '')}
			</span>
		</div>
		<div data-js="container">
			{assign var="RECORD" value=Settings_Companies_Record_Model::getInstance()}
			{assign var="FORM_FIELDS" value=$RECORD->getModule()->getFormFields()}
			{foreach key="FIELD_NAME" item="FIELD" from=$FORM_FIELDS name=companyFields}
				{assign var="FIELD_MODEL" value=$RECORD->getFieldInstanceByName($FIELD_NAME, 'LBL_'|cat:$FIELD_NAME|upper)->set('fieldvalue',$RECORD->get($FIELD_NAME))}
				<div class="{if !$smarty.foreach.companyFields.last}form-group{/if} row js-field-block-column">
					<label class="col-lg-3 col-form-label text-left text-lg-right">
						{if $FIELD_MODEL->isMandatory() eq true}
							<span class="redColor">*</span>
						{/if}
						<b>{App\Language::translate('LBL_'|cat:$FIELD_NAME|upper, $QUALIFIED_MODULE)}</b>
						{if isset($FIELD['infoText'])}
							<div class="js-popover-tooltip ml-2 mr-2 d-inline mt-2 text-primary" data-js="popover"
								data-content="{App\Purifier::encodeHtml(App\Language::translate($FIELD['infoText'], $QUALIFIED_MODULE))}">
								<span class="fas fa-info-circle"></span>
							</div>
						{/if}
					</label>
					<div class="col-lg-8">
						{include file=App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) MODULE=$QUALIFIED_MODULE}
					</div>
				</div>
			{/foreach}
			{if empty($IS_MODAL)}
				<div class="row">
					<div class="col-lg-3 text-left text-lg-right" style="align-self: center;">
						<label class="col-form-label">
							<b>{App\Language::translate('LBL_REGISTRATION_STATUS', $QUALIFIED_MODULE)}</b>
						</label>
					</div>
					<div class="col-lg-8 p-sm-2 ml-2 js-status-field">
						<div class="btn-group" role="group">
							<button class="btn btn-secondary js-popover-tooltip js-refresh-status"
								data-js="popover"
								data-content="{App\Purifier::encodeHtml(App\Language::translate('LBL_REFRESH_STATUS', $QUALIFIED_MODULE))}"
								type="button">
								<i class="fas fa-refresh"></i>
							</button>
							<button type="button" class="btn {if $IS_REGISTERED} btn-success{else} btn-danger{/if} js-refresh-status">
								<span class="far fa-circle-{if $IS_REGISTERED}check{else}xmark{/if} mr-1">
								</span>
								{$STATUS}
							</button>
						</div>
						{if !empty($STATUS_ERROR)}
							<span class="text-red ml-2">{\App\Language::translateSingleMod($STATUS_ERROR, 'Other.Exceptions')}</span>
						{/if}
					</div>
				</div>
			{/if}
		</div>
		<hr class="widgetHr" />
		<div class="text-center pt-2">
			{if !empty($IS_MODAL) && !empty($LOCK_EXIT)}
				<a class="btn btn-danger js-post-action mr-2" role="button" href="index.php?module=Users&parent=Settings&action=Logout">
					<span class="fas fa-power-off mr-2"></span><strong>{\App\Language::translate('LBL_SIGN_OUT', $QUALIFIED_MODULE)}</strong>
				</a>
			{/if}
			<button class="btn btn-success mr-1" type="submit">
				<span class="fa fa-check mr-1"></span>{App\Language::translate('LBL_SEND', $QUALIFIED_MODULE)}
			</button>
		</div>
	</form>
	<!-- /tpl-Settings-Companies-Form -->
{/strip}
