{*<!--
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
-->*}
{strip}
<table class="summary-table" style="width:100%;">
	<tbody>
	{foreach item=FIELD_MODEL key=FIELD_NAME from=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS']}
		{if $FIELD_MODEL->get('name') neq 'modifiedtime' && $FIELD_MODEL->get('name') neq 'createdtime'}
			<tr class="summaryViewEntries">
				<td class="fieldLabel" style="width:35%"><label class="muted">{vtranslate($FIELD_MODEL->get('label'),$MODULE_NAME)}</label></td>
				<td class="fieldValue" style="width:65%">
                    <div class="row">
                       <div class="value textOverflowEllipsis col-xs-10 paddingRightZero" {if $FIELD_MODEL->get('uitype') eq '19' or $FIELD_MODEL->get('uitype') eq '20' or $FIELD_MODEL->get('uitype') eq '21'}style="word-wrap: break-word;white-space:pre-wrap;"{/if}>
                            {include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
                        </div>
                        {if $FIELD_MODEL->isEditable() eq 'true' && ($FIELD_MODEL->getFieldDataType()!=Vtiger_Field_Model::REFERENCE_TYPE) && $IS_AJAX_ENABLED && $FIELD_MODEL->isAjaxEditable() eq 'true' && $FIELD_MODEL->get('uitype') neq 69}
                            <div class="hide edit col-xs-12">
                                {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME}
								{if $FIELD_MODEL->getFieldDataType() eq 'boolean' || $FIELD_MODEL->getFieldDataType() eq 'picklist'}
									<input type="hidden" class="fieldname" data-type="{$FIELD_MODEL->getFieldDataType()}" value='{$FIELD_MODEL->get('name')}' data-prev-value='{$FIELD_MODEL->get('fieldvalue')}' />		
								{else}
									{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId())}
									{if $FIELD_VALUE|is_array}
										{assign var=FIELD_VALUE value=\App\Json::encode($FIELD_VALUE)}
									{/if}
									<input type="hidden" class="fieldname" value='{$FIELD_MODEL->get('name')}' data-type="{$FIELD_MODEL->getFieldDataType()}" data-prev-value='{Vtiger_Util_Helper::toSafeHTML($FIELD_VALUE)}' />
								{/if}
                            </div>
                            <div class="summaryViewEdit col-xs-2 cursorPointer">
								<div class="pull-right">
									<span class="glyphicon glyphicon-pencil" title="{vtranslate('LBL_EDIT',$MODULE_NAME)}"></span>
								</div>
                            </div>
                        {/if}
                    </div>
				</td>
			</tr>
		{/if}
	{/foreach}
	</tbody>
</table>
<hr>
<div class="row">
	<div class="col-md-4 toggleViewByMode">
		{assign var="CURRENT_VIEW" value="full"}
		{assign var="CURRENT_MODE_LABEL" value="{vtranslate('LBL_COMPLETE_DETAILS',{$MODULE_NAME})}"}
		<button type="button" class="btn btn-default changeDetailViewMode cursorPointer"><strong>{vtranslate('LBL_SHOW_FULL_DETAILS',$MODULE_NAME)}</strong></button>
		{assign var="FULL_MODE_URL" value={$RECORD->getDetailViewUrl()|cat:'&mode=showDetailViewByMode&requestMode=full'} }
		<input type="hidden" name="viewMode" value="{$CURRENT_VIEW}" data-nextviewname="full" data-currentviewlabel="{$CURRENT_MODE_LABEL}"
			data-full-url="{$FULL_MODE_URL}"  />
	</div>
	<div class="col-md-8">
		<div class="pull-right">
			<div>
				<p>
					<small>
						{vtranslate('LBL_CREATED_ON',$MODULE_NAME)} {Vtiger_Util_Helper::formatDateTimeIntoDayString($RECORD->get('createdtime'))}
					</small>
				</p>
			</div>
			<div>
				<p>
					<small>
						{vtranslate('LBL_MODIFIED_ON',$MODULE_NAME)} {Vtiger_Util_Helper::formatDateTimeIntoDayString($RECORD->get('modifiedtime'))}
					</small>
				</p>
			</div>
		</div>
	</div>
</div>
{/strip}
