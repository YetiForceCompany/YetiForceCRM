/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'OpenStreetMap_Map_Js',
	{},
	{
		container: false,
		mapInstance: false,
		selectedParams: false,
		layerMarkers: false,
		markers: false,
		cacheMarkers: [],
		polygonLayer: false,
		routeLayer: false,
		recordsIds: '',
		cacheLayerMarkers: {},
		indirectPointLayer: {},
		setSelectedParams: function (params) {
			delete params['view'];
			this.selectedParams = params;
		},
		registerMap: function (startCoordinate, startZoom) {
			let attribution =
				'&copy; <a href="https://yetiforce.com/en/yetiforce/license" rel="noreferrer noopener">YetiForce Map powered by Open Street Map</a>';
			if (CONFIG.disableBranding) {
				attribution = '';
			}
			this.mapInstance = L.map('mapid').setView(startCoordinate, startZoom);
			L.tileLayer($('.js-tile-layer-server').val(), {
				maxZoom: 19,
				attribution: attribution
			}).addTo(this.mapInstance);
			return this.mapInstance;
		},
		setMarkers: function (data) {
			var thisInstance = this;
			var markerArray = [];
			var container = this.container;
			var map = this.mapInstance;
			if (typeof data.coordinates !== 'undefined') {
				var markers = L.markerClusterGroup({
					maxClusterRadius: 10
				});
				if (typeof this.layerMarkers !== 'boolean') {
					map.removeLayer(this.layerMarkers);
				}
				var records = [];
				data.coordinates.forEach(function (e) {
					markerArray.push([e.lat, e.lon]);
					var marker = L.marker([e.lat, e.lon], {
						icon: L.AwesomeMarkers.icon({
							icon: 'home',
							markerColor: 'blue',
							prefix: 'fa',
							iconColor: e.color
						})
					}).bindPopup(e.label);
					markers.addLayer(marker);
					records.push(e.recordId);
				});
				this.recordsIds = records;
				this.markers = data.coordinates;
				this.layerMarkers = markers;
				map.addLayer(markers);
			}
			if (typeof this.polygonLayer !== 'boolean') {
				map.removeLayer(this.polygonLayer);
			}
			if (typeof data.coordinatesCenter !== 'undefined') {
				if (typeof data.coordinatesCenter.error === 'undefined') {
					let radius = container.find('.js-radius').val();
					markerArray.push([data.coordinatesCenter.lat, data.coordinatesCenter.lon]);
					let marker = L.marker([data.coordinatesCenter.lat, data.coordinatesCenter.lon], {
						icon: L.AwesomeMarkers.icon({
							icon: 'search',
							markerColor: 'red',
							prefix: 'fa'
						})
					}).bindPopup(this.getMarkerPopup(container.find('.js-search-address').val(), data.coordinatesCenter));
					map.addLayer(marker);
					if ($.isNumeric(radius)) {
						radius = parseInt(radius) * 1000;
						let circle = L.circle([data.coordinatesCenter.lat, data.coordinatesCenter.lon], radius, {
							color: 'red',
							fillColor: '#f03',
							fillOpacity: 0.05
						});
						this.polygonLayer = L.featureGroup([circle]);
						map.addLayer(this.polygonLayer);
					}
				} else {
					Vtiger_Helper_Js.showMessage({
						title: app.vtranslate('JS_LBL_PERMISSION'),
						text: data.coordinatesCenter.error,
						type: 'error'
					});
				}
			}
			if (typeof data.cache !== 'undefined') {
				Object.keys(data.cache).forEach((key) => {
					if (typeof thisInstance.cacheLayerMarkers[key] !== 'undefined') {
						map.removeLayer(thisInstance.cacheLayerMarkers[key]);
					}
					var markersCache = L.markerClusterGroup({
						maxClusterRadius: 10
					});
					let coordinates = data.cache[key];
					coordinates.forEach((e) => {
						if (thisInstance.recordsIds.indexOf(e.recordId) === -1) {
							markerArray.push([e.lat, e.lon]);
							var marker = L.marker([e.lat, e.lon], {
								icon: L.AwesomeMarkers.icon({
									icon: 'home',
									markerColor: 'orange',
									prefix: 'fa',
									iconColor: e.color
								})
							}).bindPopup(e.label);
							markersCache.addLayer(marker);
						}
					});
					thisInstance.cacheMarkers[key] = coordinates;
					map.addLayer(markersCache);
					thisInstance.cacheLayerMarkers[key] = markersCache;
				});
			}

			var legendContainer = this.container.find('.js-legend-container');
			if (typeof data.legend !== 'undefined') {
				let html = '';
				data.legend.forEach(function (e) {
					html +=
						'<div class="float-left mt-2"><span class="leegendIcon mt-1" style="background:' +
						e.color +
						'"></span> ' +
						e.value +
						'</div>';
				});
				legendContainer.html(html);
			} else {
				legendContainer.html('');
			}
			if (markerArray.length) map.fitBounds(markerArray);
			this.container.find('.groupNeighbours').prop('checked', true);
		},
		showCalculateBtn: function () {
			var container = this.container;
			var endAddress = container.find('.end').val();
			var startAddress = container.find('.start').val();
			if (endAddress.length > 0 && startAddress.length > 0) {
				container.find('.js-calculate-route').parent().removeClass('d-none');
			}
		},
		registerCacheEvents: function (container) {
			var thisInstance = this;
			container.find('.showRecordsFromCache').on('change', (e) => {
				const currentTarget = $(e.currentTarget),
					moduleName = currentTarget.data('module');
				if (currentTarget.is(':checked')) {
					AppConnector.request({
						module: 'OpenStreetMap',
						action: 'GetMarkers',
						srcModule: app.getModuleName(),
						cache: [moduleName]
					}).done((response) => {
						this.setMarkers(response.result);
					});
				} else {
					this.mapInstance.removeLayer(this.cacheLayerMarkers[moduleName]);
				}
			});
			container.find('.copyToClipboard').on('click', function () {
				var params = {
					module: 'OpenStreetMap',
					action: 'ClipBoard',
					mode: 'save',
					recordIds: JSON.stringify(thisInstance.recordsIds),
					srcModule: app.getModuleName()
				};
				AppConnector.request(params).done(function (response) {
					Vtiger_Helper_Js.showMessage({
						text: app.vtranslate('JS_NOTIFY_COPY_TEXT'),
						type: 'success'
					});
					var countRecords = container.find('.countRecords' + app.getModuleName());
					countRecords.html(response.result);
					countRecords.closest('.cacheModuleContainer').find('.js-delete-clip-board').removeClass('d-none');
				});
			});
			container.find('.js-delete-clip-board').on('click', function (e) {
				var currentTarget = $(e.currentTarget);
				var moduleName = currentTarget.data('module');
				var params = {
					module: 'OpenStreetMap',
					action: 'ClipBoard',
					mode: 'delete',
					srcModule: moduleName
				};
				AppConnector.request(params).done(function (response) {
					Vtiger_Helper_Js.showMessage({
						title: app.vtranslate('JS_LBL_PERMISSION'),
						text: app.vtranslate('JS_SAVE_NOTIFY_OK'),
						type: 'success'
					});
					var countRecords = container.find('.countRecords' + moduleName);
					countRecords.html('');
					currentTarget.addClass('d-none');
					countRecords.closest('.cacheModuleContainer').find('.showRecordsFromCache').prop('checked', false);
					countRecords.closest('.cacheModuleContainer').find('.showRecordsFromCache').trigger('change');
				});
			});
			container.find('.addAllRecords').on('click', function (e) {
				var currentTarget = $(e.currentTarget);
				var moduleName = currentTarget.data('module');
				var params = {
					module: 'OpenStreetMap',
					action: 'ClipBoard',
					mode: 'addAllRecords',
					srcModule: moduleName
				};
				AppConnector.request(params).done(function (response) {
					Vtiger_Helper_Js.showMessage({
						text: app.vtranslate('JS_MESSAGE_DOWNLOADED_ADDRESS_DATA'),
						type: 'success'
					});
					container.find('.countRecords' + moduleName).html(response.result.count);
					var moduleContainer = currentTarget.closest('.cacheModuleContainer');
					moduleContainer.find('.showRecordsFromCache').prop('checked', true);
					moduleContainer.find('.showRecordsFromCache').trigger('change');
					if (response.result.count != '0') moduleContainer.find('.js-delete-clip-board').removeClass('d-none');
				});
			});
		},
		getCacheParamsToRequest: function () {
			let params = [];
			this.container.find('.showRecordsFromCache').each(function () {
				var currentObject = $(this);
				if (currentObject.is(':checked')) params.push(currentObject.data('module'));
			});
			return params;
		},
		registerSearchCompany: function () {
			const searchValue = this.container.find('.js-search-company');
			const searchModule = this.container.find('.searchModule');
			$.widget('custom.ivAutocomplete', $.ui.autocomplete, {
				_create: function () {
					this._super();
					this.widget().menu('option', 'items', '> :not(.ui-autocomplete-category)');
				},
				_renderMenu: function (ul, items) {
					let that = this,
						currentCategory = '';
					$.each(items, function (_index, item) {
						let li;
						console.log(item.category != currentCategory, item);
						if (item.category != currentCategory) {
							ul.append("<li class='ui-autocomplete-category'>" + item.category + '</li>');
							currentCategory = item.category;
						}
						li = that._renderItemData(ul, item);
						if (item.category) {
							li.attr('aria-label', item.category + ' : ' + item.label);
						}
					});
				},
				_renderItemData: function (ul, item) {
					return this._renderItem(ul, item).data('ui-autocomplete-item', item);
				},
				_renderItem: function (ul, item) {
					return $('<li>').data('item.autocomplete', item).append($('<a></a>').html(item.label)).appendTo(ul);
				}
			});
			searchValue.ivAutocomplete({
				delay: '600',
				minLength: '3',
				source: function (_request, response) {
					AppConnector.request({
						module: searchModule.val(),
						currentModule: app.getModuleName(),
						searchModule: searchModule.val(),
						view: 'BasicAjax',
						mode: 'showSearchResults',
						value: searchValue.val(),
						html: false
					}).done(function (responseAjax) {
						responseAjax = JSON.parse(responseAjax);
						let responseDataList = responseAjax.result;
						if (responseDataList.length <= 0) {
							responseDataList.push({
								label: app.vtranslate('JS_NO_RESULTS_FOUND'),
								type: 'no results',
								category: ''
							});
						}
						response(responseDataList);
					});
				},
				select: (_event, ui) => {
					this.recordsIds.push(ui.item.id);
					AppConnector.request({
						module: 'OpenStreetMap',
						action: 'ClipBoard',
						mode: 'addRecord',
						record: ui.item.id,
						srcModuleName: searchModule.val()
					}).done((response) => {
						if (response.result.length == 1) {
							let marker = L.marker([response.result[0].lat, response.result[0].lon], {
								icon: L.AwesomeMarkers.icon({
									icon: 'home',
									markerColor: 'cadetblue',
									prefix: 'fa',
									iconColor: response.result[0].color
								})
							}).bindPopup(response.result[0].label);
							this.layerMarkers.addLayer(marker);
							this.mapInstance.addLayer(this.layerMarkers);
							this.mapInstance.setView(new L.LatLng(response.result[0].lat, response.result[0].lon), 13);
						} else {
							Vtiger_Helper_Js.showMessage({
								title: app.vtranslate('JS_LBL_PERMISSION'),
								text: response.result,
								type: 'error'
							});
						}
					});
				}
			});
		},
		registerSearchAddress: function () {
			const searchValue = this.container.find('.js-search-address'),
				searchBtn = this.container.find('.js-search-btn'),
				operator = this.container.find('.js-select-operator');
			if (operator.length && operator.val()) {
				searchValue
					.autocomplete({
						delay: 600,
						minLength: 3,
						source: function (request, response) {
							AppConnector.request({
								module: app.getModuleName(),
								action: 'Fields',
								mode: 'findAddress',
								type: operator.val(),
								value: request.term
							})
								.done(function (requestData) {
									if (requestData.result === false) {
										app.showNotify({
											title: app.vtranslate('JS_ERROR'),
											type: 'error'
										});
									} else if (requestData.result.length) {
										response(requestData.result);
									} else {
										response([{ label: app.vtranslate('JS_NO_RESULTS_FOUND'), value: '' }]);
									}
								})
								.fail(function (_textStatus, _errorThrown, jqXHR) {
									app.showNotify({
										title: app.vtranslate('JS_ERROR'),
										text: jqXHR.responseJSON.error.message,
										type: 'error',
										animation: 'show'
									});
									response([{ label: app.vtranslate('JS_NO_RESULTS_FOUND'), value: '' }]);
								});
						},
						select: (_event, ui) => {
							if (ui.item.coordinates) {
								let marker = L.marker([ui.item.coordinates.lat, ui.item.coordinates.lon], {
									icon: L.AwesomeMarkers.icon({
										icon: 'home',
										markerColor: 'cadetblue',
										prefix: 'fa'
									})
								}).bindPopup(this.getMarkerPopup(ui.item.label, ui.item.coordinates));
								this.layerMarkers.addLayer(marker);
								this.mapInstance.addLayer(this.layerMarkers);
								this.mapInstance.setView(new L.LatLng(ui.item.coordinates.lat, ui.item.coordinates.lon), 10);
							} else {
								searchValue.val(ui.item.label);
								searchBtn.trigger('click');
							}
						}
					})
					.autocomplete('instance')._renderItem = function (ul, item) {
					return $('<li>')
						.append(`<div><span class="fi fi-${item.countryCode}"></span> ${item.label}</div>`)
						.appendTo(ul);
				};
			}
			this.container.find('.js-search-address,.js-radius').on('keydown', (e) => {
				if (e.code === 'Enter') {
					searchBtn.trigger('click');
				}
			});
			searchBtn.on('click', () => {
				const progressIndicatorElement = jQuery.progressIndicator({
					position: this.container,
					blockInfo: {
						enabled: true
					}
				});
				let params = {
					module: 'OpenStreetMap',
					action: 'GetMarkers',
					srcModule: app.getModuleName(),
					searchValue: this.container.find('.js-search-address').val(),
					cache: this.getCacheParamsToRequest()
				};
				const radiusValue = this.container.find('.js-radius').val();
				if (radiusValue !== '' && parseInt(radiusValue)) {
					params['radius'] = parseInt(radiusValue);
				}
				AppConnector.request($.extend(this.selectedParams, params)).done((response) => {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					this.setMarkers(response.result);
				});
			});
		},
		registerMyLocation: function () {
			const locationBtn = this.container.find('.js-my-location-btn');
			if (!navigator.geolocation) {
				locationBtn.addClass('d-none');
				return;
			}
			navigator.permissions.query({ name: 'geolocation' }).then((response) => {
				if (response.state === 'denied') {
					locationBtn.addClass('d-none');
				}
			});
			locationBtn.on('click', () => {
				navigator.geolocation.getCurrentPosition((position) => {
					this.addMarker(
						position.coords.latitude,
						position.coords.longitude,
						this.getMarkerPopup(
							locationBtn.data('label'),
							{
								lat: position.coords.latitude,
								lon: position.coords.longitude
							},
							false
						),
						L.AwesomeMarkers.icon({
							icon: 'street-view',
							prefix: 'fa'
						})
					);
					this.mapInstance.setView(new L.LatLng(position.coords.latitude, position.coords.longitude), 12);
				});
			});
		},
		registerBasicModal: function () {
			this.registerSearchAddress();
			this.registerMyLocation();
		},
		registerPanel: function () {
			let thisInstance = this;
			let container = this.container;
			let map = this.mapInstance;
			let layer, description;
			app.registerBlockAnimationEvent(container);
			this.registerCacheEvents(container);
			container.find('.groupBy').on('click', () => {
				let progressIndicator = jQuery.progressIndicator({
					position: container,
					blockInfo: {
						enabled: true
					}
				});
				let params = {
					module: 'OpenStreetMap',
					action: 'GetMarkers',
					srcModule: app.getModuleName(),
					groupBy: container.find('.fieldsToGroup').val(),
					searchValue: container.find('.js-search-address').val(),
					radius: container.find('.js-radius').val(),
					cache: this.getCacheParamsToRequest()
				};
				params = $.extend(this.selectedParams, params);
				AppConnector.request(params).done((response) => {
					progressIndicator.progressIndicator({ mode: 'hide' });
					this.setMarkers(response.result);
				});
			});
			container.find('.groupNeighbours').on('change', (group) => {
				let currentTarget = $(group.currentTarget);
				map.removeLayer(this.layerMarkers);
				let markers = this.markers;
				if (currentTarget.is(':checked')) {
					layer = L.markerClusterGroup({
						maxClusterRadius: 10
					});
					markers.forEach((e) => {
						let marker = L.marker([e.lat, e.lon], {
							icon: L.AwesomeMarkers.icon({
								icon: 'home',
								markerColor: 'blue',
								prefix: 'fa',
								iconColor: e.color
							})
						}).bindPopup(e.label);
						layer.addLayer(marker);
					});
					Object.keys(this.cacheLayerMarkers).forEach((key) => {
						map.removeLayer(this.cacheLayerMarkers[key]);
						let cacheLayer = L.markerClusterGroup({
							maxClusterRadius: 10
						});
						this.cacheMarkers[key].forEach((e) => {
							let marker = L.marker([e.lat, e.lon], {
								icon: L.AwesomeMarkers.icon({
									icon: 'home',
									markerColor: 'orange',
									prefix: 'fa',
									iconColor: e.color
								})
							}).bindPopup(e.label);
							cacheLayer.addLayer(marker);
						});
						this.cacheLayerMarkers[key] = cacheLayer;
						map.addLayer(cacheLayer);
					});
				} else {
					let markerArray = [];
					markers.forEach((e) => {
						let marker = L.marker([e.lat, e.lon], {
							icon: L.AwesomeMarkers.icon({
								icon: 'home',
								markerColor: 'blue',
								prefix: 'fa',
								iconColor: e.color
							})
						}).bindPopup(e.label);
						markerArray.push(marker);
					});
					layer = L.featureGroup(markerArray);
					Object.keys(this.cacheLayerMarkers).forEach((key) => {
						map.removeLayer(this.cacheLayerMarkers[key]);
						let markerArray = [];
						this.cacheMarkers[key].forEach((e) => {
							let marker = L.marker([e.lat, e.lon], {
								icon: L.AwesomeMarkers.icon({
									icon: 'home',
									markerColor: 'orange',
									prefix: 'fa',
									iconColor: e.color
								})
							}).bindPopup(e.label);
							markerArray.push(marker);
						});
						this.cacheLayerMarkers[key] = L.featureGroup(markerArray);
						map.addLayer(this.cacheLayerMarkers[key]);
					});
				}
				this.layerMarkers = layer;
				map.addLayer(layer);
			});
			let startIconLayer = false;
			container.on('click', '.startTrack', (e) => {
				if (startIconLayer) {
					map.removeLayer(startIconLayer);
				}
				let currentTargetTrack = $(e.currentTarget);
				let containerPopup = currentTargetTrack.closest('.leaflet-popup-content');
				description = containerPopup.find('.description').html();
				let startElement = container.find('.start');
				let coordinates = containerPopup.find('.coordinates');
				description = description.replace(/\<br\>/gi, ', ');
				startElement.val(description);
				startElement.data('lat', coordinates.data('lat'));
				startElement.data('lon', coordinates.data('lon'));
				let marker = L.marker([coordinates.data('lat'), coordinates.data('lon')], {
					icon: L.AwesomeMarkers.icon({
						icon: 'truck',
						markerColor: 'green',
						prefix: 'fa'
					})
				}).bindPopup(containerPopup.html());
				startIconLayer = L.featureGroup([marker]);
				map.addLayer(startIconLayer);
				this.showCalculateBtn();
			});
			let endIconLayer = false;
			container.on('click', '.endTrack', (e) => {
				if (endIconLayer) {
					map.removeLayer(endIconLayer);
				}
				let currentTargetTrack = $(e.currentTarget);
				let containerPopup = currentTargetTrack.closest('.leaflet-popup-content');
				description = containerPopup.find('.description').html();
				let endElement = container.find('.end');
				let coordinates = containerPopup.find('.coordinates');
				description = description.replace(/\<br\>/gi, ', ');
				endElement.val(description);
				endElement.data('lat', coordinates.data('lat'));
				endElement.data('lon', coordinates.data('lon'));
				let marker = L.marker([coordinates.data('lat'), coordinates.data('lon')], {
					icon: L.AwesomeMarkers.icon({
						icon: 'flag-checkered',
						markerColor: 'red',
						prefix: 'fa'
					})
				}).bindPopup(containerPopup.html());
				endIconLayer = L.featureGroup([marker]);
				map.addLayer(endIconLayer);
				this.showCalculateBtn();
			});
			container.on('click', '.indirectPoint', (e) => {
				let currentTarget = $(e.currentTarget);
				let containerPopup = currentTarget.closest('.leaflet-popup-content');
				description = containerPopup.find('.description').html();
				let template = container.find('.indirectTemplate');
				let indirect = template.clone();
				template.before(indirect);
				indirect.removeClass('indirectTemplate');
				indirect.removeClass('d-none');
				let coordinates = containerPopup.find('.coordinates');
				description = description.replace(/\<br\>/gi, ', ');
				if (typeof this.indirectPointLayer[description] !== 'undefined') {
					map.removeLayer(this.indirectPointLayer[description]);
				}
				let indirectField = indirect.find('.indirect');
				indirectField.val(description);
				indirectField.data('lat', coordinates.data('lat'));
				indirectField.data('lon', coordinates.data('lon'));
				let marker = L.marker([coordinates.data('lat'), coordinates.data('lon')], {
					icon: L.AwesomeMarkers.icon({
						icon: 'flag',
						markerColor: 'orange',
						prefix: 'fa'
					})
				}).bindPopup(containerPopup.html());
				this.indirectPointLayer[description] = L.featureGroup([marker]);
				map.addLayer(this.indirectPointLayer[description]);
			});
			container.on('click', '.removeIndirect', (e) => {
				let currentTarget = $(e.currentTarget);
				let container = currentTarget.closest('.indirectContainer');
				map.removeLayer(this.indirectPointLayer[container.find('.indirect').val()]);
				currentTarget.closest('.indirectContainer').remove();
			});
			container.on('click', '.moveUp', (e) => {
				let currentTarget = $(e.currentTarget);
				let container = currentTarget.closest('.indirectContainer');
				let previousElement = container.prev();
				if (!previousElement.hasClass('startContainer')) {
					previousElement.before(container);
				}
			});
			container.on('click', '.moveDown', (e) => {
				let currentTarget = $(e.currentTarget);
				let container = currentTarget.closest('.indirectContainer');
				let nextElement = container.next();
				if (!nextElement.hasClass('indirectTemplate')) {
					nextElement.after(container);
				}
			});
			container.on('click', '.searchInRadius', (e) => {
				if (endIconLayer) {
					map.removeLayer(endIconLayer);
				}
				let currentTarget = $(e.currentTarget);
				let containerPopup = currentTarget.closest('.leaflet-popup-content');
				let coordinates = containerPopup.find('.coordinates');
				let progressIndicatorElement = jQuery.progressIndicator({
					position: container,
					blockInfo: {
						enabled: true
					}
				});
				let params = {
					module: 'OpenStreetMap',
					action: 'GetMarkers',
					srcModule: app.getModuleName(),
					radius: container.find('.radius').val(),
					lat: coordinates.data('lat'),
					lon: coordinates.data('lon'),
					cache: this.getCacheParamsToRequest()
				};
				params = $.extend(this.selectedParams, params);
				AppConnector.request(params).done(function (response) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					this.setMarkers(response.result);
				});
			});
			const descriptionContainer = container.find('.js-description-container');
			container.find('.js-calculate-route').on('click', () => {
				let indirectLon = [];
				let indirectLat = [];
				container.find('.indirectContainer:not(.d-none) input.indirect').each(function () {
					let currentTarget = $(this);
					indirectLat.push(currentTarget.data('lat'));
					indirectLon.push(currentTarget.data('lon'));
				});
				let endElement = container.find('.end');
				let startElement = container.find('.start');
				let progressIndicatorElement = jQuery.progressIndicator({
					position: container,
					blockInfo: {
						enabled: true
					}
				});
				AppConnector.request({
					url: 'index.php',
					data: {
						module: 'OpenStreetMap',
						action: 'GetRoute',
						flon: startElement.data('lon'),
						flat: startElement.data('lat'),
						ilon: indirectLon,
						ilat: indirectLat,
						tlon: endElement.data('lon'),
						tlat: endElement.data('lat')
					}
				})
					.done((response) => {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						if (this.routeLayer) {
							map.removeLayer(this.routeLayer);
						}
						let route = L.geoJson(response.result.geoJson);
						this.routeLayer = L.featureGroup([route]);
						map.addLayer(this.routeLayer);
						descriptionContainer.removeClass('d-none');
						const instruction = container.find('.js-instruction_block');
						if (response.result.properties.description) {
							instruction.removeClass('d-none');
							descriptionContainer.find('.js-instruction_body').html(response.result.properties.description);
						} else {
							instruction.addClass('d-none');
						}
						descriptionContainer
							.find('.distance')
							.html(App.Fields.Double.formatToDisplay(response.result.properties.distance));
						descriptionContainer
							.find('.travelTime')
							.html(App.Fields.Double.formatToDisplay(response.result.properties.traveltime / 60));
					})
					.fail(function (error, _title) {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						app.showNotify({
							titleTrusted: false,
							textTrusted: false,
							title: app.vtranslate('JS_UNEXPECTED_ERROR'),
							text: error,
							type: 'error'
						});
					});
			});
			container.on('click', '.setView', (e) => {
				let currentTarget = $(e.currentTarget);
				let inputInstance = currentTarget.closest('.input-group').find('.end,.start,.indirect');
				let lat = inputInstance.data('lat');
				let lon = inputInstance.data('lon');
				if (!(typeof lat === 'undefined' && typeof lon === 'undefined')) {
					map.setView(new L.LatLng(lat, lon), 11);
				}
			});
			this.registerSearchCompany();
		},
		registerModalView: function (container) {
			this.container = container;
			const progress = jQuery.progressIndicator({
				position: container,
				blockInfo: {
					enabled: true
				}
			});
			container.find('#mapid').css({
				height: $('body').height() - 160
			});
			this.registerMap([0, 0], 2);
			this.registerBasicModal();
			this.registerPanel();
			AppConnector.request(
				$.extend(this.selectedParams, {
					module: 'OpenStreetMap',
					action: 'GetMarkers',
					srcModule: app.getModuleName()
				})
			).done((response) => {
				progress.progressIndicator({ mode: 'hide' });
				this.setMarkers(response.result);
			});
		},
		getMarkerPopup: function (label, coordinates, btn = true) {
			let popup = `<span class="description">${label}</span>
			<br /><input type=hidden class="coordinates" data-lat="${coordinates.lat}" data-lon="${coordinates.lon}">`;
			if (btn) {
				popup += `<button class="btn btn-success btn-sm p-1 startTrack mr-2"><span class="fas  fa-truck"></span></button>
		<button class="btn btn-warning btn-sm p-1 indirectPoint mr-2"><span class="fas fa-flag-checkered"></span></button>
		<button class="btn btn-danger btn-sm p-1 endTrack"><span class="fas fa-flag-checkered"></span></button>`;
			}
			popup += `<span class="border d-block my-1 p-1">${coordinates.lat}, ${coordinates.lon}</span>`;
			return popup;
		},
		registerDetailView: function (container) {
			this.container = container;
			let coordinates = container.find('#coordinates').val();
			coordinates = JSON.parse(coordinates);
			let startCoordinate = [0, 0],
				startZoom = 2;
			const $map = container.find('#mapid');
			if (coordinates.length) {
				startCoordinate = coordinates[0];
				startZoom = 6;
			}
			if ($('.mainBody').length) {
				if ($('.mainBody').height() < 1000) {
					$map.height(
						$('.mainBody').height() -
							($('.detailViewTitle').height() + $('.detailViewContainer .related').height() + 25)
					);
				} else {
					$map.height(1000);
				}
			} else {
				if ($('.bodyContents').height() < 1000) {
					$map.height(
						$('.bodyContents').height() -
							($('.detailViewTitle').height() + $('.detailViewContainer .related').height() + 25)
					);
				} else {
					$map.height(1000);
				}
			}
			const myMap = this.registerMap(startCoordinate, startZoom),
				markers = L.markerClusterGroup({
					maxClusterRadius: 10
				});
			coordinates.forEach(function (e) {
				markers.addLayer(
					L.marker([e.lat, e.lon], {
						icon: L.AwesomeMarkers.icon({
							icon: 'home',
							markerColor: 'blue',
							prefix: 'fa',
							iconColor: e.color
						})
					}).bindPopup(e.label)
				);
			});
			myMap.addLayer(markers);
		},
		registerFromField: function (container, _fieldInstance) {
			this.container = container;
			container.find('#mapid').css({
				height: $('body').height() - 160
			});
			const decimalPoint = container.find('.js-point-decimal');
			let point = null;
			if (decimalPoint.length > 0) {
				point = JSON.parse(decimalPoint.val());
			}
			if (point) {
				this.registerMap([point['lat'], point['lon']], 14);
			} else {
				this.registerMap([0, 0], 3);
			}
			this.registerBasicModal();
			this.layerMarkers = L.markerClusterGroup({
				maxClusterRadius: 10
			});
			if (point) {
				this.addMarker(
					point['lat'],
					point['lon'],
					this.getMarkerPopup(
						'',
						{
							lat: point['lat'],
							lon: point['lon']
						},
						false
					)
				);
			}
		},
		addMarker: function (lat, lon, label, icon) {
			if (!icon) {
				icon = L.AwesomeMarkers.icon({
					icon: 'home',
					markerColor: 'cadetblue',
					prefix: 'fa'
				});
			}
			const marker = L.marker([lat, lon], { icon: icon });
			if (label) {
				marker.bindPopup(label);
			}
			this.layerMarkers.addLayer(marker);
			this.mapInstance.addLayer(this.layerMarkers);
		}
	}
);
