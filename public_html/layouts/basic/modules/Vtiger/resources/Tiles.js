/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
$.Class(
	'Vtiger_Tiles_Js',
	{},
	{
		contentContainer: false,
		/**
		 * Register tiles size change action
		 * @param {jQuery} topMenuContainer
		 */
		registerTileSizeChange: function (topMenuContainer) {
			topMenuContainer.find('.js-tile-size').on('click', (e) => {
				let selectedTileSize = $(e.currentTarget).attr('data-tile-size');
				topMenuContainer.find('.js-tile-dropdown-title').text($(e.currentTarget).text());
				topMenuContainer.find('.js-selected-tile-size').attr('data-selected-tile-size', selectedTileSize);
				app.setMainParams('pageNumber', '1');
				app.setMainParams('pageToJump', '1');
				let urlParams = {
					viewname: this.listInstance.getCurrentCvId(),
					search_key: this.listInstance.getAlphabetSearchField(),
					search_value: '',
					search_params: '',
					advancedConditions: '',
					tile_size: selectedTileSize
				};
				this.contentContainer.find('#recordsCount').val('');
				this.contentContainer.find('#totalPageCount').text('');
				topMenuContainer.find('.pagination').data('totalCount', 0);
				this.listInstance.getListViewRecords(urlParams).done(() => {
					this.listInstance.updatePagination(1);
					this.setHeightOfTiles(this.contentContainer);
				});
				e.stopPropagation();
			});
		},
		/**
		 * Adjust height tile to the highest
		 */
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
		/**
		 * Function to register the click on the tile
		 * @param {jQuery} tileContainer
		 */
		registerTileClickEvent: function (tileContainer) {
			tileContainer.on('click', '.js-card-body', function (e) {
				if ($(e.target).hasClass('js-show-image-preview')) return;
				if ($(e.target).closest('div').hasClass('actions')) return;
				if ($(e.target).is('button') || $(e.target).parent().is('button')) return;
				if ($(e.target).closest('a').hasClass('noLinkBtn')) return;
				if ($(e.target).is('input[type="checkbox"]')) return;
				let recordUrl = $(e.target).closest('.js-tile-container').data('recordurl');
				if (typeof recordUrl !== 'undefined') {
					window.location.href = recordUrl;
				}
			});
		},
		/**
		 * Function which will give you all the list view params
		 * @param {string} urlParams
		 */
		getListViewRecords: function (urlParams) {
			this.listInstance.getListViewRecords(urlParams).done(() => {
				this.setHeightOfTiles(this.contentContainer);
			});
		},
		registerImagePreview() {
			this.contentContainer.on('click', '.js-show-image-preview', (e) => {
				const moduleName = this.contentContainer.find('[name="module"]').length
					? this.contentContainer.find('[name="module"]').val()
					: app.getModuleName();
				const recordId = $(e.target).closest('.js-tile-container').attr('data-record-id');
				const url = `index.php?module=${moduleName}&view=ImagePreview&record=${recordId}`;
				app.showModalWindow('', url, (modalWindow) => {
					let imageSrc = '';
					if ('IMG' === e.target.nodeName) {
						imageSrc = $(e.target).attr('src');
					} else {
						imageSrc = $(e.target).css('background-image');
						imageSrc = imageSrc.replace('url("', '');
						imageSrc = imageSrc.replace('")', '');
					}
					modalWindow.find('img.js-image-preview').attr('src', imageSrc);
				});
			});
		},
		/**
		 * Register events
		 */
		registerEvents: function () {
			this.listInstance = new Vtiger_List_Js();
			this.listInstance.registerEvents();
			const topMenuContainer = this.listInstance.getListViewTopMenuContainer();
			this.contentContainer = this.listInstance.getListViewContainer();
			this.registerTileSizeChange(topMenuContainer);
			this.setHeightOfTiles();
			this.registerTileClickEvent(this.contentContainer);
			this.registerImagePreview();
		}
	}
);
