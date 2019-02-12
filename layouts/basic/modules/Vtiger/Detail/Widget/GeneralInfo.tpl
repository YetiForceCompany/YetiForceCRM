{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-Widget-GeneralInfo -->
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
	<div class="c-detail-widget u-mb-13px c-detail-widget--general-info js-widget-general-info" data-js="edit/save">
		<div>
			<div class="c-detail-widget__header">
				<h5 class="mb-0 py-2"> {\App\Language::translate('LBL_RECORD_SUMMARY',$MODULE_NAME)}</h5>
			</div>
		</div>
		<div class="c-detail-widget__content table-responsive-sm">
			<table class="c-detail-widget__table">
				<tbody>
				{if !empty($SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'])}
					{foreach item=FIELD_MODEL key=FIELD_NAME from=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS']}
						{if $FIELD_MODEL->getName() neq 'modifiedtime' && $FIELD_MODEL->getName() neq 'createdtime'}
							<tr class="c-table__row--hover">
								<td class="{$WIDTHTYPE}">
									<label class="font-weight-bold">{\App\Language::translate($FIELD_MODEL->getFieldLabel(),$MODULE_NAME)}
										{assign var=HELPINFO value=explode(',',$FIELD_MODEL->get('helpinfo'))}
										{assign var=HELPINFO_LABEL value=$MODULE_NAME|cat:'|'|cat:$FIELD_MODEL->getFieldLabel()}
										{if in_array($VIEW,$HELPINFO) && \App\Language::translate($HELPINFO_LABEL, 'HelpInfo') neq $HELPINFO_LABEL}
											<a href="#" class="js-help-info pl-1" title="" data-placement="top"
											   data-content="{\App\Language::translate($HELPINFO_LABEL, 'HelpInfo')}"
											   data-original-title='{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}'><span
														class="fas fa-info-circle"></span></a>
										{/if}
									</label>
								</td>
								<td class="fieldValue {$WIDTHTYPE}">
									<div class="row">
										<div class="value col-10"
											 {if $FIELD_MODEL->getUIType() eq '19' or $FIELD_MODEL->getUIType() eq '20' or $FIELD_MODEL->getUIType() eq '21'}style="word-wrap: break-word;white-space:pre-wrap;"{/if}>
											{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
										</div>
										{if !$IS_READ_ONLY && $FIELD_MODEL->isEditable() eq 'true' && ($FIELD_MODEL->getFieldDataType()!=Vtiger_Field_Model::REFERENCE_TYPE) && $IS_AJAX_ENABLED && $FIELD_MODEL->isAjaxEditable() eq 'true'}
											<div class="d-none edit col-12">
												{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME}
												{if $FIELD_MODEL->getFieldDataType() eq 'boolean' || $FIELD_MODEL->getFieldDataType() eq 'picklist'}
													<input type="hidden" class="fieldname"
														   data-type="{$FIELD_MODEL->getFieldDataType()}"
														   value='{$FIELD_MODEL->getName()}'
														   data-prev-value='{\App\Purifier::encodeHtml($FIELD_MODEL->get('fieldvalue'))}'/>
												{else}
													{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD)}
													{if $FIELD_VALUE|is_array}
														{assign var=FIELD_VALUE value=\App\Json::encode($FIELD_VALUE)}
													{/if}
													<input type="hidden" class="fieldname"
														   value='{$FIELD_MODEL->getName()}'
														   data-type="{$FIELD_MODEL->getFieldDataType()}"
														   data-prev-value='{\App\Purifier::encodeHtml($FIELD_VALUE)}'/>
												{/if}
											</div>
											<div class="c-table__action--hover js-detail-quick-edit col-2 u-cursor-pointer"
												 data-js="click">
												<div class="float-right">
													<span class="fas fa-edit"
														  title="{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}"></span>
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
		<hr>
		<div class="d-flex flex-wrap justify-content-between">
			<div class="toggleViewByMode">
				{if !$IS_READ_ONLY}
					{assign var="CURRENT_VIEW" value="full"}
					{assign var="CURRENT_MODE_LABEL" value="{\App\Language::translate('LBL_COMPLETE_DETAILS',{$MODULE_NAME})}"}
					<button type="button"
							class="btn btn-outline-secondary btn-block changeDetailViewMode u-cursor-pointer">
						<strong>{\App\Language::translate('LBL_SHOW_FULL_DETAILS',$MODULE_NAME)}</strong></button>
					{assign var="FULL_MODE_URL" value={$RECORD->getDetailViewUrl()|cat:'&mode=showDetailViewByMode&requestMode=full'} }
					<input type="hidden" name="viewMode" value="{$CURRENT_VIEW}" data-nextviewname="full"
						   data-currentviewlabel="{$CURRENT_MODE_LABEL}" data-full-url="{$FULL_MODE_URL}"/>
				{/if}
			</div>
			<div>
				<p>
					<small>
						{\App\Language::translate('LBL_CREATED_ON',$MODULE_NAME)} {\App\Fields\DateTime::formatToDay($RECORD->get('createdtime'))}
					</small>
					<br/>
					<small>
						{\App\Language::translate('LBL_MODIFIED_ON',$MODULE_NAME)} {\App\Fields\DateTime::formatToDay($RECORD->get('modifiedtime'))}
					</small>
				</p>
			</div>
		</div>
	</div>
	<!-- /tpl-Base-Detail-Widget-GeneralInfo -->
{/strip}
