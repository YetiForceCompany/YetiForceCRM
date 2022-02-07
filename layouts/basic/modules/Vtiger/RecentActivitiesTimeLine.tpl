{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-RecentActivitiesTimeLine recentActivitiesContainer pt-sm-1 pt-0">
		<input type="hidden" id="updatesCurrentPage" value="{$PAGING_MODEL->get('page')}" />
		<input type="hidden" id="updatesPageLimit" value="{$PAGING_MODEL->getPageLimit()}" />
		{if !empty($RECENT_ACTIVITIES)}
			{assign var=LIST_ENTITY_STATE_COLOR value=App\Config::search('LIST_ENTITY_STATE_COLOR')}
			<div id="updates" class="w-100">
				<ul class="timeline">
					{assign var=COUNT value=0}
					{foreach item=RECENT_ACTIVITY from=$RECENT_ACTIVITIES name=recentActivites}
						{assign var=PROCEED value= TRUE}
						{if ($RECENT_ACTIVITY->isRelationLink()) or ($RECENT_ACTIVITY->isRelationUnLink())}
							{assign var=RELATION value=$RECENT_ACTIVITY->getRelationInstance()}
							{if !($RELATION->getValue())}
								{assign var=PROCEED value= FALSE}
							{/if}
						{/if}
						{if $PROCEED}
							{assign var=MODIFIER_IMAGE value=$RECENT_ACTIVITY->getModifiedBy()->getImage()}
							{assign var=MODIFIER_NAME value=\App\Purifier::encodeHtml($RECENT_ACTIVITY->getModifierName())}
							<li>
								{if $RECENT_ACTIVITY->isReviewed() && !($COUNT eq 0 && $PAGING_MODEL->get('page') eq 1)}
									{$NEW_CHANGE = false}
									<div class="lineOfText">
										<div>{\App\Language::translate('LBL_REVIEWED', $MODULE_BASE_NAME)}</div>
									</div>
								{/if}
								{$COUNT=$COUNT+1}
								{if $RECENT_ACTIVITY->isCreate()}
									<div class="d-flex">
										<span class="c-circle-icon mt-2 bg-success d-sm-inline d-none text-center"
											style="background-color: {ModTracker::$colorsActions[$RECENT_ACTIVITY->get('status')]} !important;">
											<span class="{ModTracker::$iconActions[$RECENT_ACTIVITY->get('status')]} fa-fw text-light mt-2"></span>
										</span>
										<div class="flex-grow-1 ml-1 p-1 timeline-item {if $NEW_CHANGE} bgWarning{/if} isCreate">
											<div class="float-sm-left imageContainer d-sm-block d-none text-center">
												{if $MODIFIER_IMAGE}
													<img class="userImage" src="{$MODIFIER_IMAGE['url']}">
												{else}
													<span class="fas fa-user userImage"></span>
												{/if}
											</div>
											<div class="timeline-body small">
												<strong>{$MODIFIER_NAME}</strong>
												&nbsp;{\App\Language::translate($RECENT_ACTIVITY->getStatusLabel(), 'ModTracker')}
												<div class="float-right time text-muted ml-1">{\App\Fields\DateTime::formatToViewDate($RECENT_ACTIVITY->getParent()->get('createdtime'))}</div>
												<div>
													{foreach item=FIELDMODEL from=$RECENT_ACTIVITY->getFieldInstances()}
														{if $FIELDMODEL && $FIELDMODEL->getFieldInstance() && $FIELDMODEL->getFieldInstance()->isViewable() && $FIELDMODEL->getFieldInstance()->getDisplayType() neq '5'}
															<div class='font-x-small updateInfoContainer d-flex flex-wrap'>
																<span>{\App\Language::translate($FIELDMODEL->getName(),$MODULE_NAME)}</span>:&nbsp;
																{if $FIELDMODEL->get('postvalue') neq ''}
																	<strong>{$FIELDMODEL->getNewValue()}</strong>
																{/if}
															</div>
														{/if}
													{/foreach}
													{include file=\App\Layout::getTemplatePath('RecentActivitiesInventory.tpl', $MODULE_NAME)}
												</div>
											</div>
										</div>
									</div>
								{else if $RECENT_ACTIVITY->isUpdate() || $RECENT_ACTIVITY->isTransferEdit()}
									<div class="d-flex">
										<div class="c-circle-icon mt-2 d-sm-inline d-none text-center"
											style="background-color: {ModTracker::$colorsActions[$RECENT_ACTIVITY->get('status')]};">
											<div class="{ModTracker::$iconActions[$RECENT_ACTIVITY->get('status')]} fa-fw text-light mt-2 mx-auto"></div>
										</div>
										<div class="flex-grow-1 ml-sm-1 ml-0 p-1 timeline-item{if $NEW_CHANGE} bgWarning{/if} isUpdate">
											<div class="float-sm-left imageContainer d-sm-block d-none text-center">
												{if $MODIFIER_IMAGE}
													<img class="userImage" src="{$MODIFIER_IMAGE['url']}">
												{else}
													<span class="fas fa-user userImage"></span>
												{/if}
											</div>
											<div class="timeline-body small">
												<strong>{$MODIFIER_NAME}
													&nbsp;</strong> {\App\Language::translate($RECENT_ACTIVITY->getStatusLabel(),'ModTracker')}
												<div class="float-right time text-muted ml-1">{\App\Fields\DateTime::formatToViewDate($RECENT_ACTIVITY->getActivityTime())}</div>
												<div>
													{foreach item=FIELDMODEL from=$RECENT_ACTIVITY->getFieldInstances()}
														{if $FIELDMODEL && $FIELDMODEL->getFieldInstance() && $FIELDMODEL->getFieldInstance()->isViewable() && $FIELDMODEL->getFieldInstance()->getDisplayType() neq '5'}
															<div class="font-x-small updateInfoContainer">
																<span>{\App\Language::translate($FIELDMODEL->getName(),$MODULE_NAME)}</span>:&nbsp;
																{if $FIELDMODEL->get('prevalue') neq '' && $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && ($FIELDMODEL->get('postvalue') eq '0' || $FIELDMODEL->get('prevalue') eq '0'))}
																	&nbsp;{\App\Language::translate('LBL_FROM')}&nbsp;
																	{if $FIELDMODEL->get('postvalue') neq ''}
																		<strong>{$FIELDMODEL->getOldValue()}</strong>
																	{/if}
																{else if $FIELDMODEL->get('postvalue') neq '' && ($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
																	&nbsp;
																	<strong>{\App\Language::translate('LBL_DELETED','ModTracker')}</strong>
																	&nbsp;(
																	<del>{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getOldValue())}</del>
																	)
																{else if $FIELDMODEL->get('postvalue') eq ''}
																	&nbsp;
																	<strong>{\App\Language::translate('LBL_DELETED_VALUE','ModTracker')}</strong>
																	&nbsp;(
																	<del>{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getOldValue())}</del>
																	)
																{else}
																	&nbsp;{\App\Language::translate('LBL_CHANGED')}
																{/if}
																{if $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
																	&nbsp;{\App\Language::translate('LBL_TO')}&nbsp;<strong>{$FIELDMODEL->getNewValue()}</strong>
																{/if}
															</div>
														{/if}
													{/foreach}
													{include file=\App\Layout::getTemplatePath('RecentActivitiesInventory.tpl', $MODULE_NAME)}
												</div>
											</div>
										</div>
									</div>
								{elseif ($RECENT_ACTIVITY->isRelationLink() || $RECENT_ACTIVITY->isRelationUnLink() || $RECENT_ACTIVITY->isTransferLink() || $RECENT_ACTIVITY->isTransferUnLink())}
									<div class="d-flex">
										<div class="c-circle-icon mt-2 d-sm-inline d-none text-center"
											style="background-color: {ModTracker::$colorsActions[$RECENT_ACTIVITY->get('status')]};">
											<div class="{ModTracker::$iconActions[$RECENT_ACTIVITY->get('status')]} fa-fw text-light mt-2 mx-auto"></div>
										</div>
										<div class="flex-grow-1 ml-1 p-1 timeline-item{if $NEW_CHANGE} bgWarning{/if} isRelationLink isRelationUnLink">
											<div class="float-sm-left imageContainer d-sm-block d-none text-center">
												{if $MODIFIER_IMAGE}
													<img class="userImage" src="{$MODIFIER_IMAGE['url']}">
												{else}
													<span class="fas fa-user userImage"></span>
												{/if}
											</div>
											<div class="timeline-body small">
												<div class="float-right time text-muted ml-1">{\App\Fields\DateTime::formatToViewDate($RECENT_ACTIVITY->getActivityTime())}</div>
												<span>
													<strong>{$MODIFIER_NAME}&nbsp;</strong>
												</span>
												{assign var=RELATION value=$RECENT_ACTIVITY->getRelationInstance()}
												<span>{\App\Language::translate($RECENT_ACTIVITY->getStatusLabel(),'ModTracker')}
													&nbsp;</span>
												<span>
													{if \App\Privilege::isPermitted($RELATION->get('targetmodule'), 'DetailView', $RELATION->get('targetid'))}
														<strong class="js-more-content">
															<span class="teaserContent">
																{\App\Utils\Completions::decode(Vtiger_Util_Helper::toVtiger6SafeHTML(\App\Purifier::decodeHtml($RELATION->getValue())))}</span>
															{if $RELATION->has('fullValue')}
																<span class="fullContent d-none">{$RELATION->get('fullValue')}</span>
																<a href="#" class="js-more font-weight-lighter">{\App\Language::translate('LBL_MORE_BTN')}</a>
															{/if}
														</strong>
													{/if}
												</span>
												<span>&nbsp;({\App\Language::translate('SINGLE_'|cat:$RELATION->get('targetmodule'), $RELATION->get('targetmodule'))}
													)</span>
											</div>
										</div>
									</div>
								{else if $RECENT_ACTIVITY->isChangeState() || $RECENT_ACTIVITY->isTransferDelete()}
									<div class="d-flex">
										<div class="c-circle-icon mt-2 d-sm-inline d-none text-center"
											style="background-color: {ModTracker::$colorsActions[$RECENT_ACTIVITY->get('status')]};">
											<div class="{ModTracker::$iconActions[$RECENT_ACTIVITY->get('status')]} fa-fw text-light mt-2 mx-auto"></div>
										</div>
										<div class="flex-grow-1 ml-1 p-1 timeline-item isDisplayed">
											<div class="imageContainer float-left d-sm-block d-none text-center">
												{if $MODIFIER_IMAGE}
													<img class="userImage" src="{$MODIFIER_IMAGE['url']}">
												{else}
													<span class="fas fa-user userImage"></span>
												{/if}
											</div>
											<div class="timeline-body small">
												<div class="float-right time text-muted ml-1">{\App\Fields\DateTime::formatToViewDate($RECENT_ACTIVITY->getActivityTime())}</div>
												<div><strong>{$MODIFIER_NAME}</strong>&nbsp;{\App\Language::translate($RECENT_ACTIVITY->getStatusLabel(), 'ModTracker')}
												</div>
											</div>
										</div>
									</div>
								{else if $RECENT_ACTIVITY->isConvertToAccount()}
									<div class="d-flex">
										<div class="c-circle-icon mt-2 d-sm-inline d-none text-center"
											style="background-color: {ModTracker::$colorsActions[$RECENT_ACTIVITY->get('status')]};">
											<div class="{ModTracker::$iconActions[$RECENT_ACTIVITY->get('status')]} fa-fw text-light mt-2 mx-auto"></div>
										</div>
										<div class="flex-grow-1 ml-1 p-1 timeline-item{if $NEW_CHANGE} bgWarning{/if} isConvertToAccount">
											<div class="float-left imageContainer d-sm-block d-none text-center">
												{if $MODIFIER_IMAGE}
													<img class="userImage" src="{$MODIFIER_IMAGE['url']}">
												{else}
													<span class="fas fa-user userImage"></span>
												{/if}
											</div>
											<div class="timeline-body small">
												<div><strong>{$MODIFIER_NAME}</strong>&nbsp;{\App\Language::translate($RECENT_ACTIVITY->getStatusLabel(), 'ModTracker')}
												</div>
												<span class="time float-right"><span>{\App\Fields\DateTime::formatToViewDate($RECENT_ACTIVITY->getActivityTime())}</span></span>
											</div>
										</div>
									</div>
								{else if $RECENT_ACTIVITY->isDisplayed() || $RECENT_ACTIVITY->isShowHiddenData()}
									<div class="d-flex">
										<div class="c-circle-icon mt-2 d-sm-inline d-none text-center"
											style="background-color: {ModTracker::$colorsActions[$RECENT_ACTIVITY->get('status')]};">
											<div class="{ModTracker::$iconActions[$RECENT_ACTIVITY->get('status')]} fa-fw text-light mt-2 mx-auto"></div>
										</div>
										<div class="flex-grow-1 ml-1 p-1 timeline-item isDisplayed">
											<div class="float-left imageContainer d-sm-block d-none text-center">
												{if $MODIFIER_IMAGE}
													<img class="userImage" src="{$MODIFIER_IMAGE['url']}">
												{else}
													<span class="fas fa-user userImage"></span>
												{/if}
											</div>
											<div class="timeline-body small">
												<div class="float-left">
													<strong>{$MODIFIER_NAME}</strong>
													&nbsp;{\App\Language::translate($RECENT_ACTIVITY->getStatusLabel(), 'ModTracker')}
												</div>
												<span class="time float-right"><span>{\App\Fields\DateTime::formatToViewDate($RECENT_ACTIVITY->getActivityTime())}</span></span>
											</div>
										</div>
									</div>
								{/if}
							</li>
						{/if}
					{/foreach}
				</ul>
			</div>
		{else}
			<div class="summaryWidgetContainer">
				<p class="textAlignCenter">{\App\Language::translate('LBL_NO_RECENT_UPDATES')}</p>
			</div>
		{/if}
		<input type="hidden" id="newChange" value="{$NEW_CHANGE}" />
		<div class="d-flex pt-0 pb-2 px-0 js-more-link">
			{if !$IS_READ_ONLY && $PAGING_MODEL->isNextPageExists()}
				<div class="ml-auto">
					<button type="button" class="btn btn-link btn-sm moreRecentUpdates">{\App\Language::translate('LBL_MORE',$MODULE_NAME)} ..</button>
				</div>
			{/if}
		</div>
	</div>
{/strip}
