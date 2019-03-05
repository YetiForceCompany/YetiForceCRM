{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-Detail-SocialMediaTwitter">
		<div class="table-responsive">
			<ul class="tweets">
				{foreach from=$SOCIAL_MODEL->getAllRecords() item=ITEM}
					<li>
						<div class="d-flex">
							<div class="flex-grow-1 ml-1 p-1 timeline-item isUpdate">
								<div class="float-sm-left imageContainer">
									<span class="u-ml-minus-5px mt-2">
										<span class="fas fa-user fa-fw fa-2x userImage"></span>
									</span>
								</div>
								<div class="timeline-body small">
									<strong>
										{\App\Purifier::encodeHtml($ITEM['twitter_name'])}
										( {\App\Purifier::encodeHtml($ITEM['twitter_login'])} )
									</strong>
									<div class="float-right time text-muted">
										<span title="{\App\Purifier::encodeHtml($ITEM['created'])}">
											{\App\Purifier::encodeHtml($ITEM['created'])}
										</span>
									</div>
									<div>
										{\App\Purifier::encodeHtml($ITEM['message'])}
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
