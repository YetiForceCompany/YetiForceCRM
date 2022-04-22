/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
$.Class(
	'Vtiger_Tiles_Js',
	{},
	{
		contentContainer: false,
		registerTileSizeChange: function (topMenuContainer, listInstance) {
			let thisInstance = this;
			topMenuContainer.find('.js-tiles-size').on('change', (e) => {
				app.setMainParams('pageNumber', '1');
				app.setMainParams('pageToJump', '1');
				let urlParams = {
					viewname: listInstance.getCurrentCvId(),
					search_key: listInstance.getAlphabetSearchField(),
					search_value: '',
					search_params: '',
					advancedConditions: '',
					tile_size: $(e.currentTarget).find('option:selected').val()
				};
				$('#recordsCount').val('');
				$('#totalPageCount').text('');
				topMenuContainer.find('.pagination').data('totalCount', 0);
				listInstance.getListViewRecords(urlParams).done(() => {
					listInstance.updatePagination(1);
					thisInstance.setHeightOfTiles(thisInstance.contentContainer);
				});
				e.stopPropagation();
			});
		},
		setHeightOfTiles: function () {
			let maxHeight = -1;
			let tiles = this.contentContainer.find('.js-tile-card');
			tiles.each(function () {
				maxHeight = maxHeight > $(this).height() ? maxHeight : $(this).height();
			});

			tiles.each(function () {
				$(this).height(maxHeight);
			});
		},
		registerEvents: function () {
			const listInstance = new Vtiger_List_Js();
			listInstance.registerEvents();
			const topMenuContainer = listInstance.getListViewTopMenuContainer();
			this.contentContainer = listInstance.getListViewContainer();
			this.registerTileSizeChange(topMenuContainer, listInstance);
			this.setHeightOfTiles();
		}
	}
);
