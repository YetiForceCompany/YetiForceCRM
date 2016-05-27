{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="recentActivitiesContainer row no-margin">
		<input type="hidden" id="updatesCurrentPage" value="{$PAGING_MODEL->get('page')}" />
		<input type="hidden" id="updatesPageLimit" value="{$PAGING_MODEL->getPageLimit()}" />
		{if !empty($RECENT_ACTIVITIES)}
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
							{if $RECENT_ACTIVITY->isReviewed() && !($COUNT eq 0 && $PAGING_MODEL->get('page') eq 1)}
								{$NEW_CHANGE = false}
								<li>
									<div class="lineOfText marginLeft15">
										<div>{vtranslate('LBL_REVIEWED', $MODULE_BASE_NAME)}</div>
									</div>
								</li>
							{/if}
							{$COUNT=$COUNT+1}
							{if $RECENT_ACTIVITY->isCreate()}
								<li>
									<span class="glyphicon glyphicon-plus bgGreen"></span>
									<div class="timeline-item{if $NEW_CHANGE} bgWarning{/if}">
										<div class="pull-left paddingRight15 imageContainer">
											<img class="userImage img-circle" src="{$RECENT_ACTIVITY->getModifiedBy()->getImagePath()}">
										</div>
										<div class="timeline-body row no-margin">
											<span class="time pull-right">
												<span title="{$RECENT_ACTIVITY->getDisplayActivityTime()}">{Vtiger_Util_Helper::formatDateDiffInStrings($RECENT_ACTIVITY->getParent()->get('createdtime'))}</span>
											</span>
											<strong>{$RECENT_ACTIVITY->getModifiedBy()->getName()}</strong> 
											&nbsp;{vtranslate('LBL_CREATED', $MODULE_NAME)}
											{foreach item=FIELDMODEL from=$RECENT_ACTIVITY->getFieldInstances()}
												{if $FIELDMODEL && $FIELDMODEL->getFieldInstance() && $FIELDMODEL->getFieldInstance()->isViewable() && $FIELDMODEL->getFieldInstance()->getDisplayType() neq '5'}
													<div class='font-x-small updateInfoContainer'>
														<span>{vtranslate($FIELDMODEL->getName(),$MODULE_NAME)}</span>:&nbsp;
														{if $FIELDMODEL->get('prevalue') neq '' && $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && ($FIELDMODEL->get('postvalue') eq '0' || $FIELDMODEL->get('prevalue') eq '0'))}
															&nbsp;{vtranslate('LBL_FROM')} <strong style="white-space:pre-wrap;">
																{vtranslate(Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getDisplayValue(decode_html($FIELDMODEL->get('prevalue')))),$MODULE_NAME)}</strong>
															{else if $FIELDMODEL->get('postvalue') eq '' || ($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
															&nbsp; <strong> {vtranslate('LBL_DELETED')} </strong> ( <del>{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getDisplayValue(decode_html($FIELDMODEL->get('prevalue'))))}</del> )
														{else}
															&nbsp;{vtranslate('LBL_CHANGED')}
														{/if}
														{if $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
															&nbsp;{vtranslate('LBL_TO')}&nbsp;<strong style="white-space:pre-wrap;">
																{vtranslate(Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getDisplayValue(decode_html($FIELDMODEL->get('postvalue')))),$MODULE_NAME)}</strong>
															{/if}
													</div>
												{/if}
											{/foreach}
										</div>
									</div>
								</li>
							{else if $RECENT_ACTIVITY->isUpdate()}
								<li>
									<span class="glyphicon glyphicon-pencil bgDarkBlue"></span>
									<div class="timeline-item{if $NEW_CHANGE} bgWarning{/if}">
										<div class="pull-left paddingRight15 imageContainer">
											<img class="userImage img-circle" src="{$RECENT_ACTIVITY->getModifiedBy()->getImagePath()}">
										</div>
										<div class="timeline-body row no-margin">
											<span class="time pull-right">
												<span title="{$RECENT_ACTIVITY->getDisplayActivityTime()}">{Vtiger_Util_Helper::formatDateDiffInStrings($RECENT_ACTIVITY->getActivityTime())}</span>
											</span>
											<span><strong>{$RECENT_ACTIVITY->getModifiedBy()->getDisplayName()}</strong> {vtranslate('LBL_UPDATED', $MODULE_NAME)}</span>
											{foreach item=FIELDMODEL from=$RECENT_ACTIVITY->getFieldInstances()}
												{if $FIELDMODEL && $FIELDMODEL->getFieldInstance() && $FIELDMODEL->getFieldInstance()->isViewable() && $FIELDMODEL->getFieldInstance()->getDisplayType() neq '5'}
													<div class='font-x-small updateInfoContainer'>
														<span>{vtranslate($FIELDMODEL->getName(),$MODULE_NAME)}</span>:&nbsp;
														{if $FIELDMODEL->get('prevalue') neq '' && $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && ($FIELDMODEL->get('postvalue') eq '0' || $FIELDMODEL->get('prevalue') eq '0'))}
															&nbsp;{vtranslate('LBL_FROM')}&nbsp;<strong style="white-space:pre-wrap;">
																{vtranslate(Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getDisplayValue(decode_html($FIELDMODEL->get('prevalue')))),$MODULE_NAME)}</strong>
															{else if $FIELDMODEL->get('postvalue') eq '' || ($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
															&nbsp; <strong> {vtranslate('LBL_DELETED')} </strong> ( <del>{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getDisplayValue(decode_html($FIELDMODEL->get('prevalue'))))}</del> )
														{else}
															&nbsp;{vtranslate('LBL_CHANGED')}
														{/if}
														{if $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
															&nbsp;{vtranslate('LBL_TO')}&nbsp;<strong style="white-space:pre-wrap;">
																{vtranslate(Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getDisplayValue(decode_html($FIELDMODEL->get('postvalue')))),$MODULE_NAME)}</strong>
															{/if}
													</div>
												{/if}
											{/foreach}
										</div>
									</div>
								</li>
							{else if ($RECENT_ACTIVITY->isRelationLink() || $RECENT_ACTIVITY->isRelationUnLink())}
								<li>
									<span class="glyphicon glyphicon-link bgOrange"></span>
									<div class="timeline-item{if $NEW_CHANGE} bgWarning{/if}">
										<div class="pull-left paddingRight15 imageContainer">
											<img class="userImage img-circle" src="{$RECENT_ACTIVITY->getModifiedBy()->getImagePath()}">
										</div>
										<div class="timeline-body row no-margin">
											<div class="pull-right">
												<span class="time pull-right">
													<span title="{$RECENT_ACTIVITY->getDisplayActivityTime()}">{Vtiger_Util_Helper::formatDateDiffInStrings($RECENT_ACTIVITY->getActivityTime())}</span>
												</span>
											</div>
											<span>
												<strong>{$RECENT_ACTIVITY->getModifiedBy()->getName()}&nbsp;</strong>
											</span>
											{assign var=RELATION value=$RECENT_ACTIVITY->getRelationInstance()}
											<span>
												{if $RECENT_ACTIVITY->isRelationLink()}
													{vtranslate('LBL_ADDED', $MODULE_NAME)}
												{else}
													{vtranslate('LBL_REMOVED', $MODULE_NAME)}
												{/if} 
											</span>
											<span>
												{if $RELATION->getLinkedRecord()->getModuleName() eq 'Calendar'}
													{if isPermitted('Calendar', 'DetailView', $RELATION->getLinkedRecord()->getId()) eq 'yes'} <strong>{$RELATION->getLinkedRecord()->getName()}</strong> {else} {/if}
												{else} <strong>{$RELATION->getLinkedRecord()->getName()}</strong> {/if}
											</span>
											<span>({vtranslate($RELATION->getLinkedRecord()->getModuleName(), $RELATION->getLinkedRecord()->getModuleName())})</span>
										</div>
									</div>
								</li>
							{else if $RECENT_ACTIVITY->isRestore()}
								<li>

								</li>
							{else if $RECENT_ACTIVITY->isConvertToAccount()}
								<li>
									<span class="glyphicon glyphicon-transfer bgAzure"></span>
									<div class="timeline-item{if $NEW_CHANGE} bgWarning{/if}">
										<div class="pull-left paddingRight15 imageContainer">
											<img class="userImage img-circle" src="{$RECENT_ACTIVITY->getModifiedBy()->getImagePath()}">
										</div>
										<div class="timeline-body row no-margin">
											<span class="time pull-right">
												<span title="{$RECENT_ACTIVITY->getDisplayActivityTime()}">{Vtiger_Util_Helper::formatDateDiffInStrings($RECENT_ACTIVITY->getActivityTime())}</span>
											</span>
											<div class="pull-left">
												<strong>{vtranslate('LBL_CONVERTED_FROM_LEAD', $MODULE_NAME)}</strong> 
											</div>
										</div>
									</div>
								</li>
							{else if $RECENT_ACTIVITY->isDisplayed()}
								<li>
									<span class="glyphicon glyphicon-th-list bgAzure"></span>
									<div class="timeline-item">
										<div class="pull-left paddingRight15 imageContainer">
											<img class="userImage img-circle" src="{$RECENT_ACTIVITY->getModifiedBy()->getImagePath()}">
										</div>
										<div class="timeline-body row no-margin">
											<span class="time pull-right">
												<span title="{$RECENT_ACTIVITY->getDisplayActivityTime()}">{Vtiger_Util_Helper::formatDateDiffInStrings($RECENT_ACTIVITY->getActivityTime())}</span>
											</span>
											<div class="pull-left">
												<strong>{$RECENT_ACTIVITY->getModifiedBy()->getName()}</strong>
												&nbsp;{vtranslate('LBL_DISPLAYED', $MODULE_NAME)}
											</div>
										</div>
									</div>
								</li>
							{/if}
						{/if}
					{/foreach}
				</ul>
			</div>
		{else}
			<div class="summaryWidgetContainer">
				<p class="textAlignCenter">{vtranslate('LBL_NO_RECENT_UPDATES')}</p>
			</div>
		{/if}
		<input type="hidden" id="newChange" value="{$NEW_CHANGE}" />
		<div id="moreLink">
			{if $PAGING_MODEL->isNextPageExists()}
				<div class="pull-right">
					<button type="button" class="btn btn-primary btn-xs moreRecentUpdates">{vtranslate('LBL_MORE',$MODULE_NAME)}..</button>
				</div>
			{/if}
		</div>
	</div>
{/strip}
