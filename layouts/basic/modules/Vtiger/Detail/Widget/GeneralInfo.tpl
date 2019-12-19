{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Base-Detail-Widget-GeneralInfo -->
{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
{if $WIDTHTYPE eq 'narrow'}
	{assign var=WIDTHTYPE_GROUP value="input-group-sm"}
{elseif $WIDTHTYPE eq 'wide'}
	{assign var=WIDTHTYPE_GROUP value="input-group-lg"}
{else}
	{assign var=WIDTHTYPE_GROUP value=''}
{/if}
{assign var=TRANSLATED_LABEL value=\App\Language::translate('LBL_RECORD_SUMMARY',$MODULE_NAME)}
<div class="c-detail-widget c-detail-widget--general-info js-widget-general-info" data-js="edit/save">
	<div class="c-detail-widget__header js-detail-widget-header collapsed border-bottom-0">
		<div class="c-detail-widget__header__container d-flex align-items-center py-1">
			<div class="c-detail-widget__toggle collapsed" id="{$TRANSLATED_LABEL}" data-toggle="collapse" data-target="#{$TRANSLATED_LABEL}-collapse" aria-expanded="false" aria-controls="{$TRANSLATED_LABEL}-collapse">
				<span class="mdi mdi-chevron-up" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
			</div>
			<div class="c-detail-widget__header__title">
				<h5 class="mb-0" title="{$TRANSLATED_LABEL}">{$TRANSLATED_LABEL}</h5>
			</div>
			<div class="c-detail-widget__actions q-fab z-fab row inline justify-center js-fab__container ml-auto quasar-reset">
				<button type="button" tabindex="0" class="js-fab__btn q-btn inline q-btn-item non-selectable no-outline q-btn--flat q-btn--round text-grey-6 q-focusable q-hoverable u-font-size-10px q-ml-auto">
					<div tabindex="-1" class="q-focus-helper"></div>
					<div class="q-btn__content text-center col items-center q-anchor--skip justify-center row">
						<i aria-hidden="true" class="mdi mdi-wrench q-icon"></i>
					</div>
				</button>
				<div class="q-fab__actions flex inline items-center q-fab__actions--left js-comment-actions">
					{if !$IS_READ_ONLY}
						{assign var="CURRENT_VIEW" value="full"}
						{assign var="CURRENT_MODE_LABEL" value="{\App\Language::translate('LBL_COMPLETE_DETAILS',{$MODULE_NAME})}"}
						<button type="button" class="btn btn-sm btn-light changeDetailViewMode ml-auto">
							<span title="{\App\Language::translate('LBL_SHOW_FULL_DETAILS',$MODULE_NAME)}" class="fas fa-th-list"></span>
						</button>
						{assign var="FULL_MODE_URL" value={$RECORD->getDetailViewUrl()|cat:'&mode=showDetailViewByMode&requestMode=full'}}
						<input type="hidden" name="viewMode" value="{$CURRENT_VIEW}" data-nextviewname="full" data-currentviewlabel="{$CURRENT_MODE_LABEL}" data-full-url="{$FULL_MODE_URL}" />
					{/if}
				</div>
			</div>
		</div>
	</div>
	<div class="c-detail-widget__content js-detail-widget-collapse js-detail-widget-content collapse multi-collapse pt-0" id="{$TRANSLATED_LABEL}-collapse" data-storage-key="GeneralInfo" aria-labelledby="{$TRANSLATED_LABEL}"
		data-js="container|value">
		<table class="c-detail-widget__table u-table-fixed">
			<tbody>
				{if !empty($SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'])}
					{foreach item=FIELD_MODEL key=FIELD_NAME from=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS']}
						{if $FIELD_MODEL->getName() neq 'modifiedtime' && $FIELD_MODEL->getName() neq 'createdtime'}
							<tr class="c-table__row--hover">
								<td class="{$WIDTHTYPE}">
									<label class="font-weight-bold mb-0">{\App\Language::translate($FIELD_MODEL->getFieldLabel(),$MODULE_NAME)}
										{assign var=HELPINFO_LABEL value=\App\Language::getTranslateHelpInfo($FIELD_MODEL,$VIEW)}
										{if $HELPINFO_LABEL}
											<a href="#" class="js-help-info float-right u-cursor-pointer" title="" data-placement="top" data-content="{$HELPINFO_LABEL}"
												data-original-title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}">
												<span class="fas fa-info-circle"></span>
											</a>
										{/if}
									</label>
								</td>
								<td class="fieldValue {$WIDTHTYPE} u-w-60per">
									<div class="c-detail-widget__header__container d-flex align-items-center px-0">
										<div class="value px-0 w-100" {if $FIELD_MODEL->getUIType() eq '19' or $FIELD_MODEL->getUIType() eq '20'
											or $FIELD_MODEL->getUIType() eq '21'}style="word-wrap: break-word;white-space:pre-wrap;"{/if}>
											{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName())
											FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD SOURCE_TPL='GeneralInfoWidget'}
										</div>
										{if !$IS_READ_ONLY && $FIELD_MODEL->isEditable() eq 'true' &&
										($FIELD_MODEL->getFieldDataType()!=Vtiger_Field_Model::REFERENCE_TYPE) && $IS_AJAX_ENABLED &&
										$FIELD_MODEL->isAjaxEditable() eq 'true'}
											<div class="d-none edit input-group input-group-sm px-0">
												{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(),
												$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME}
												{if $FIELD_MODEL->getFieldDataType() eq 'boolean' || $FIELD_MODEL->getFieldDataType() eq 'picklist'}
												<input type="hidden" class="fieldname" data-type="{$FIELD_MODEL->getFieldDataType()}" value='{$FIELD_MODEL->getName()}' data-prev-value='{\App\Purifier::encodeHtml($FIELD_MODEL->get(' fieldvalue'))}' />
												{else}
												{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),
												$RECORD)}
												{if $FIELD_VALUE|is_array}
												{assign var=FIELD_VALUE value=\App\Json::encode($FIELD_VALUE)}
												{/if}
												<input type="hidden" class="fieldname" value='{$FIELD_MODEL->getName()}' data-type="{$FIELD_MODEL->getFieldDataType()}" data-prev-value='{\App\Purifier::encodeHtml($FIELD_VALUE)}' />
												{/if}
											</div>
											<div class="c-table__action--hover js-detail-quick-edit  u-cursor-pointer px-0 ml-1 u-w-fit" data-js="click">
												<div class="float-right">
													<span class="fas fa-edit" title="{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}"></span>
												</div>
											</div>
										{/if}
									</div>
								</td>
							</tr>
						{/if}
					{/foreach}
				{/if}
			</tbody>
		</table>
	</div>
</div>
<!-- /tpl-Base-Detail-Widget-GeneralInfo -->
{/strip}
