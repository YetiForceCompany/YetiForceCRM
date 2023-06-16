{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Calendar-ExtraSourcesModal -->
	{function SHOW_DYNAMIC_FIELDS FIELDS_DATA=[]}
		{assign var=MODULE_MODEL value=Vtiger_Module_Model::getInstance($FIELDS_DATA['target_module'])}
		{assign var=CUSTOM_VIEWS value=CustomView_Record_Model::getAllByGroup($MODULE_MODEL->getName())}
		<div class="form-group row">
			<label class="col-sm-4 col-form-label text-right" for="custom_view">
				{\App\Language::translate('LBL_SOURCE_CUSTOM_VIEW', $MODULE_NAME, null, true, 'Calendar')}
				<span class="redColor">*</span>
			</label>
			<div class="col-md-8">
				<select class="select2 form-control" name="custom_view" data-validation-engine="validate[required]">
					{foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
						<optgroup label="{\App\Language::translate($GROUP_LABEL)}">
							{foreach item=CUSTOM_VIEW from=$GROUP_CUSTOM_VIEWS}
								<option value="{$CUSTOM_VIEW->getId()}"
									{if $FIELDS_DATA['custom_view'] eq $CUSTOM_VIEW->getId()} selected="selected" {/if}>
									{\App\Language::translate($CUSTOM_VIEW->get('viewname'), $CUSTOM_VIEW->getModule()->getName())}
									{if $GROUP_LABEL neq 'Mine'} [ {$CUSTOM_VIEW->getOwnerName()} ] {/if}
								</option>
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
			</div>
		</div>
		<div class="form-group row">
			<label class="col-sm-4 col-form-label text-right" for="custom_view">
				{\App\Language::translate('LBL_SOURCE_FIELD_LABEL', $MODULE_NAME, null, true, 'Calendar')}
				<span class="redColor">*</span>
			</label>
			<div class="col-md-8">
				<select class="select2 form-control" name="field_label" data-validation-engine="validate[required]">
					<optgroup>
						<option value="0" {if $FIELDS_DATA['field_label'] eq 0} selected="selected" {/if}>
							{\App\Language::translate('LBL_SOURCE_EMPTY_FIELD_LABEL', $MODULE_NAME, null, true, 'Calendar')}
						</option>
					</optgroup>
					<optgroup label="{\App\Language::translate('LBL_FIELDS')}">
						{foreach from=$MODULE_MODEL->getFields() item=FIELD_MODEL}
							{if $FIELD_MODEL->isActiveField() && $FIELD_MODEL->isViewEnabled()}
								<option value="{$FIELD_MODEL->getId()}"
									{if $FIELDS_DATA['field_label'] eq $FIELD_MODEL->getId()} selected="selected" {/if}>
									{$FIELD_MODEL->getFullLabelTranslation()}
								</option>
							{/if}
						{/foreach}
					</optgroup>
				</select>
			</div>
		</div>
		<div class="form-group row">
			<label class="col-sm-4 col-form-label text-right" for="custom_view">
				{if $FIELDS_DATA['type'] == 1 || $FIELDS_DATA['type'] == 2}
					{\App\Language::translate('LBL_SOURCE_SINGLE_FIELD', $MODULE_NAME, null, true, 'Calendar')}
				{else}
					{\App\Language::translate('LBL_SOURCE_RANGE_FIELDS', $MODULE_NAME, null, true, 'Calendar')}
				{/if}
				<span class="redColor">*</span>
			</label>
			<div class="col-md-8">
				<div class="row">
					{if $FIELDS_DATA['type'] == 1 || $FIELDS_DATA['type'] == 3}
						<div class="col-md-12">
							<select class="select2 form-control" name="fieldid_a_date" data-validation-engine="validate[required]">
								{foreach item=FIELD_MODEL from=$MODULE_MODEL->getFieldsByType(['date','datetime'], true)}
									<option value="{$FIELD_MODEL->getId()}"
										{if $FIELDS_DATA['fieldid_a_date'] eq $FIELD_MODEL->getId()} selected="selected" {/if}>
										{$FIELD_MODEL->getFullLabelTranslation()}
									</option>
								{/foreach}
							</select>
						</div>
					{else}
						{assign var=FIELD_TYPE value='datetime'}
						<div class="col-md-6">
							<select class="select2 form-control" name="fieldid_a_date" data-validation-engine="validate[required]">
								{foreach item=FIELD_MODEL from=$MODULE_MODEL->getFieldsByType('date', true)}
									{if $FIELD_MODEL->isActiveField() && $FIELD_MODEL->isViewEnabled()}
										<option value="{$FIELD_MODEL->getId()}"
											{if $FIELDS_DATA['fieldid_a_date'] eq $FIELD_MODEL->getId()} selected="selected" {/if}>
											{$FIELD_MODEL->getFullLabelTranslation()}
										</option>
									{/if}
								{/foreach}
							</select>
						</div>
						<div class="col-md-6">
							<select class="select2 form-control" name="fieldid_a_time" data-validation-engine="validate[required]">
								{foreach item=FIELD_MODEL from=$MODULE_MODEL->getFieldsByType('time', true)}
									{if $FIELD_MODEL->isActiveField() && $FIELD_MODEL->isViewEnabled()}
										<option value="{$FIELD_MODEL->getId()}"
											{if $FIELDS_DATA['fieldid_a_time'] eq $FIELD_MODEL->getId()} selected="selected" {/if}>
											{$FIELD_MODEL->getFullLabelTranslation()}
										</option>
									{/if}
								{/foreach}
							</select>
						</div>
					{/if}
				</div>
				{if $FIELDS_DATA['type'] == 3 || $FIELDS_DATA['type'] == 4}
					<div class="row mt-2">
						{if $FIELDS_DATA['type'] == 3}
							<div class="col-md-12">
								<select class="select2 form-control" name="fieldid_b_date" data-validation-engine="validate[required]">
									{foreach item=FIELD_MODEL from=$MODULE_MODEL->getFieldsByType(['date','datetime'], true)}
										{if $FIELD_MODEL->isActiveField() && $FIELD_MODEL->isViewEnabled()}
											<option value="{$FIELD_MODEL->getId()}"
												{if $FIELDS_DATA['fieldid_b_date'] eq $FIELD_MODEL->getId()} selected="selected" {/if}>
												{$FIELD_MODEL->getFullLabelTranslation()}
											</option>
										{/if}
									{/foreach}
								</select>
							</div>
						{else}
							{assign var=FIELD_TYPE value='datetime'}
							<div class="col-md-6">
								<select class="select2 form-control" name="fieldid_b_date" data-validation-engine="validate[required]">
									{foreach item=FIELD_MODEL from=$MODULE_MODEL->getFieldsByType('date', true)}
										{if $FIELD_MODEL->isActiveField() && $FIELD_MODEL->isViewEnabled()}
											<option value="{$FIELD_MODEL->getId()}"
												{if $FIELDS_DATA['fieldid_b_date'] eq $FIELD_MODEL->getId()} selected="selected" {/if}>
												{$FIELD_MODEL->getFullLabelTranslation()}
											</option>
										{/if}
									{/foreach}
								</select>
							</div>
							<div class="col-md-6">
								<select class="select2 form-control" name="fieldid_b_time" data-validation-engine="validate[required]">
									{foreach item=FIELD_MODEL from=$MODULE_MODEL->getFieldsByType('time', true)}
										{if $FIELD_MODEL->isActiveField() && $FIELD_MODEL->isViewEnabled()}
											<option value="{$FIELD_MODEL->getId()}"
												{if $FIELDS_DATA['fieldid_b_time'] eq $FIELD_MODEL->getId()} selected="selected" {/if}>
												{$FIELD_MODEL->getFullLabelTranslation()}
											</option>
										{/if}
									{/foreach}
								</select>
							</div>
						{/if}
					</div>
				{/if}
			</div>
		</div>
	{/function}
	{if $IS_DYNAMIC}
		{SHOW_DYNAMIC_FIELDS FIELDS_DATA=$DYNAMIC_FIELDS}
	{else}
		<div class="modal-body js-modal-body mb-0" data-js="container">
			<form class="form-horizontal js-modal-form js-validate-form" data-js="container|validate">
				<input type="hidden" name="module" value="{$MODULE_NAME}" />
				<input type="hidden" name="id" value="{if $SOURCE}{\App\Purifier::encodeHtml($SOURCE->get('id'))}{/if}" />
				<input type="hidden" name="action" value="Calendar" />
				<input type="hidden" name="mode" value="saveExtraSources" />
				<input type="hidden" name="base_module" value="{\App\Module::getModuleId($MODULE_NAME)}" />
				<div class="form-group row">
					<label class="col-sm-4 col-form-label text-right" for="label">
						{\App\Language::translate('LBL_SOURCE_LABEL', $MODULE_NAME, null, true, 'Calendar')}
						<span class="redColor">*</span>
					</label>
					<div class="col-sm-8">
						<input type="text" name="label" class="form-control" value="{if $SOURCE}{\App\Purifier::encodeHtml($SOURCE->get('label'))}{/if}" data-validation-engine="validate[required,max[100]]">
					</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-4 col-form-label text-right" for="type">
						{\App\Language::translate('LBL_SOURCE_TYPE', $MODULE_NAME, null, true, 'Calendar')}
						<span class="redColor">*</span>
					</label>
					<div class="col-md-8">
						<select class="select2 form-control" name="type" data-validation-engine="validate[required]">
							{foreach from=Vtiger_CalendarExtSource_Model::EXTRA_SOURCE_TYPES key=EST_ID item=EST_LABEL}
								<option value="{$EST_ID}" {if $SOURCE && $SOURCE->get('type') == $EST_ID}selected="true" {/if}>
									{\App\Language::translate($EST_LABEL, 'Calendar')}
								</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-4 col-form-label text-right" for="color">
						{\App\Language::translate('LBL_SOURCE_COLOR', $MODULE_NAME, null, true, 'Calendar')}
						<span class="redColor">*</span>
					</label>
					<div class="col-sm-8">
						<div class="input-group input-group-sm">
							<div class="input-group-prepend">
								<span class="input-group-text pl-3 js-color-picker__color" {if $SOURCE}style="background-color: {\App\Purifier::encodeHtml($SOURCE->get('color'))}" {/if}>
								</span>
							</div>
							<input type="text" name="color" class="form-control js-color-picker" value="{if $SOURCE}{\App\Purifier::encodeHtml($SOURCE->get('color'))}{/if}" data-validation-engine="validate[required,max[10]]">
						</div>
					</div>
				</div>
				{if $USER_MODEL->isAdminUser()}
					<div class="form-group row">
						<label class="col-sm-4 col-form-label text-right" for="public">
							{\App\Language::translate('LBL_SOURCE_PUBLIC', $MODULE_NAME, null, true, 'Calendar')}
							<a href="#" class="js-popover-tooltip" data-js="popover" title="" data-placement="auto"
								data-content="{\App\Language::translate('LBL_SOURCE_PUBLIC_DESC', $MODULE_NAME, null, true, 'Calendar')}">
								<span class="fas fa-info-circle ml-1"></span>
							</a>
						</label>
						<div class="col-sm-8">
							<input type="checkbox" name="public" value="1" class="form-control mt-2" {if $SOURCE && $SOURCE->get('public') == 1}checked="checked" {/if}>
						</div>
					</div>
				{/if}
				<div class="form-group row">
					<label class="col-sm-4 col-form-label text-right" for="include_filters">
						{\App\Language::translate('LBL_SOURCE_INCLUDE_FILTERS', $MODULE_NAME, null, true, 'Calendar')}
						<a href="#" class="js-popover-tooltip" data-js="popover" title="" data-placement="auto"
							data-content="{\App\Language::translate('LBL_SOURCE_INCLUDE_FILTERS_DESC', $MODULE_NAME, null, true, 'Calendar')}">
							<span class="fas fa-info-circle ml-1"></span>
						</a>
					</label>
					<div class="col-sm-8">
						<input type="checkbox" name="include_filters" value="1" class="form-control mt-2" {if $SOURCE && $SOURCE->get('include_filters') == 1}checked="checked" {/if}>
					</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-4 col-form-label text-right" for="type">
						{\App\Language::translate('LBL_SOURCE_MODULE', $MODULE_NAME, null, true, 'Calendar')}
						<span class="redColor">*</span>
					</label>
					<div class="col-sm-8">
						<select class="row modules select2 form-control" name="target_module" data-validation-engine="validate[required]" data-js="change">
							{foreach from=\App\Module::getModulesList() item=MODULE_INFO}
								<option value="{$MODULE_INFO['tabid']}" {if $SOURCE && $SOURCE->get('target_module') == $MODULE_INFO['tabid']}selected="true" {/if}>
									{\App\Language::translate($MODULE_INFO['tablabel'], $MODULE_INFO['name'])}
								</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="js-dynamic-fields" data-js="container">
					{if $DYNAMIC_FIELDS}
						{SHOW_DYNAMIC_FIELDS FIELDS_DATA=$DYNAMIC_FIELDS}
					{/if}
				</div>
			</form>
		</div>
	{/if}

	<!-- /tpl-Base-Modals-MeetingModal -->
{/strip}
