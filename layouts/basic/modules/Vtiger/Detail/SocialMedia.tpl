{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-Detail-SocialMedia">
		<div class="mt-2">
			<div class="contents tabbable">
				<ul class="nav nav-tabs layoutTabs massEditTabs">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#twitter">
							<strong>
								{\App\Language::translate('LBL_TWITTER', 'Settings::SocialMedia')}
							</strong>
						</a>
					</li>
				</ul>
				<div class="tab-content layoutContent py-3">
					<div class="tab-pane active" id="twitter">
						{include file=\App\Layout::getTemplatePath('Detail\SocialMediaTwitter.tpl', $MODULE_NAME)}
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
