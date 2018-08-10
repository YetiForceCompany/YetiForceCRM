{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Detail-SocialMedia-Twitter-View">
		TWITTER:<br>
		{foreach from=$TWITTER_ACCOUNT item=TWITTER}
			{$TWITTER}
			<br>
		{/foreach}
		<div></div>

		<div class="table-responsive">
			<ul class="timeline">
				{foreach from=SocialMedia_Module_Model::getAllRecords($TWITTER_ACCOUNT) item=RECORD_TWITTER}
					<li>
						<div class="d-flex">
							<span class="flex-shrink-0 fa-layers fa-fw fa-2x u-ml-minus-5px mt-2"></span>
							<div class="flex-grow-1 ml-1 p-1 timeline-item isUpdate">
								<div class="float-sm-left imageContainer">{*img*}</div>
								<div class="timeline-body small">
									<strong>{$RECORD_TWITTER->get('twitter_login')}</strong>
									<div class="float-right time text-muted"><span
												title="{$RECORD_TWITTER->get('created_at')}">{$RECORD_TWITTER->get('created_at')}</span>
									</div>
									<div>
										{$RECORD_TWITTER->get('message')}
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
