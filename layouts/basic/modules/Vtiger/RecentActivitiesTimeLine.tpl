{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="recentActivitiesContainer row no-margin">
		<input type="hidden" id="updatesCurrentPage" value="{$PAGING_MODEL->get('page')}" />
		<input type="hidden" id="updatesPageLimit" value="{$PAGING_MODEL->getPageLimit()}" />
		{if !empty($RECENT_ACTIVITIES)}
			{assign var=LIST_ENTITY_STATE_COLOR value=AppConfig::search('LIST_ENTITY_STATE_COLOR')}
			<div id="updates">
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
									<div class="lineOfText marginLeft15">
										<div>{\App\Language::translate('LBL_REVIEWED', $MODULE_BASE_NAME)}</div>
									</div>
								{/if}
								{$COUNT=$COUNT+1}
								{if $RECENT_ACTIVITY->isCreate()}
									<span class="fas fa-plus bgGreen"></span>
									<div class="timeline-item{if $NEW_CHANGE} bgWarning{/if} isCreate">
										<div class="float-left paddingRight15 imageContainer">
											{assign var=IMAGE value=$RECENT_ACTIVITY->getModifiedBy()->getImagePath()}
											{if $IMAGE}
												<img class="userImage" src="data:image/jpg;base64,{base64_encode(file_get_contents($IMAGE))}" >
											{else}	
												<span class="fas fa-user userImage" aria-hidden="true"></span>
											{/if}
										</div>
										<div class="timeline-body row no-margin">
											<span class="time float-right">
												<span>{\App\Fields\DateTime::formatToViewDate($RECENT_ACTIVITY->getParent()->get('createdtime'))}</span>
											</span>
											<strong>{$RECENT_ACTIVITY->getModifiedBy()->getName()}</strong> 
											&nbsp;{\App\Language::translate($RECENT_ACTIVITY->getStatusLabel(), 'ModTracker')}
											{foreach item=FIELDMODEL from=$RECENT_ACTIVITY->getFieldInstances()}
												{if $FIELDMODEL && $FIELDMODEL->getFieldInstance() && $FIELDMODEL->getFieldInstance()->isViewable() && $FIELDMODEL->getFieldInstance()->getDisplayType() neq '5'}
													<div class='font-x-small updateInfoContainer'>
														<span>{\App\Language::translate($FIELDMODEL->getName(),$MODULE_NAME)}</span>:&nbsp;
														{if $FIELDMODEL->get('postvalue') neq ''}
															<strong class="moreContent">
																<span class="teaserContent">
																	{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getNewValue())}
																</span>
																{if $FIELDMODEL->has('fullPostValue')}
																	<span class="fullContent hide">
																		{$FIELDMODEL->get('fullPostValue')}
																	</span>
																	<button type="button" class="btn btn-info btn-xs moreBtn" data-on="{\App\Language::translate('LBL_MORE_BTN')}" data-off="{\App\Language::translate('LBL_HIDE_BTN')}">{\App\Language::translate('LBL_MORE_BTN')}</button>
																{/if}
															</strong>
														{/if}
													</div>
												{/if}
											{/foreach}
										</div>
									</div>
								{else if $RECENT_ACTIVITY->isUpdate()}
									<span class="fas fa-pencil-alt bgDarkBlue"></span>
									<div class="timeline-item{if $NEW_CHANGE} bgWarning{/if} isUpdate">
										<div class="float-left paddingRight15 imageContainer">
											{assign var=IMAGE value=$RECENT_ACTIVITY->getModifiedBy()->getImagePath()}
											{if $IMAGE}
												<img class="userImage" src="data:image/jpg;base64,{base64_encode(file_get_contents($IMAGE))}" >
											{else}	
												<span class="fas fa-user userImage" aria-hidden="true"></span>
											{/if}
										</div>
										<div class="timeline-body row no-margin">
											<span class="time float-right">
												<span>{\App\Fields\DateTime::formatToViewDate($RECENT_ACTIVITY->getActivityTime())}</span>
											</span>
											<span><strong>{$RECENT_ACTIVITY->getModifiedBy()->getDisplayName()}&nbsp;</strong> {\App\Language::translate($RECENT_ACTIVITY->getStatusLabel(),'ModTracker')}</span>
											{foreach item=FIELDMODEL from=$RECENT_ACTIVITY->getFieldInstances()}
												{if $FIELDMODEL && $FIELDMODEL->getFieldInstance() && $FIELDMODEL->getFieldInstance()->isViewable() && $FIELDMODEL->getFieldInstance()->getDisplayType() neq '5'}
													<div class='font-x-small updateInfoContainer'>
														<span>{\App\Language::translate($FIELDMODEL->getName(),$MODULE_NAME)}</span>:&nbsp;
														{if $FIELDMODEL->get('prevalue') neq '' && $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && ($FIELDMODEL->get('postvalue') eq '0' || $FIELDMODEL->get('prevalue') eq '0'))}
															&nbsp;{\App\Language::translate('LBL_FROM')}&nbsp;
															<strong class="moreContent">
																<span class="teaserContent">
																	{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getOldValue())}
																</span>
																{if $FIELDMODEL->has('fullPreValue')}
																	<span class="fullContent hide">
																		{$FIELDMODEL->get('fullPreValue')}
																	</span>
																	<button type="button" class="btn btn-info btn-xs moreBtn" data-on="{\App\Language::translate('LBL_MORE_BTN')}" data-off="{\App\Language::translate('LBL_HIDE_BTN')}">{\App\Language::translate('LBL_MORE_BTN')}</button>
																{/if}
															</strong>
														{else if $FIELDMODEL->get('postvalue') eq '' || ($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
															&nbsp; 
															<strong>
																{\App\Language::translate('LBL_DELETED','ModTracker')}
															</strong>
															( <del>{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getOldValue())}</del> )
														{else}
															&nbsp;{\App\Language::translate('LBL_CHANGED')}
														{/if}
														{if $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
															&nbsp;{\App\Language::translate('LBL_TO')}&nbsp;
															<strong class="moreContent">
																<span class="teaserContent">
																	{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getNewValue())}
																</span>
																{if $FIELDMODEL->has('fullPostValue')}
																	<span class="fullContent hide">
																		{$FIELDMODEL->get('fullPostValue')}
																	</span>
																	<button type="button" class="btn btn-info btn-xs moreBtn" data-on="{\App\Language::translate('LBL_MORE_BTN')}" data-off="{\App\Language::translate('LBL_HIDE_BTN')}">{\App\Language::translate('LBL_MORE_BTN')}</button>
																{/if}
															</strong>
														{/if}
													</div>
												{/if}
											{/foreach}
										</div>
									</div>
								{else if ($RECENT_ACTIVITY->isRelationLink() || $RECENT_ACTIVITY->isRelationUnLink())}
									<span class="glyphicon glyphicon-link bgOrange"></span>
									<div class="timeline-item{if $NEW_CHANGE} bgWarning{/if} isRelationLink isRelationUnLink">
										<div class="float-left paddingRight15 imageContainer">
											{assign var=IMAGE value=$RECENT_ACTIVITY->getModifiedBy()->getImagePath()}
											{if $IMAGE}
												<img class="userImage" src="data:image/jpg;base64,{base64_encode(file_get_contents($IMAGE))}" >
											{else}	
												<span class="fas fa-user userImage" aria-hidden="true"></span>
											{/if}
										</div>
										<div class="timeline-body row no-margin">
											<div class="float-right">
												<span class="time float-right">
													<span>{\App\Fields\DateTime::formatToViewDate($RECENT_ACTIVITY->getActivityTime())}</span>
												</span>
											</div>
											<span>
												<strong>{$RECENT_ACTIVITY->getModifiedBy()->getName()}&nbsp;</strong>
											</span>
											{assign var=RELATION value=$RECENT_ACTIVITY->getRelationInstance()}
											<span>
												{\App\Language::translate($RECENT_ACTIVITY->getStatusLabel(),'ModTracker')}&nbsp;
											</span>
											<span>
												{if \App\Privilege::isPermitted($RELATION->getLinkedRecord()->getModuleName(), 'DetailView', $RELATION->getLinkedRecord()->getId())}
													<strong class="moreContent">
														<span class="teaserContent">
															{Vtiger_Util_Helper::toVtiger6SafeHTML($RELATION->getValue())}
														</span>
														{if $RELATION->has('fullValue')}
															<span class="fullContent hide">
																{$RELATION->get('fullValue')}
															</span>
															<button type="button" class="btn btn-info btn-xs moreBtn" data-on="{\App\Language::translate('LBL_MORE_BTN')}" data-off="{\App\Language::translate('LBL_HIDE_BTN')}">{\App\Language::translate('LBL_MORE_BTN')}</button>
														{/if}
													</strong>
												{/if}
											</span>
											<span>&nbsp;({\App\Language::translate('SINGLE_'|cat:$RELATION->getLinkedRecord()->getModuleName(), $RELATION->getLinkedRecord()->getModuleName())})</span>
										</div>
									</div>
								{else if $RECENT_ACTIVITY->isChangeState()}
									{if $RECENT_ACTIVITY->get('status') == 1}
										<span class="fas fa-trash-alt entityStateIcon" {if $LIST_ENTITY_STATE_COLOR['Trash']}style="background: {$LIST_ENTITY_STATE_COLOR['Trash']};"{/if}></span>
									{else if $RECENT_ACTIVITY->get('status') == 3}
										<span class="fa glyphicon fa-refresh entityStateIcon" {if $LIST_ENTITY_STATE_COLOR['Active']}style="background: {$LIST_ENTITY_STATE_COLOR['Active']};"{/if}></span>
									{else if $RECENT_ACTIVITY->get('status') == 8}
										<span class="fa glyphicon fa-archive entityStateIcon" {if $LIST_ENTITY_STATE_COLOR['Archived']}style="background: {$LIST_ENTITY_STATE_COLOR['Archived']};"{/if}></span>
									{/if}
									<div class="timeline-item isDisplayed">
										<div class="float-left paddingRight15 imageContainer">
											{assign var=IMAGE value=$RECENT_ACTIVITY->getModifiedBy()->getImagePath()}
											{if $IMAGE}
												<img class="userImage" src="data:image/jpg;base64,{base64_encode(file_get_contents($IMAGE))}" >
											{else}	
												<span class="fas fa-user userImage" aria-hidden="true"></span>
											{/if}
										</div>
										<div class="timeline-body row no-margin">
											<span class="time float-right">
												<span>{\App\Fields\DateTime::formatToViewDate($RECENT_ACTIVITY->getActivityTime())}</span>
											</span>
											<div class="float-left">
												<strong>{$RECENT_ACTIVITY->getModifiedBy()->getName()}</strong>
												&nbsp;{\App\Language::translate($RECENT_ACTIVITY->getStatusLabel(), 'ModTracker')}
											</div>
										</div>
									</div>
								{else if $RECENT_ACTIVITY->isConvertToAccount()}
									<span class="fas fa-exchange-alt bgAzure"></span>
									<div class="timeline-item{if $NEW_CHANGE} bgWarning{/if} isConvertToAccount">
										<div class="float-left paddingRight15 imageContainer">
											{assign var=IMAGE value=$RECENT_ACTIVITY->getModifiedBy()->getImagePath()}
											{if $IMAGE}
												<img class="userImage" src="data:image/jpg;base64,{base64_encode(file_get_contents($IMAGE))}" >
											{else}	
												<span class="fas fa-user userImage" aria-hidden="true"></span>
											{/if}
										</div>
										<div class="timeline-body row no-margin">
											<span class="time float-right">
												<span>{\App\Fields\DateTime::formatToViewDate($RECENT_ACTIVITY->getActivityTime())}</span>
											</span>
											<div class="float-left">
												<strong>{\App\Language::translate($RECENT_ACTIVITY->getStatusLabel(), 'ModTracker')}</strong> 
											</div>
										</div>
									</div>
								{else if $RECENT_ACTIVITY->isDisplayed()}
									<span class="fas fa-th-list bgAzure"></span>
									<div class="timeline-item isDisplayed">
										<div class="float-left paddingRight15 imageContainer">
											{assign var=IMAGE value=$RECENT_ACTIVITY->getModifiedBy()->getImagePath()}
											{if $IMAGE}
												<img class="userImage" src="data:image/jpg;base64,{base64_encode(file_get_contents($IMAGE))}" >
											{else}	
												<span class="fas fa-user userImage" aria-hidden="true"></span>
											{/if}
										</div>
										<div class="timeline-body row no-margin">
											<span class="time float-right">
												<span>{\App\Fields\DateTime::formatToViewDate($RECENT_ACTIVITY->getActivityTime())}</span>
											</span>
											<div class="float-left">
												<strong>{$RECENT_ACTIVITY->getModifiedBy()->getName()}</strong>
												&nbsp;{\App\Language::translate($RECENT_ACTIVITY->getStatusLabel(), 'ModTracker')}
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
		<div id="moreLink">
			{if !$IS_READ_ONLY && $PAGING_MODEL->isNextPageExists()}
				<div class="float-right">
					<button type="button" class="btn btn-primary btn-xs moreRecentUpdates">{\App\Language::translate('LBL_MORE',$MODULE_NAME)}..</button>
				</div>
			{/if}
		</div>
	</div>
{/strip}
