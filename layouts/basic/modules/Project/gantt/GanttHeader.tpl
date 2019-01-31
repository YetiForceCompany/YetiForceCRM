{strip}
	<div class="d-flex flex-wrap w-100 justify-content-between mb-1 js-gantt-header" data-js="container">
		<div class="d-inline-flex js-gantt-header__title">
			<h4 class="h-100 align-middle mb-0 mt-1 js-gantt-title" data-js="container">{$GANTT_TITLE}</h4>
		</div>
		<div class="d-inline-flex text-right js-gantt-header__options">
			<div class="d-flex flex-wrap justify-content-between js-gantt-header__options-container" data-js="container">
				<div class="d-inline-flex mr-1 c-gantt-header__option">
					<button class="btn btn-primary js-gantt-header__btn-filter" data-js="click">
						<span class="fas fa-filter"></span> {\App\Language::translate('LBL_GANTT_FILTER','Project')}
					</button>
				</div>
				<div class="d-inline-flex mr-1 c-gantt-header__option">
					<button class="btn btn-success js-gantt-header__btn-center" data-js="click">
						<span class="fas fa-compress-arrows-alt"></span> {\App\Language::translate('LBL_GANTT_NOW','Project')}
					</button>
				</div>
				<div class="text-center d-inline-flex mr-1 c-gantt-header__option">
					<label class="mb-0">
						{\App\Language::translate('LBL_GANTT_ZOOM_X','Project')}
						<input type="range" min="2" max="24" value="21" class="c-range-slider vertical-align-middle js-gantt-header__range-slider js-gantt-header__range-slider--x">
					</label>
				</div>
				<div class="text-center d-inline-flex mr-1 c-gantt-header__option">
					<label class="mb-0">
						{\App\Language::translate('LBL_GANTT_ZOOM_Y','Project')}
						<input type="range" min="7" max="100" value="24" class="c-range-slider vertical-align-middle js-gantt-header__range-slider js-gantt-header__range-slider--y">
					</label>
				</div>
				<div class="text-center d-inline-flex mr-1 c-gantt-header__option">
					<label class="mb-0">
						{\App\Language::translate('LBL_GANTT_EXPAND','Project')}
						<input type="range" min="0" max="31" value="4" class="c-range-slider vertical-align-middle js-gantt-header__range-slider js-gantt-header__range-slider--scope">
					</label>
				</div>
				<div class="text-center d-inline-flex mr-1 c-gantt-header__option">
					<label class="mb-0">
						{\App\Language::translate('LBL_GANTT_TASKLIST','Project')}
						<input type="range" min="0" max="100" value="100" class="c-range-slider vertical-align-middle js-gantt-header__range-slider js-gantt-header__range-slider--task-list-width">
					</label>
				</div>
				<div class="text-center d-inline-flex mr-1 c-gantt-header__option">
					<label class="mb-0">
						{\App\Language::translate('LBL_GANTT_TASKLIST_VISIBLE','Project')}
						<input type="checkbox" checked="checked" class="form-control vertical-align-middle js-gantt-header__range-slider js-gantt-header__range-slider--task-list-visible">
					</label>
				</div>
			</div>
		</div>
	</div>
{/strip}
