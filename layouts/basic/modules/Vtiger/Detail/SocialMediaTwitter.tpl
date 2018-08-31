{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{*
	<span class="flex-shrink-0 fa-layers fa-fw fa-2x u-ml-minus-5px mt-2">
            <span class="fas fa-circle text-success" style="color: {ModTracker::$colorsActions[$RECENT_ACTIVITY->get('status')]} !important;"></span>
            <span class="{ModTracker::$iconActions[$RECENT_ACTIVITY->get('status')]} text-light" data-fa-transform="shrink-8"></span>
           </span>
	*}
	<style>
		ul.tweets li {
			list-style-type: none;
			width: 528px;
			min-height: 72px;
			border-top: #e6ecf0 solid 1px;
		}
	</style>
	<div class="tpl-Detail-SocialMediaTwitter">
		<div class="table-responsive">
			{*<ul class="timeline">*}
			<ul class="tweets">
				{foreach from=$SOCIAL_MODEL->getAllRecords() item=ITEM}
					<li style="">
						<div class="d-flex">
							<div class="flex-grow-1 ml-1 p-1 timeline-item isUpdate">
								<div class="float-sm-left imageContainer">
									<span class="flex-shrink-0 fa-layers fa-fw fa-2x u-ml-minus-5px mt-2">
										<span class="fas fa-user userImage"></span>
									</span>
								</div>
								<div class="timeline-body small">
									<strong>
										NAME SURNAME ( {$ITEM['twitter_login']} )
									</strong>
									<div class="float-right time text-muted">
										<span title="{$ITEM['created']}">
											{$ITEM['created']}
										</span>
									</div>
									<div>
										{$ITEM['message']}
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
