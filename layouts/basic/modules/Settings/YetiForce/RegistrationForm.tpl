{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-YetiForce-RegistrationForm card js-card-body" data-js="container">
		<div class="card-body">
			{if !empty($COMPANY_ID)}
				{assign var="RECORD" value=Settings_Companies_Record_Model::getInstance($COMPANY_ID)}
			{else}
				{assign var="RECORD" value=Settings_Companies_Record_Model::getCleanInstance()}
			{/if}
			{assign var="SOURCE_MODULE" value=$RECORD->set('SOURCE_MODULE',$MODULE_NAME)}
			{assign var="MODULE_TRANSLATION" value="Settings::Companies"}
			{foreach key="FIELD_NAME" item="FIELD" from=$RECORD->getModule()->getFormFields()}
				{if $MODULE_NAME === 'YetiForce' && $FIELD['registerView'] === false}
					{continue}
				{/if}
				{if $FIELD_NAME === 'spacer'}
					<hr/>
					{continue}
				{elseif $FIELD_NAME === 'type'}
					<div class="form-group row">
						<label class="col-lg-4 col-form-label text-left text-lg-right">
							<b>{App\Language::translate($FIELD['label'], $MODULE_TRANSLATION)}</b>
						</label>
						<div class="col-lg-8">
							<div class="btn-group btn-group-toggle" data-toggle="buttons">
								<label class="btn btn-sm btn-outline-primary{if $RECORD->get('type')===1} active{/if}"
									   for="option1">
									<input value="1" type="radio" name="type" id="option1"
										   data-validation-engine="validate[required]"
										   autocomplete="off"{if $RECORD->get('type')==1} checked{/if}>
									{\App\Language::translate('LBL_TYPE_TARGET_USER',$MODULE_TRANSLATION)}
								</label>
								<label class="btn btn-sm btn-outline-primary{if $RECORD->get('type')===2} active{/if}"
									   for="option2">
									<input value="2" type="radio" name="type" id="option2"
										   data-validation-engine="validate[required]"
										   autocomplete="off"{if $RECORD->get('type')==2} checked{/if}>
									{\App\Language::translate('LBL_TYPE_INTEGRATOR',$MODULE_TRANSLATION)}
								</label>
								<label class="btn btn-sm btn-outline-primary{if $RECORD->get('type')===3} active{/if}"
									   for="option3">
									<input value="3" type="radio" name="type" id="option3"
										   data-validation-engine="validate[required]"
										   autocomplete="off"{if $RECORD->get('type')==3} checked{/if}>
									{\App\Language::translate('LBL_TYPE_PROVIDER',$MODULE_TRANSLATION)}
								</label>
							</div>
						</div>
					</div>
				{elseif $FIELD_NAME === 'logo'}
					<div class="form-group row">
						<div class="col-lg-4 col-form-label text-left text-lg-right">
							<b>{$RECORD->getDisplayValue($FIELD_NAME)}</b>
						</div>
						<div class="col-lg-8 d-flex">
							<div class="u-h-fit my-auto">
								<input type="file" name="{$FIELD_NAME}" id="{$FIELD_NAME}"/>&nbsp;&nbsp;
							</div>
						</div>
					</div>
				{else}
					{assign var="FIELD_MODEL" value=$RECORD->getFieldInstanceByName($FIELD_NAME, $FIELD['label'])->set('fieldvalue',$RECORD->get($FIELD_NAME))}
					<div class="form-group row">
						<label class="col-lg-4 col-form-label text-left text-lg-right">
							{if $FIELD_NAME === 'newsletter'}
								<div class="js-popover-tooltip ml-2 mr-2 d-inline mt-2" data-js="popover"
									 data-content="{\App\Purifier::encodeHtml(App\Language::translateArgs("LBL_EMAIL_NEWSLETTER_INFO", $MODULE_TRANSLATION,"<a href=\"https://yetiforce.com/pl/newsletter-info\">{App\Language::translate('LBL_PRIVACY_POLICY', $MODULE_TRANSLATION)}</a>"))}">
									<span class="fas fa-info-circle"></span>
								</div>
							{/if}
							{if $FIELD_MODEL->isMandatory() eq true}
								<span class="redColor">*</span>
							{/if}
							<b>{App\Language::translate($FIELD['label'], $MODULE_TRANSLATION)}</b>
						</label>
						<div class="col-lg-8">
							{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName())}
						</div>
					</div>
				{/if}
				{if $FIELD_NAME === 'newsletter'}
					<div class="newsletterContent d-none" data-js="class:d-none">
						{elseif $FIELD_NAME === 'email'}
					</div>
				{/if}
			{/foreach}
		</div>
	</div>
{/strip}