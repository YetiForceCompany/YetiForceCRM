{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="recentActivitiesContainer pt-2">
		<input type="hidden" id="updatesCurrentPage" value="{$PAGING_MODEL->get('page')}" />
		<input type="hidden" id="updatesPageLimit" value="{$PAGING_MODEL->getPageLimit()}" />
		{if !empty($RECENT_ACTIVITIES)}
			{assign var=LIST_ENTITY_STATE_COLOR value=AppConfig::search('LIST_ENTITY_STATE_COLOR')}
			<div id="updates" class="w-100">
				<ul class="timeline">
					{assign var=COUNT value=0}
					{foreach item=RECENT_ACTIVITY from=$RECENT_ACTIVITIES name=recentActivites}
						{assign var=PROCEED value= TRUE}
						{if ($RECENT_ACTIVITY->isRelationLink()) or ($RECENT_ACTIVITY->isRelationUnLink())}
							{assign var=RELATION value=$RECENT_ACTIVITY->getRelationInstance()}
							{if !($RELATION->getLinkedRecord())}
								{assign var=PROCEED value= FALSE}
							{/if}
						{/if}
						{if $PROCEED}
							<li>
								{if $RECENT_ACTIVITY->isReviewed() && !($COUNT eq 0 && $PAGING_MODEL->get('page') eq 1)}
									{$NEW_CHANGE = false}
									<div class="lineOfText"><div>{\App\Language::translate('LBL_REVIEWED', $MODULE_BASE_NAME)}</div></div>
										{/if}
										{$COUNT=$COUNT+1}
										{if $RECENT_ACTIVITY->isCreate()}
									<div class="row">
										<span class="fa-layers fa-fw fa-2x ml-10px">
											<span class="fas fa-circle text-success"></span>
											<span class="fas fa-plus text-light" data-fa-transform="shrink-8"></span>
										</span>
										<div class="col-11 ml-1 p-1 timeline-item {if $NEW_CHANGE} bgWarning{/if} isCreate">
											<div class="float-left imageContainer">
												{assign var=IMAGE value=$RECENT_ACTIVITY->getModifiedBy()->getImagePath()}
												{if $IMAGE}
													<img class="userImage" src="data:image/jpg;base64,{base64_encode(file_get_contents($IMAGE))}" >
												{else}
													<span class="fas fa-user userImage"></span>
												{/if}
											</div>
											<div class="timeline-body small">
												<strong>{$RECENT_ACTIVITY->getModifiedBy()->getName()}</strong>
												&nbsp;{\App\Language::translate($RECENT_ACTIVITY->getStatusLabel(), 'ModTracker')}
												<div class="float-right time text-muted">{\App\Fields\DateTime::formatToViewDate($RECENT_ACTIVITY->getParent()->get('createdtime'))}</div>
												<div>
													{foreach item=FIELDMODEL from=$RECENT_ACTIVITY->getFieldInstances()}
														{if $FIELDMODEL && $FIELDMODEL->getFieldInstance() && $FIELDMODEL->getFieldInstance()->isViewable() && $FIELDMODEL->getFieldInstance()->getDisplayType() neq '5'}
															<div class='font-x-small updateInfoContainer'>
																<span>{\App\Language::translate($FIELDMODEL->getName(),$MODULE_NAME)}</span>:&nbsp;
																{if $FIELDMODEL->get('postvalue') neq ''}
																	<strong class="moreContent">
																		<span class="teaserContent">{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getNewValue())}</span>
																		{if $FIELDMODEL->has('fullPostValue')}
																			<span class="fullContent hide">{$FIELDMODEL->get('fullPostValue')}</span>
																			<button type="button" class="btn btn-info btn-sm moreBtn" data-on="{\App\Language::translate('LBL_MORE_BTN')}" data-off="{\App\Language::translate('LBL_HIDE_BTN')}">{\App\Language::translate('LBL_MORE_BTN')}</button>
																		{/if}
																	</strong>
																{/if}
															</div>
														{/if}
													{/foreach}
												</div>
											</div>
										</div>
									</div>
								{else if $RECENT_ACTIVITY->isUpdate()}
									<div class="row">
										<span class="fa-layers fa-fw fa-2x ml-10px">
											<span class="fas fa-circle text-dark"></span>
											<span class="fas fa-edit text-light" data-fa-transform="shrink-8"></span>
										</span>
										<div class="col-11 ml-1 p-1 timeline-item{if $NEW_CHANGE} bgWarning{/if} isUpdate">
											<div class="float-left imageContainer">
												{assign var=IMAGE value=$RECENT_ACTIVITY->getModifiedBy()->getImagePath()}
												{if $IMAGE}
													<img class="userImage" src="data:image/jpg;base64,{base64_encode(file_get_contents($IMAGE))}" >
												{else}
													<span class="fas fa-user userImage"></span>
												{/if}
											</div>
											<div class="timeline-body small">
												<strong>{$RECENT_ACTIVITY->getModifiedBy()->getDisplayName()}&nbsp;</strong> {\App\Language::translate($RECENT_ACTIVITY->getStatusLabel(),'ModTracker')}
												<div class="float-right time text-muted">{\App\Fields\DateTime::formatToViewDate($RECENT_ACTIVITY->getActivityTime())}</div>
												<div>
													{foreach item=FIELDMODEL from=$RECENT_ACTIVITY->getFieldInstances()}
														{if $FIELDMODEL && $FIELDMODEL->getFieldInstance() && $FIELDMODEL->getFieldInstance()->isViewable() && $FIELDMODEL->getFieldInstance()->getDisplayType() neq '5'}
															<div class='font-x-small updateInfoContainer'>
																<span>{\App\Language::translate($FIELDMODEL->getName(),$MODULE_NAME)}</span>:&nbsp;
																{if $FIELDMODEL->get('prevalue') neq '' && $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && ($FIELDMODEL->get('postvalue') eq '0' || $FIELDMODEL->get('prevalue') eq '0'))}
																	&nbsp;{\App\Language::translate('LBL_FROM')}&nbsp;
																	<strong class="moreContent">
																		<span class="teaserContent">{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getOldValue())}</span>
																		{if $FIELDMODEL->has('fullPreValue')}
																			<span class="fullContent hide">{$FIELDMODEL->get('fullPreValue')}</span>
																			<button type="button" class="btn btn-info btn-sm moreBtn" data-on="{\App\Language::translate('LBL_MORE_BTN')}" data-off="{\App\Language::translate('LBL_HIDE_BTN')}">{\App\Language::translate('LBL_MORE_BTN')}</button>
																		{/if}
																	</strong>
																{else if $FIELDMODEL->get('postvalue') eq '' || ($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
																	&nbsp;
																	<strong>{\App\Language::translate('LBL_DELETED','ModTracker')}</strong>
																	( <del>{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getOldValue())}</del> )
																{else}
																	&nbsp;{\App\Language::translate('LBL_CHANGED')}
																{/if}
																{if $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
																	&nbsp;{\App\Language::translate('LBL_TO')}&nbsp;
																	<strong class="moreContent">
																		<span class="teaserContent">{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getNewValue())}</span>
																		{if $FIELDMODEL->has('fullPostValue')}
																			<span class="fullContent hide">{$FIELDMODEL->get('fullPostValue')}</span>
																			<button type="button" class="btn btn-info btn-sm moreBtn" data-on="{\App\Language::translate('LBL_MORE_BTN')}" data-off="{\App\Language::translate('LBL_HIDE_BTN')}">{\App\Language::translate('LBL_MORE_BTN')}</button>
																		{/if}
																	</strong>
																{/if}
															</div>
														{/if}
													{/foreach}
												</div>
											</div>
										</div>
									</div>
								{else if ($RECENT_ACTIVITY->isRelationLink() || $RECENT_ACTIVITY->isRelationUnLink())}
									<div class="row">
										<span class="fa-layers fa-fw fa-2x ml-10px">
											<span class="fas fa-circle text-warning"></span>
											<span class="fas fa-link text-light" data-fa-transform="shrink-8"></span>
										</span>
										<div class="col-11 ml-1 p-1 timeline-item{if $NEW_CHANGE} bgWarning{/if} isRelationLink isRelationUnLink">
											<div class="float-left imageContainer">
												{assign var=IMAGE value=$RECENT_ACTIVITY->getModifiedBy()->getImagePath()}
												{if $IMAGE}
													<img class="userImage" src="data:image/jpg;base64,{base64_encode(file_get_contents($IMAGE))}" >
												{else}
													<span class="fas fa-user userImage"></span>
												{/if}
											</div>
											<div class="timeline-body small">
												<div class="float-right time text-muted">{\App\Fields\DateTime::formatToViewDate($RECENT_ACTIVITY->getActivityTime())}</div>
												<span><strong>{$RECENT_ACTIVITY->getModifiedBy()->getName()}&nbsp;</strong></span>
												{assign var=RELATION value=$RECENT_ACTIVITY->getRelationInstance()}
												<span>{\App\Language::translate($RECENT_ACTIVITY->getStatusLabel(),'ModTracker')}&nbsp;</span>
												<span>
													{if \App\Privilege::isPermitted($RELATION->getLinkedRecord()->getModuleName(), 'DetailView', $RELATION->getLinkedRecord()->getId())}
														<strong class="moreContent">
															<span class="teaserContent">{Vtiger_Util_Helper::toVtiger6SafeHTML($RELATION->getValue())}</span>
															{if $RELATION->has('fullValue')}
																<span class="fullContent hide">{$RELATION->get('fullValue')}</span>
																<button type="button" class="btn btn-info btn-sm moreBtn" data-on="{\App\Language::translate('LBL_MORE_BTN')}" data-off="{\App\Language::translate('LBL_HIDE_BTN')}">{\App\Language::translate('LBL_MORE_BTN')}</button>
															{/if}
														</strong>
													{/if}
												</span>
												<span>&nbsp;({\App\Language::translate('SINGLE_'|cat:$RELATION->getLinkedRecord()->getModuleName(), $RELATION->getLinkedRecord()->getModuleName())})</span>
											</div>
										</div>
									</div>
								{else if $RECENT_ACTIVITY->isChangeState()}
									<div class="row">
										<span class="fa-layers fa-fw fa-2x ml-10px">
											{if $RECENT_ACTIVITY->get('status') == 1}
												<span class="fas fa-circle" {if $LIST_ENTITY_STATE_COLOR['Trash']}style="color: {$LIST_ENTITY_STATE_COLOR['Trash']}{/if};"></span>
												<span class="fas fa-trash-alt fa-fw text-light" data-fa-transform="shrink-8"></span>
											{else if $RECENT_ACTIVITY->get('status') == 3}
												<span class="fas fa-circle" {if $LIST_ENTITY_STATE_COLOR['Active']}style="color: {$LIST_ENTITY_STATE_COLOR['Active']}{/if};"></span>
												<span class="fas fa-refresh fa-fw text-light" data-fa-transform="shrink-8"></span>
											{else if $RECENT_ACTIVITY->get('status') == 8}
												<span class="fas fa-circle" {if $LIST_ENTITY_STATE_COLOR['Archived']}style="color: {$LIST_ENTITY_STATE_COLOR['Archived']}{/if};"></span>
												<span class="fas fa-archive fa-fw text-light" data-fa-transform="shrink-8"></span>
											{/if}
										</span>
										<div class="col-11 ml-1 p-1 timeline-item isDisplayed">
											<div class="imageContainer float-left">
												{assign var=IMAGE value=$RECENT_ACTIVITY->getModifiedBy()->getImagePath()}
												{if $IMAGE}
													<img class="userImage" src="data:image/jpg;base64,{base64_encode(file_get_contents($IMAGE))}" >
												{else}
													<span class="fas fa-user userImage"></span>
												{/if}
											</div>
											<div class="timeline-body small">
												<div class="float-right time text-muted">{\App\Fields\DateTime::formatToViewDate($RECENT_ACTIVITY->getActivityTime())}</div>
												<div><strong>{$RECENT_ACTIVITY->getModifiedBy()->getName()}</strong>&nbsp;{\App\Language::translate($RECENT_ACTIVITY->getStatusLabel(), 'ModTracker')}</div>
											</div>
										</div>
									</div>
								{else if $RECENT_ACTIVITY->isConvertToAccount()}
									<div class="row">
										<span class="fa-layers fa-fw fa-2x ml-10px">
											<span class="fas fa-circle text-info"></span>
											<span class="fas fa-exchange-alt text-light" data-fa-transform="shrink-8"></span>
										</span>
										<div class="col-11 ml-1 p-1 timeline-item{if $NEW_CHANGE} bgWarning{/if} isConvertToAccount">
											<div class="float-left imageContainer">
												{assign var=IMAGE value=$RECENT_ACTIVITY->getModifiedBy()->getImagePath()}
												{if $IMAGE}
													<img class="userImage" src="data:image/jpg;base64,{base64_encode(file_get_contents($IMAGE))}" >
												{else}
													<span class="fas fa-user userImage"></span>
												{/if}
											</div>
											<div class="timeline-body small">
												<div><strong>{$RECENT_ACTIVITY->getModifiedBy()->getName()}</strong>&nbsp;{\App\Language::translate($RECENT_ACTIVITY->getStatusLabel(), 'ModTracker')}</div>
												<span class="time float-right"><span>{\App\Fields\DateTime::formatToViewDate($RECENT_ACTIVITY->getActivityTime())}</span></span>
											</div>
										</div>
									</div>
								{else if $RECENT_ACTIVITY->isDisplayed()}
									<div class="row">
										<span class="fa-layers fa-fw fa-2x ml-10px">
											<span class="fas fa-circle text-info"></span>
											<span class="fas fa-th-list text-light" data-fa-transform="shrink-8"></span>
										</span>
										<div class="col-11 ml-1 p-1 timeline-item isDisplayed">
											<div class="float-left imageContainer">
												{assign var=IMAGE value=$RECENT_ACTIVITY->getModifiedBy()->getImagePath()}
												{if $IMAGE}
													<img class="userImage" src="data:image/jpg;base64,{base64_encode(file_get_contents($IMAGE))}" >
												{else}
													<span class="fas fa-user userImage"></span>
												{/if}
											</div>
											<div class="timeline-body small">
												<div class="float-left"><strong>{$RECENT_ACTIVITY->getModifiedBy()->getName()}</strong> &nbsp;{\App\Language::translate($RECENT_ACTIVITY->getStatusLabel(), 'ModTracker')}</div>
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
			<div class="summaryWidgetContainer"><p class="textAlignCenter">{\App\Language::translate('LBL_NO_RECENT_UPDATES')}</p></div>
			{/if}
		<input type="hidden" id="newChange" value="{$NEW_CHANGE}" />
		<div id="moreLink">
			{if !$IS_READ_ONLY && $PAGING_MODEL->isNextPageExists()}
				<div class="float-right"><button type="button" class="btn btn-primary btn-sm moreRecentUpdates">{\App\Language::translate('LBL_MORE',$MODULE_NAME)}..</button></div>
			{/if}
		</div>
	</div>
{/strip}
