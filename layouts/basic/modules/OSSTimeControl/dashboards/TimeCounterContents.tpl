{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-OSSTimeControl-dashboards-TimeCounterContents -->
	<div class="u-min-w-400px">
		<div class="o-time-counter text-center">
			<div class="o-time-counter__navigator position-relative">
				<span class="fa-regular fa-circle o-time-counter__navigator-icon"></span>
				<div class="o-time-counter__navigator-buttons js-navigator-buttons row" data-js="click">
					<a class="u-cursor-pointer text-success js-time-counter-start" title="{\App\Language::translate('LBL_START_TIME_COUNTER', $MODULE_NAME)}" data-js="click"> <span class="fa-solid fa-circle-play"></span> </a>
					<a class="u-cursor-pointer text-danger d-none js-time-counter-stop mr-1" title="{\App\Language::translate('LBL_STOP_TIME_COUNTER', $MODULE_NAME)}" data-js="click"> <span class="fa-solid fa-circle-stop"></span> </a>
					<a class="u-cursor-pointer text-warning d-none js-time-counter-reset" title="{\App\Language::translate('LBL_RESET_TIME_COUNTER', $MODULE_NAME)}" data-js="click"> <span class="fa-solid fa-circle-xmark"></span> </a>
				</div>
			</div>
			<div class="o-time-counter__timer text-center mt-3 js-time-counter" data-js="container">
				00:00:00
			</div>
		</div>
		{assign var=WIDGET_DATA value=\App\Json::decode(html_entity_decode($WIDGET->get('data')))}
		<div class="mt-4 d-flex justify-content-center">
			{if !empty($WIDGET_DATA['default_time'])}
				{foreach from=$WIDGET_DATA['default_time'] item=item}
					<button type="button" class="btn btn-light u-cursor-pointer js-time-counter-minute mr-1" data-value="{$item}" title="{\App\Language::translate('LBL_'|cat:$item|cat:'_MINUTES')}" data-js="click">
						{$item}
					</button>
				{/foreach}
			{/if}
		</div>
	</div>
	<!-- /tpl-OSSTimeControl-dashboards-TimeCounterContents -->
{/strip}
