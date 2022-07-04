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
					let marker = L.marker([position.coords.latitude, position.coords.longitude], {
						icon: L.AwesomeMarkers.icon({
							icon: 'home',
							markerColor: 'cadetblue',
							prefix: 'fa'
						})
					}).bindPopup(
						this.getMarkerPopup(locationBtn.data('label'), {
							lat: position.coords.latitude,
							lon: position.coords.longitude
						})
					);
					this.layerMarkers.addLayer(marker);
					this.mapInstance.addLayer(this.layerMarkers);
					this.mapInstance.setView(new L.LatLng(position.coords.latitude, position.coords.longitude), 10);
				});
			});
		},
		registerBasicModal: function () {
			var thisInstance = this;
			var container = this.container;
			var map = thisInstance.mapInstance;
			var layer, description;
			app.registerBlockAnimationEvent(container);
			thisInstance.registerCacheEvents(container);
			container.find('.groupBy').on('click', function () {
				var progressIndicatorElement = jQuery.progressIndicator({
					position: container,
					blockInfo: {
						enabled: true
					}
				});
				var params = {
					module: 'OpenStreetMap',
					action: 'GetMarkers',
					srcModule: app.getModuleName(),
					groupBy: container.find('.fieldsToGroup').val(),
					searchValue: container.find('.js-search-address').val(),
					radius: container.find('.js-radius').val(),
					cache: thisInstance.getCacheParamsToRequest()
				};
				params = $.extend(thisInstance.selectedParams, params);
				AppConnector.request(params).done(function (response) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					thisInstance.setMarkers(response.result);
				});
			});
			container.find('.groupNeighbours').on('change', function (e) {
				var currentTarget = $(e.currentTarget);
				map.removeLayer(thisInstance.layerMarkers);
				var markers = thisInstance.markers;
				if (currentTarget.is(':checked')) {
					layer = L.markerClusterGroup({
						maxClusterRadius: 10
					});
					markers.forEach(function (e) {
						var marker = L.marker([e.lat, e.lon], {
							icon: L.AwesomeMarkers.icon({
								icon: 'home',
								markerColor: 'blue',
								prefix: 'fa',
								iconColor: e.color
							})
						}).bindPopup(e.label);
						layer.addLayer(marker);
					});

					Object.keys(thisInstance.cacheLayerMarkers).forEach(function (key) {
						map.removeLayer(thisInstance.cacheLayerMarkers[key]);
						var cacheLayer = L.markerClusterGroup({
							maxClusterRadius: 10
						});
						thisInstance.cacheMarkers[key].forEach(function (e) {
							var marker = L.marker([e.lat, e.lon], {
								icon: L.AwesomeMarkers.icon({
									icon: 'home',
									markerColor: 'orange',
									prefix: 'fa',
									iconColor: e.color
								})
							}).bindPopup(e.label);
							cacheLayer.addLayer(marker);
						});
						thisInstance.cacheLayerMarkers[key] = cacheLayer;
						map.addLayer(cacheLayer);
					});
				} else {
					var markerArray = [];
					markers.forEach(function (e) {
						var marker = L.marker([e.lat, e.lon], {
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
					Object.keys(thisInstance.cacheLayerMarkers).forEach(function (key) {
						map.removeLayer(thisInstance.cacheLayerMarkers[key]);
						var markerArray = [];
						thisInstance.cacheMarkers[key].forEach(function (e) {
							var marker = L.marker([e.lat, e.lon], {
								icon: L.AwesomeMarkers.icon({
									icon: 'home',
									markerColor: 'orange',
									prefix: 'fa',
									iconColor: e.color
								})
							}).bindPopup(e.label);
							markerArray.push(marker);
						});
						thisInstance.cacheLayerMarkers[key] = L.featureGroup(markerArray);
						map.addLayer(thisInstance.cacheLayerMarkers[key]);
					});
				}
				thisInstance.layerMarkers = layer;
				map.addLayer(layer);
			});
			var startIconLayer = false;
			container.on('click', '.startTrack', function (e) {
				if (startIconLayer) {
					map.removeLayer(startIconLayer);
				}
				var currentTarget = $(e.currentTarget);
				var containerPopup = currentTarget.closest('.leaflet-popup-content');
				description = containerPopup.find('.description').html();
				var startElement = container.find('.start');
				var coordinates = containerPopup.find('.coordinates');
				description = description.replace(/\<br\>/gi, ', ');
				startElement.val(description);
				startElement.data('lat', coordinates.data('lat'));
				startElement.data('lon', coordinates.data('lon'));
				var marker = L.marker([coordinates.data('lat'), coordinates.data('lon')], {
					icon: L.AwesomeMarkers.icon({
						icon: 'truck',
						markerColor: 'green',
						prefix: 'fa'
					})
				}).bindPopup(containerPopup.html());
				startIconLayer = L.featureGroup([marker]);
				map.addLayer(startIconLayer);
				thisInstance.showCalculateBtn();
			});
			var endIconLayer = false;
			container.on('click', '.endTrack', function (e) {
				if (endIconLayer) {
					map.removeLayer(endIconLayer);
				}
				var currentTarget = $(e.currentTarget);
				var containerPopup = currentTarget.closest('.leaflet-popup-content');
				description = containerPopup.find('.description').html();
				var endElement = container.find('.end');
				var coordinates = containerPopup.find('.coordinates');
				description = description.replace(/\<br\>/gi, ', ');
				endElement.val(description);
				endElement.data('lat', coordinates.data('lat'));
				endElement.data('lon', coordinates.data('lon'));
				var marker = L.marker([coordinates.data('lat'), coordinates.data('lon')], {
					icon: L.AwesomeMarkers.icon({
						icon: 'flag-checkered',
						markerColor: 'red',
						prefix: 'fa'
					})
				}).bindPopup(containerPopup.html());
				endIconLayer = L.featureGroup([marker]);
				map.addLayer(endIconLayer);
				thisInstance.showCalculateBtn();
			});

			container.on('click', '.indirectPoint', function (e) {
				var currentTarget = $(e.currentTarget);
				var containerPopup = currentTarget.closest('.leaflet-popup-content');
				description = containerPopup.find('.description').html();
				var template = container.find('.indirectTemplate');
				var indirect = template.clone();
				template.before(indirect);
				indirect.removeClass('indirectTemplate');
				indirect.removeClass('d-none');
				var coordinates = containerPopup.find('.coordinates');
				description = description.replace(/\<br\>/gi, ', ');
				if (typeof thisInstance.indirectPointLayer[description] !== 'undefined') {
					map.removeLayer(thisInstance.indirectPointLayer[description]);
				}
				var indirectField = indirect.find('.indirect');
				indirectField.val(description);
				indirectField.data('lat', coordinates.data('lat'));
				indirectField.data('lon', coordinates.data('lon'));
				var marker = L.marker([coordinates.data('lat'), coordinates.data('lon')], {
					icon: L.AwesomeMarkers.icon({
						icon: 'flag',
						markerColor: 'orange',
						prefix: 'fa'
					})
				}).bindPopup(containerPopup.html());
				thisInstance.indirectPointLayer[description] = L.featureGroup([marker]);
				map.addLayer(thisInstance.indirectPointLayer[description]);
			});
			container.on('click', '.removeIndirect', function (e) {
				var currentTarget = $(e.currentTarget);
				var container = currentTarget.closest('.indirectContainer');
				map.removeLayer(thisInstance.indirectPointLayer[container.find('.indirect').val()]);
				currentTarget.closest('.indirectContainer').remove();
			});
			container.on('click', '.moveUp', function (e) {
				var currentTarget = $(e.currentTarget);
				var container = currentTarget.closest('.indirectContainer');
				var previousElement = container.prev();
				if (!previousElement.hasClass('startContainer')) {
					previousElement.before(container);
				}
			});
			container.on('click', '.moveDown', function (e) {
				var currentTarget = $(e.currentTarget);
				var container = currentTarget.closest('.indirectContainer');
				var nextElement = container.next();
				if (!nextElement.hasClass('indirectTemplate')) {
					nextElement.after(container);
				}
			});
			container.on('click', '.searchInRadius', function (e) {
				if (endIconLayer) {
					map.removeLayer(endIconLayer);
				}
				var currentTarget = $(e.currentTarget);
				var containerPopup = currentTarget.closest('.leaflet-popup-content');
				var coordinates = containerPopup.find('.coordinates');
				var progressIndicatorElement = jQuery.progressIndicator({
					position: container,
					blockInfo: {
						enabled: true
					}
				});
				var params = {
					module: 'OpenStreetMap',
					action: 'GetMarkers',
					srcModule: app.getModuleName(),
					radius: container.find('.radius').val(),
					lat: coordinates.data('lat'),
					lon: coordinates.data('lon'),
					cache: thisInstance.getCacheParamsToRequest()
				};
				params = $.extend(thisInstance.selectedParams, params);
				AppConnector.request(params).done(function (response) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					thisInstance.setMarkers(response.result);
				});
			});
			const descriptionContainer = container.find('.js-description-container');
			container.find('.js-calculate-route').on('click', function () {
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
					.done(function (response) {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						if (thisInstance.routeLayer) {
							map.removeLayer(thisInstance.routeLayer);
						}
						let route = L.geoJson(response.result.geoJson);
						thisInstance.routeLayer = L.featureGroup([route]);
						map.addLayer(thisInstance.routeLayer);
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
					.fail(function (error, title) {
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
			container.on('click', '.setView', function (e) {
				let currentTarget = $(e.currentTarget);
				let inputInstance = currentTarget.closest('.input-group').find('.end,.start,.indirect');
				let lat = inputInstance.data('lat');
				let lon = inputInstance.data('lon');
				if (!(typeof lat === 'undefined' && typeof lon === 'undefined')) {
					map.setView(new L.LatLng(lat, lon), 11);
				}
			});
			this.registerSearchCompany();
			this.registerSearchAddress();
			this.registerMyLocation();
		},
		registerModalView: function (container) {
			let thisInstance = this;
			let progressIndicatorElement = jQuery.progressIndicator({
				position: container,
				blockInfo: {
					enabled: true
				}
			});
			this.container = container;
			$('#mapid').css({
				height: $('body').height() - 160
			});
			this.registerMap([0, 0], 2);
			let params = {
				module: 'OpenStreetMap',
				action: 'GetMarkers',
				srcModule: app.getModuleName()
			};
			params = $.extend(this.selectedParams, params);
			thisInstance.registerBasicModal();
			AppConnector.request(params).done(function (response) {
				progressIndicatorElement.progressIndicator({ mode: 'hide' });
				thisInstance.setMarkers(response.result);
			});
		},
		getMarkerPopup: function (label, coordinates) {
			return `<span class="description">${label}</span>
			<br /><input type=hidden class="coordinates" data-lon="${coordinates.lon}"
			data-lat="${coordinates.lat}">
			<button class="btn btn-success btn-sm p-1 startTrack mr-2"><span class="fas  fa-truck"></span></button>
		<button class="btn btn-warning btn-sm p-1 indirectPoint mr-2"><span class="fas fa-flag-checkered"></span></button>
		<button class="btn btn-danger btn-sm p-1 endTrack"><span class="fas fa-flag-checkered"></span></button>`;
		},
		registerDetailView: function (container) {
			this.container = container;
			var coordinates = container.find('#coordinates').val();
			coordinates = JSON.parse(coordinates);
			var startCoordinate = [0, 0];
			var startZoom = 2;
			var $map = container.find('#mapid');
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

			var myMap = this.registerMap(startCoordinate, startZoom);
			var markers = L.markerClusterGroup({
				maxClusterRadius: 10
			});
			coordinates.forEach(function (e) {
				var marker = L.marker([e.lat, e.lon], {
					icon: L.AwesomeMarkers.icon({
						icon: 'home',
						markerColor: 'blue',
						prefix: 'fa',
						iconColor: e.color
					})
				}).bindPopup(e.label);
				markers.addLayer(marker);
			});
			myMap.addLayer(markers);
		}
	}
);
