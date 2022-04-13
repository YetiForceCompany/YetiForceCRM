{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-Widget-GeneralInfo -->
	{assign var=TRANSLATED_LABEL value=\App\Language::translate('LBL_RECORD_SUMMARY',$MODULE_NAME)}
	<div class="c-detail-widget c-detail-widget--general-info js-widget-general-info detailViewTable" data-js="edit/save">
		<div class="c-detail-widget__header js-detail-widget-header collapsed border-bottom-0">
			<div class="c-detail-widget__header__container d-flex align-items-center py-1">
				<div class="c-detail-widget__toggle collapsed" id="{$TRANSLATED_LABEL}" data-toggle="collapse" data-target="#{$TRANSLATED_LABEL}-collapse" aria-expanded="false" aria-controls="{$TRANSLATED_LABEL}-collapse">
					<span class="u-transform_rotate-180deg mdi mdi-chevron-down" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
				</div>
				<div class="c-detail-widget__header__title">
					<h5 class="mb-0" title="{$TRANSLATED_LABEL}">{$TRANSLATED_LABEL}</h5>
				</div>
				{if empty($IS_READ_ONLY)}
					<div class="row inline justify-center js-hb__container ml-auto">
						<button type="button" tabindex="0" class="btn js-hb__btn u-hidden-block-btn text-grey-6 py-0 px-1">
							<div class="text-center col items-center justify-center row">
								<i aria-hidden="true" class="mdi mdi-wrench"></i>
							</div>
						</button>
						<div class="u-hidden-block items-center js-comment-actions">
							{assign var="CURRENT_VIEW" value="full"}
							{assign var="CURRENT_MODE_LABEL" value="{\App\Language::translate('LBL_COMPLETE_DETAILS',{$MODULE_NAME})}"}
							<button type="button" class="btn btn-sm btn-light changeDetailViewMode ml-auto">
								<span title="{\App\Language::translate('LBL_SHOW_FULL_DETAILS',$MODULE_NAME)}" class="fas fa-th-list"></span>
							</button>
							{assign var="FULL_MODE_URL" value={$RECORD->getDetailViewUrl()|cat:'&mode=showDetailViewByMode&requestMode=full'}}
							<input type="hidden" name="viewMode" value="{$CURRENT_VIEW}" data-nextviewname="full" data-currentviewlabel="{$CURRENT_MODE_LABEL}" data-full-url="{$FULL_MODE_URL}" />
						</div>
					</div>
				{/if}
			</div>
		</div>
		<div class="c-detail-widget__content js-detail-widget-collapse js-detail-widget-content collapse multi-collapse pt-0{if !empty($IS_READ_ONLY)} show{/if}" id="{$TRANSLATED_LABEL}-collapse" data-storage-key="GeneralInfo" aria-labelledby="{$TRANSLATED_LABEL}"
			data-js="container|value">
			<table class="c-detail-widget__table u-table-fixed">
				<tbody>
					{if !empty($SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'])}
						{foreach item=FIELD_MODEL key=FIELD_NAME from=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS']}
							{if $FIELD_MODEL->getName() neq 'modifiedtime' && $FIELD_MODEL->getName() neq 'createdtime'}
								<tr class="c-table__row--hover {if $FIELD_MODEL->get('hideField')} d-none{/if}">
									<td class="fieldLabel {$WIDTHTYPE}">
										<label class="font-weight-bold mb-0">
											{assign var=ICON value=$FIELD_MODEL->getIcon('GeneralInfo')}
											{if isset($ICON['name'])}<span class="{$ICON['name']} mr-2"></span>{/if}
											{\App\Language::translate($FIELD_MODEL->getFieldLabel(),$MODULE_NAME)}
											{assign var=HELPINFO_LABEL value=\App\Language::getTranslateHelpInfo($FIELD_MODEL,$VIEW)}
											{if $HELPINFO_LABEL}
												<a href="#" class="js-help-info float-right u-cursor-pointer" title="" data-placement="top" data-content="{$HELPINFO_LABEL}"
													data-original-title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}">
													<span class="fas fa-info-circle"></span>
												</a>
											{/if}
										</label>
									</td>
									<td class="fieldValue {$WIDTHTYPE} py-0 u-w-60per">
										<div class="c-detail-widget__header__container d-flex align-items-center px-0">
											<div class="value px-0 w-100" {if $FIELD_MODEL->getUIType() eq '19' or $FIELD_MODEL->getUIType() eq '20' or $FIELD_MODEL->getUIType() eq '21'}style="word-wrap: break-word;white-space:pre-wrap;" {/if}>
												{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD SOURCE_TPL='GeneralInfoWidget'}
											</div>
											{if empty($IS_READ_ONLY) && $FIELD_MODEL->isEditable() eq 'true' && ($FIELD_MODEL->getFieldDataType()!=Vtiger_Field_Model::REFERENCE_TYPE) && $IS_AJAX_ENABLED && $FIELD_MODEL->isAjaxEditable() eq 'true'}
												<div class="d-none edit input-group input-group-sm px-0"> {include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME}
													{if $FIELD_MODEL->getFieldDataType() eq 'boolean' || $FIELD_MODEL->getFieldDataType() eq 'picklist'}
														<input type="hidden" class="fieldname" data-type="{$FIELD_MODEL->getFieldDataType()}" value='{$FIELD_MODEL->getName()}' data-prev-value='{\App\Purifier::encodeHtml($FIELD_MODEL->get('fieldvalue'))}' />
													{else}
														{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD)}
														{if $FIELD_VALUE|is_array}
															{assign var=FIELD_VALUE value=\App\Json::encode($FIELD_VALUE)}
														{/if}
														<input type="hidden" class="fieldname" value='{$FIELD_MODEL->getName()}' data-type="{$FIELD_MODEL->getFieldDataType()}" data-prev-value='{\App\Purifier::encodeHtml($FIELD_VALUE)}' />
													{/if}
												</div>
												<div class="c-table__action--hover js-detail-quick-edit  u-cursor-pointer px-0 ml-1 u-w-fit" data-js="click">
													<button type="button" class="btn btn-sm btn-light float-right">
														<span class="yfi yfi-full-editing-view" title="{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}"></span>
													</button>
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
