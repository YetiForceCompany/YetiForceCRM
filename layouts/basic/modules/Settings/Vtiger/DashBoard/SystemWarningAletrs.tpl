{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-Base-DashBoard-SystemWarningAletrs" id="systemWarningAletrs">
		<div class="modal fade static">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">
							<span class="yfi yfi-system-warnings mr-1"></span>
							{App\Language::translate('LBL_SYSTEM_WARNINGS','Settings:Vtiger')}
						</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="warnings">
							{foreach from=$WARNINGS item=ITEM}
								<div class="warning d-none clearfix" data-id="{get_class($ITEM)}">
									{if $ITEM->getTpl()}
										{include file=$ITEM->getTpl()}
									{else}
										<h3 class="marginTB3">
											{App\Language::translate($ITEM->getTitle(),'Settings:SystemWarnings')}
										</h3>
										<p>
											{$ITEM->getDescription()}
										</p>
										<div class="float-right">
											{if $ITEM->getLink()}
												<a class="btn btn-success ml-1" href="{$ITEM->getLink()}" target="_blank"
													rel="noreferrer noopener">
													<span class="fas fa-link mr-1"></span>
													{$ITEM->linkTitle}
												</a>
											{/if}
											<button class="btn btn-danger cancel ml-1" type="button">
												<span class="fas fa-ban mr-1"></span>
												{App\Language::translate('LBL_REMIND_LATER','Settings:SystemWarnings')}
											</button>
										</div>
									{/if}
								</div>
							{/foreach}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
