{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Detail-SocialMediaTwitter">
		<div class="table-responsive">
			<ul class="timeline">
				{foreach from=$SOCIAL_MODEL->getAllRecords() item=ITEM_TWITTER}
					<li>
						<div class="d-flex">
							<div class="flex-grow-1 ml-1 p-1 timeline-item isUpdate">
								<div class="float-sm-left imageContainer">
									<span class="fas fa-user userImage"></span>
								</div>
								<div class="timeline-body small">
									<strong>
										{$ITEM_TWITTER['twitter_login']}
									</strong>
									<div class="float-right time text-muted">
										<span title="{$ITEM_TWITTER['created']}">
											{$ITEM_TWITTER['created']}
										</span>
									</div>
									<div>
										{$ITEM_TWITTER['message']}
									</div>
								</div>
							</div>
						</div>
					</li>
				{/foreach}
			</ul>
		</div>

	</div>
{/strip}
