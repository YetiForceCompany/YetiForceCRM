{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-dashboards-TimeCounterContents -->
	<div class="o-time-counter text-center">
		<div class="o-time-counter__navigator position-relative">
			<span class="fa-regular fa-circle o-time-counter__navigator-icon"></span>
			<div class="o-time-counter__navigator-button js-navigator row">
				<a class="u-cursor-pointer text-success js-start-watch" data-js="click"> <span class="fa-solid fa-circle-play"></span> </a>
				<a class="u-cursor-pointer text-danger d-none js-stop-watch mr-1" data-js="click"> <span class="fa-solid fa-circle-stop"></span> </a>
				<a class="u-cursor-pointer text-warning d-none js-reset-watch" data-js="click"> <span class="fa-solid fa-circle-xmark"></span> </a>
			</div>
		</div>
		<div class="o-time-counter__stopwatch text-center mt-3 js-stopwatch" data-js="container">
			00:00:00
		</div>
	</div>
	<!-- /tpl-Base-dashboards-TimeCounterContents -->
{/strip}
