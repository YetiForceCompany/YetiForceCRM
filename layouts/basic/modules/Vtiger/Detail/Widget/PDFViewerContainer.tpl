{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Base-Detail-Widget-PDFViewerContainer -->
{assign var=WIDGET_UID value="id-{\App\Layout::getUniqueId('PDFViewerContainer')}"}
<div class="tpl-Detail-Widget-Basic c-detail-widget js-detail-widget" data-name="{$WIDGET['label']}"  data-type="{$WIDGET['type']}" data-id="{$WIDGET['id']}" data-js="container">
	<div class="widgetContainer_{$key} widgetContentBlock" data-url="{\App\Purifier::encodeHtml($WIDGET['url'])}" data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}" data-id="{$WIDGET['id']}">
		<div class="c-detail-widget__header js-detail-widget-header collapsed" data-js="container|value">
			<div class="c-detail-widget__header__container d-flex align-items-center py-1">
				<div class="c-detail-widget__toggle collapsed" id="{$WIDGET_UID}" data-toggle="collapse" data-target="#{$WIDGET_UID}-collapse" aria-expanded="false" aria-controls="{$WIDGET_UID}-collapse">
					<span class="mdi mdi-chevron-up" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
				</div>
				<div class="c-detail-widget__header__title">
					<h5 class="mb-0 text-truncate">{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}</h5>
				</div>
			</div>
		</div>
		<div class="c-detail-widget__content js-detail-widget-collapse collapse multi-collapse pt-0" id="{$WIDGET_UID}-collapse" data-storage-key="{$WIDGET['id']}" aria-labelledby="{$WIDGET_UID}">
			{if !empty($WIDGET['instance']) && method_exists($WIDGET['instance'], 'getCustomFields')}
				{foreach from=$WIDGET['instance']->getCustomFields() item=FIELD_MODEL}
					{assign var="FIELD_INFO" value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
					<div class="form-group-sm w-100 mr-1 mb-1">
						<select name="{$FIELD_MODEL->getName()}" class="select2 form-control form-control-sm js-filter_field"
							data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}'
							data-return="value" data-urlparams="{$FIELD_MODEL->getName()}" data-js="change">
							<optgroup class="p-0">
								<option value="0">{\App\Language::translate('LBL_SELECT_OPTION')}</option>
							</optgroup>
							{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$FIELD_MODEL->getPicklistValues()}
								<option value="{\App\Purifier::encodeHtml($PICKLIST_NAME)}" {if $FIELD_MODEL->get('fieldvalue') eq
								$PICKLIST_NAME} selected {/if}>{\App\Purifier::encodeHtml($PICKLIST_VALUE)}</option>
							{/foreach}
						</select>
					</div>
				{/foreach}
			{/if}
			<div class="js-detail-widget-content" data-js="container|value"></div>
		</div>
	</div>
</div>
<!-- /tpl-Base-Detail-Widget-PDFViewerContainer -->
{/strip}
