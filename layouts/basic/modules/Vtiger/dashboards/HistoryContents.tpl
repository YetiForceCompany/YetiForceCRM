{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<div>
		{if $HISTORIES neq false}
			{foreach key=$index item=HISTORY from=$HISTORIES}
				{assign var=MODELNAME value=get_class($HISTORY)}
				{if $MODELNAME == 'ModTracker_Record_Model'}
					{assign var=MODIFIER_NAME value=\App\Purifier::encodeHtml($HISTORY->getModifierName())}
					{assign var=TIME value=$HISTORY->getActivityTime()}
					{assign var=PARENT value=$HISTORY->getParent()}
					{assign var=MOD_NAME value=$HISTORY->getParent()->getModule()->getName()}
					{assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MOD_NAME}
					{assign var=TRANSLATED_MODULE_NAME value = \App\Language::translate($SINGLE_MODULE_NAME ,$MOD_NAME)}
					{assign var=PROCEED value= TRUE}
					{if ($HISTORY->isRelationLink()) or ($HISTORY->isRelationUnLink())}
						{assign var=RELATION value=$HISTORY->getRelationInstance()}
						{if !($RELATION->getValue())}
							{assign var=PROCEED value= FALSE}
						{/if}
					{/if}
					{if $PROCEED}
						<div class="d-flex">
							<div>
								<span class="yfm-{$MOD_NAME} fa-lg fa-fw" title="{$TRANSLATED_MODULE_NAME}"></span>
							</div>
							<div class="w-100 ml-1">
								<p class="ml-1 float-right text-muted">
									<small>{\App\Fields\DateTime::formatToViewDate("$TIME")}</small>
								</p>
								{assign var=DETAILVIEW_URL value=$PARENT->getDetailViewUrl()}
								{if $HISTORY->isUpdate()}
									{assign var=FIELDS value=$HISTORY->getFieldInstances()}
									<div>
										<div>
											<strong>{$MODIFIER_NAME}&nbsp;</strong>
											{\App\Language::translate('LBL_UPDATED','ModTracker')}&nbsp;
											<a class="u-cursor-pointer" {if stripos($DETAILVIEW_URL, 'javascript:')===0} onclick='{$DETAILVIEW_URL|substr:strlen("javascript:")}' {else} onclick='window.location.href = "{$DETAILVIEW_URL}"' {/if}>
												{$PARENT->getName()}
											</a>
										</div>
										{foreach from=$FIELDS key=INDEX item=FIELD}
											{if $INDEX lt 2}
												{if $FIELD && $FIELD->getFieldInstance() && $FIELD->getFieldInstance()->isViewableInDetailView()}
													<div class='font-x-small'>
														<span>{\App\Language::translate($FIELD->getName(), $FIELD->getModuleName())}</span>
														{if $FIELD->get('prevalue') neq '' && $FIELD->get('postvalue') neq '' && !($FIELD->getFieldInstance()->getFieldDataType() eq 'reference' && ($FIELD->get('postvalue') eq '0' || $FIELD->get('prevalue') eq '0'))}
															&nbsp;{\App\Language::translate('LBL_FROM')}&nbsp; <strong>{Vtiger_Util_Helper::toVtiger6SafeHTML(App\Purifier::decodeHtml($FIELD->getOldValue()))}</strong>
														{else if $FIELD->get('postvalue') neq '' && ($FIELD->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELD->get('postvalue') eq '0')}
															&nbsp; <strong> {\App\Language::translate('LBL_DELETED','ModTracker')} </strong> ( <del>{Vtiger_Util_Helper::toVtiger6SafeHTML(App\Purifier::decodeHtml($FIELD->getOldValue()))}</del> )
														{else if $FIELD->get('postvalue') eq ''}
															&nbsp;
															<strong>{\App\Language::translate('LBL_DELETED_VALUE','ModTracker')}</strong>
															&nbsp;(
															<del>{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELD->getOldValue())}</del>
															)
														{else}
															&nbsp;{\App\Language::translate('LBL_CHANGED')}
														{/if}
														{if $FIELD->get('postvalue') neq '' && !($FIELD->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELD->get('postvalue') eq '0')}
															&nbsp;{\App\Language::translate('LBL_TO')}&nbsp;<strong>{Vtiger_Util_Helper::toVtiger6SafeHTML(App\Purifier::decodeHtml($FIELD->getNewValue()))}</strong>
														{/if}
													</div>
												{/if}
											{else}
												<a class="btn btn-link btn-sm" role="button" href="{$PARENT->getUpdatesUrl()}">{\App\Language::translate('LBL_MORE')}</a>
												{break}
											{/if}
										{/foreach}
									</div>
								{else if ($HISTORY->isRelationLink() || $HISTORY->isRelationUnLink())}
									{assign var=RELATION value=$HISTORY->getRelationInstance()}
									{assign var=LINKED_RECORD_DETAIL_URL value=$RELATION->getDetailViewUrl()}
									{assign var=PARENT_DETAIL_URL value=$RELATION->getParent()->getParent()->getDetailViewUrl()}
									<div>
										<strong>{$MODIFIER_NAME}&nbsp;</strong>
										{\App\Language::translate($HISTORY->getStatusLabel(), 'ModTracker')}&nbsp;
										{if $RELATION->get('targetmodule') eq 'Calendar'}
											{if \App\Privilege::isPermitted('Calendar', 'DetailView', $RELATION->get('targetid'))}
												<a class="u-cursor-pointer" {if stripos($LINKED_RECORD_DETAIL_URL, 'javascript:')===0} onclick='{$LINKED_RECORD_DETAIL_URL|substr:strlen("javascript:")}' {else} onclick='window.location.href = "{$LINKED_RECORD_DETAIL_URL}"' {/if}>
													{$RELATION->getValue()}
												</a>
											{else}
												{\App\Language::translate($RELATION->get('targetmodule'), $RELATION->get('targetmodule'))}
											{/if}
										{else}
											<a class="u-cursor-pointer" {if stripos($LINKED_RECORD_DETAIL_URL, 'javascript:')===0} onclick='{$LINKED_RECORD_DETAIL_URL|substr:strlen("javascript:")}'
												{else} onclick='window.location.href = "{$LINKED_RECORD_DETAIL_URL}"'
												{/if}>
												{\App\Language::translate($RELATION->getValue(), $RELATION->get('targetmodule') )}
											</a>
										{/if}{\App\Language::translate('LBL_FOR')}
										<a class="u-cursor-pointer" {if stripos($PARENT_DETAIL_URL, 'javascript:')===0}
											onclick='{$PARENT_DETAIL_URL|substr:strlen("javascript:")}' {else} onclick='window.location.href = "{$PARENT_DETAIL_URL}"'
											{/if}>
											{$RELATION->getParent()->getParent()->getName()}
										</a>
									</div>
								{else}
									<div>
										<strong>{$MODIFIER_NAME}&nbsp;</strong>{\App\Language::translate($HISTORY->getStatusLabel(), 'ModTracker')}
										<a class="u-cursor-pointer" {if stripos($DETAILVIEW_URL, 'javascript:')===0} onclick='{$DETAILVIEW_URL|substr:strlen("javascript:")}' {else} onclick='window.location.href = "{$DETAILVIEW_URL}"' {/if}>
											&nbsp;{$PARENT->getName()}
										</a>
									</div>
								{/if}
							</div>
						</div>
					{/if}
				{else if $MODELNAME == 'ModComments_Record_Model'}
					{assign var=TRANSLATED_MODULE_NAME value = \App\Language::translate('SINGLE_ModComments' ,'ModComments')}
					<div class="d-flex">
						<div>
							<span class="fas fa-comments fa-lg fa-fw" title="{$TRANSLATED_MODULE_NAME}"></span>
						</div>
						<div class="w-100 ml-1">
							{assign var=COMMENT_TIME value=$HISTORY->getCommentedTime()}
							<p class="float-right text-muted"><small title="{\App\Fields\DateTime::formatToDay("$COMMENT_TIME")}">{\App\Fields\DateTime::formatToViewDate("$COMMENT_TIME")}</small></p>
							<div>
								<strong>{$HISTORY->getCommentatorName()}</strong> {\App\Language::translate('LBL_COMMENTED')} {\App\Language::translate('LBL_ON')} <a class="u-text-ellipsis" href="{$HISTORY->getParentRecordModel()->getDetailViewUrl()}">{$HISTORY->getParentRecordModel()->getName()}</a>
								<span class="ml-2 font-x-small">"{nl2br($HISTORY->getDisplayValue('commentcontent'))}"</span>
							</div>
						</div>
					</div>
				{/if}
			{/foreach}
			{if $NEXTPAGE}
				<button class="load-more badge badge-info" data-page="{$PAGE}" data-nextpage="{$NEXTPAGE}">{\App\Language::translate('LBL_MORE')}</button>
			{/if}
		{else}
			<span class="noDataMsg">
				{\App\Language::translate('LBL_NO_UPDATES_OR_COMMENTS', $MODULE_NAME)}
			</span>
		{/if}
	</div>
{/strip}
