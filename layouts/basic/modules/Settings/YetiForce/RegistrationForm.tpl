{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-YetiForce-RegistrationForm card js-card-body" data-js="container">
		<div class="card-body">
			{assign var="RECORD_MODEL" value=Settings_Companies_Record_Model::getInstance($company['id'])}
			{foreach key="FIELD_NAME" item="FIELD" from=$RECORD_MODEL->getModule()->getFormFields()}
				{if $FIELD_NAME === 'spacer'}
					<hr/>
					{continue}
				{/if}
				{assign var="FIELD_MODEL" value=$RECORD_MODEL->getFieldInstanceByName($FIELD_NAME, $FIELD)->set('fieldvalue',$RECORD_MODEL->get($FIELD_NAME))}
				<div class="form-group row">
					<label class="col-lg-4 col-form-label text-left text-lg-right">
						<b>
							{if $FIELD_MODEL->isMandatory() eq true}
								<span class="redColor">*</span>
							{/if}
							{App\Language::translate($FIELD_MODEL->getFieldLabel(), $COMPANIES_MODULE)}
						</b>
					</label>
					<div class="col-lg-8">
						{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName())}
					</div>
				</div>
				{if $FIELD_NAME === 'newsletter'}
					<div class="newsletterContent d-none" data-js="class:d-none">
						{elseif $FIELD_NAME === 'email'}
					</div>
				{/if}
			{/foreach}
		</div>
	</div>
{/strip}