{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
	<div class="recentActivitiesContainer" >
		<input type="hidden" id="updatesCurrentPage" value="{$PAGING_MODEL->get('page')}" />
		<input type="hidden" id="updatesPageLimit" value="{$PAGING_MODEL->getPageLimit()}" />
		<div>
			{if !empty($RECENT_ACTIVITIES)}
				<div id="updates">
					<ul class="list-unstyled">
						{assign var=COUNT value=0}
						{foreach item=RECENT_ACTIVITY from=$RECENT_ACTIVITIES}
							{assign var=PROCEED value= TRUE}
							{if ($RECENT_ACTIVITY->isRelationLink()) or ($RECENT_ACTIVITY->isRelationUnLink())}
								{assign var=RELATION value=$RECENT_ACTIVITY->getRelationInstance()}
								{if !($RELATION->getLinkedRecord())}
									{assign var=PROCEED value= FALSE}
								{/if}
							{/if}
							{if $PROCEED}
								{if $RECENT_ACTIVITY->isReviewed() && $COUNT neq 0}
									<div class="lineOfText">
										<div>{\App\Language::translate('LBL_REVIEWED', $MODULE_BASE_NAME)}</div>
									</div>
								{/if}
								{$COUNT=$COUNT+1}
								{if $RECENT_ACTIVITY->isCreate()}
									<li>
										<div>
											<span>
												<strong>
													{$RECENT_ACTIVITY->getModifiedBy()->getName()}
												</strong>
												{\App\Language::translate($RECENT_ACTIVITY->getStatusLabel(), 'ModTracker', 'ModTracker')}
												{foreach item=FIELDMODEL from=$RECENT_ACTIVITY->getFieldInstances()}
													{if $FIELDMODEL && $FIELDMODEL->getFieldInstance() && $FIELDMODEL->getFieldInstance()->isViewable() && $FIELDMODEL->getFieldInstance()->getDisplayType() neq '5'}
														<div class="font-x-small updateInfoContainer">
															<span>{\App\Language::translate($FIELDMODEL->getName(),$MODULE_NAME)}</span>:&nbsp;
															{if $FIELDMODEL->get('prevalue') neq '' && $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && ($FIELDMODEL->get('postvalue') eq '0' || $FIELDMODEL->get('prevalue') eq '0'))}
																&nbsp;{\App\Language::translate('LBL_FROM')} <strong style="white-space:pre-wrap;">
																	{\App\Language::translate(Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getDisplayValue(App\Purifier::decodeHtml($FIELDMODEL->get('prevalue')))),$MODULE_NAME)}</strong>
																{else if $FIELDMODEL->get('postvalue') eq '' || ($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
																&nbsp; <strong> {\App\Language::translate('LBL_DELETED','ModTracker')} </strong> ( <del>{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getDisplayValue(App\Purifier::decodeHtml($FIELDMODEL->get('prevalue'))))}</del> )
															{else}
																&nbsp;{\App\Language::translate('LBL_CHANGED')}
															{/if}
															{if $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
																&nbsp;{\App\Language::translate('LBL_TO')}&nbsp;<strong style="white-space:pre-wrap;">
																	{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getDisplayValue(App\Purifier::decodeHtml($FIELDMODEL->get('postvalue'))))}</strong>
																{/if}
														</div>
													{/if}
												{/foreach}
											</span>
											<span class="float-right"><p class="muted"><small>{\App\Fields\DateTime::formatToViewDate($RECENT_ACTIVITY->getParent()->get('createdtime'))}</small></p></span>
										</div>
									</li>
								{else if $RECENT_ACTIVITY->isUpdate()}
									<li>
										<div>
											<span><strong>{$RECENT_ACTIVITY->getModifiedBy()->getDisplayName()}</strong> {\App\Language::translate($RECENT_ACTIVITY->getStatusLabel(), 'ModTracker', 'ModTracker')}</span>


											<span class="float-right"><p class="muted"><small>{\App\Fields\DateTime::formatToViewDate($RECENT_ACTIVITY->getActivityTime())}</small></p></span>


										</div>
										{foreach item=FIELDMODEL from=$RECENT_ACTIVITY->getFieldInstances()}
											{if $FIELDMODEL && $FIELDMODEL->getFieldInstance() && $FIELDMODEL->getFieldInstance()->isViewable() && $FIELDMODEL->getFieldInstance()->getDisplayType() neq '5'}
												<div class='font-x-small updateInfoContainer'>
													<span>{\App\Language::translate($FIELDMODEL->getName(),$MODULE_NAME)}</span>:&nbsp;
													{if $FIELDMODEL->get('prevalue') neq '' && $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && ($FIELDMODEL->get('postvalue') eq '0' || $FIELDMODEL->get('prevalue') eq '0'))}
														&nbsp;{\App\Language::translate('LBL_FROM')} <strong style="white-space:pre-wrap;">
															{\App\Language::translate(Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getDisplayValue(App\Purifier::decodeHtml($FIELDMODEL->get('prevalue')))),$MODULE_NAME)}</strong>
														{else if $FIELDMODEL->get('postvalue') eq '' || ($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
														&nbsp; <strong> {\App\Language::translate('LBL_DELETED','ModTracker')} </strong> ( <del>{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getDisplayValue(App\Purifier::decodeHtml($FIELDMODEL->get('prevalue'))))}</del> )
													{else}
														&nbsp;{\App\Language::translate('LBL_CHANGED')}
													{/if}
													{if $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
														&nbsp;{\App\Language::translate('LBL_TO')}&nbsp;<strong style="white-space:pre-wrap;">
															{\App\Language::translate(Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getDisplayValue(App\Purifier::decodeHtml($FIELDMODEL->get('postvalue')))),$MODULE_NAME)}</strong>
														{/if}
												</div>
											{/if}
										{/foreach}
									</li>
								{else if ($RECENT_ACTIVITY->isRelationLink() || $RECENT_ACTIVITY->isRelationUnLink())}
									<li>
										<div>
											{assign var=RELATION value=$RECENT_ACTIVITY->getRelationInstance()}
											<span><strong>{$RECENT_ACTIVITY->getModifiedBy()->getName()} </strong></span>
											<span>{\App\Language::translate($RECENT_ACTIVITY->getStatusLabel(), 'ModTracker')}</span>
											<span>
												{if $RELATION->getLinkedRecord()->getModuleName() eq 'Calendar'}
													{if \App\Privilege::isPermitted('Calendar', 'DetailView', $RELATION->getLinkedRecord()->getId())} <strong>{$RELATION->getLinkedRecord()->getName()}</strong> {else} {/if}
												{else} <strong>{$RELATION->getLinkedRecord()->getName()}</strong> {/if}</span>
											(<span>{\App\Language::translate($RELATION->getLinkedRecord()->getModuleName(), $RELATION->getLinkedRecord()->getModuleName())}</span>)
											<span class="float-right"><p class="muted no-margin"><small>{\App\Fields\DateTime::formatToViewDate($RELATION->get('changedon'))}</small></p></span>
										</div>
									</li>
								{else if $RECENT_ACTIVITY->isDisplayed()}
									<li>
										<div>
											<span>
												<strong>{$RECENT_ACTIVITY->getModifiedBy()->getName()}</strong>
												{\App\Language::translate($RECENT_ACTIVITY->getStatusLabel(), 'ModTracker', 'ModTracker')}
											</span>
											<span class="float-right">
												<p class="muted no-margin">
													<small>{\App\Fields\DateTime::formatToViewDate($RECENT_ACTIVITY->getActivityTime())}
													</small>
												</p>
											</span>
										</div>
									</li>

								{else}
									<li>
										<strong>{\App\Language::translate($RECENT_ACTIVITY->getStatusLabel(), 'ModTracker')}</strong>
									</li>
								{/if}
							{/if}
						{/foreach}
					</ul>
				</div>
			{else}
				<div class="summaryWidgetContainer">
					<p class="textAlignCenter">{\App\Language::translate('LBL_NO_RECENT_UPDATES')}</p>
				</div>
			{/if}
		</div>
		<div class="d-flex py-1 js-more-link">
			{if $PAGING_MODEL->isNextPageExists()}
				<div class="ml-auto">
					<button type="button"
							class="btn btn-primary btn-sm moreRecentUpdates">{\App\Language::translate('LBL_MORE',$MODULE_NAME)}
						..
					</button>
				</div>
			{/if}
		</div>
	</div>
</div>
{/strip}
