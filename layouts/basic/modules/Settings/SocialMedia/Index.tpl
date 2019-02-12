{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-SocialMedia-Index js-social-media-container" data-js="container">
		<div class="widget_header row">
			<div class="col-md-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="mt-2">
			{if !\vtlib\Cron::getInstance('LBL_ARCHIVE_OLD_RECORDS')->isEnabled() }
				<div class="contents">
					<div class="alert alert-warning">
						{\App\Language::translate('LBL_SOCIAL_MEDIA_CRON_WARNING',$QUALIFIED_MODULE)}
					</div>
				</div>
			{/if}
			{if !\vtlib\Cron::getInstance('LBL_GET_SOCIAL_MEDIA_MESSAGES')->isEnabled() }
				<div class="contents">
					<div class="alert alert-warning">
						{\App\Language::translate('LBL_SOCIAL_MEDIA_CRON2_WARNING',$QUALIFIED_MODULE)}&nbsp;
						<b>"{\App\Language::translate('LBL_GET_SOCIAL_MEDIA_MESSAGES', 'Settings::CronTasks')}"</b>
					</div>
				</div>
			{/if}
			<div class="contents tabbable">
				<ul class="nav nav-tabs layoutTabs massEditTabs my-2 m-sm-2">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#logs">
							<span class="adminIcon-logs mr-1"></span>
							<strong>{\App\Language::translate('LBL_LOGS', $QUALIFIED_MODULE)}</strong>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#twitter">
							<span class="fab fa-twitter-square mr-1"></span>
							<strong>{\App\Language::translate('LBL_TWITTER', $QUALIFIED_MODULE)}</strong>
						</a>
					</li>
				</ul>
				<div class="tab-content layoutContent py-3">
					<div class="tab-pane active" id="logs">
						{include file=\App\Layout::getTemplatePath('Logs.tpl', 'Settings:SocialMedia')}
					</div>
					<div class="tab-pane" id="twitter">
						{include file=\App\Layout::getTemplatePath('Twitter.tpl', 'Settings:SocialMedia')}
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
