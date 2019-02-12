/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class("OpenStreetMap_Map_Js", {}, {
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
		var myMap = L.map('mapid').setView(startCoordinate, startZoom);
		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			maxZoom: 19,
			attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" rel="noreferrer noopener">OpenStreetMap</a>'

		}).addTo(myMap);
		this.mapInstance = myMap;
		return myMap;
	},
	setMarkersByResponse: function (response) {
		var thisInstance = this;
		var markerArray = [];
		var container = this.container;
		var map = this.mapInstance;
		if (typeof response.result.coordinates !== "undefined") {
			var markers = L.markerClusterGroup({
				maxClusterRadius: 10
			});
			var coordinates = response.result.coordinates;
			if (typeof this.layerMarkers !== 'boolean') {
				map.removeLayer(this.layerMarkers);
			}
			var records = [];
			coordinates.forEach(function (e) {
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
			this.markers = coordinates;
			this.layerMarkers = markers;
			map.addLayer(markers);
		}
		if (typeof this.polygonLayer !== 'boolean') {
			map.removeLayer(this.polygonLayer);
		}
		if (typeof response.result.coordinatesCeneter !== "undefined") {
			if (typeof response.result.coordinatesCeneter.error === "undefined") {
				var radius = container.find('.js-radius').val();
				markerArray.push([response.result.coordinatesCeneter.lat, response.result.coordinatesCeneter.lon]);
				var popup = '<span class="description">' + container.find('.searchValue').val() + '</span><br /><input type=hidden class="coordinates" data-lon="' + response.result.coordinatesCeneter.lon + '" data-lat="' + response.result.coordinatesCeneter.lat + '">';
				popup += '<button class="btn btn-success btn-sm p-1 startTrack mr-2"><span class="fas  fa-truck"></span></button>';
				popup += '<button class="btn btn-danger btn-sm p-1 endTrack"><span class="fas fa-flag-checkered"></span></button>';
				var marker = L.marker([response.result.coordinatesCeneter.lat, response.result.coordinatesCeneter.lon], {
					icon: L.AwesomeMarkers.icon({
						icon: 'search',
						markerColor: 'red',
						prefix: 'fa',
					})
				}).bindPopup(popup);
				map.addLayer(marker);
				if ($.isNumeric(radius)) {
					radius = parseInt(radius) * 1000;
					var circle = L.circle([response.result.coordinatesCeneter.lat, response.result.coordinatesCeneter.lon], radius, {
						color: 'red',
						fillColor: '#f03',
						fillOpacity: 0.05
					});
					this.polygonLayer = L.featureGroup([circle]);
					map.addLayer(this.polygonLayer);
				}
			} else {
				var params = {
					title: app.vtranslate('JS_LBL_PERMISSION'),
					text: response.result.coordinatesCeneter.error,
					type: 'error',
				};
				Vtiger_Helper_Js.showMessage(params);
			}
		}
		if (typeof response.result.cache !== "undefined") {
			var cache = response.result.cache;
			Object.keys(cache).forEach(function (key) {
				if (typeof thisInstance.cacheLayerMarkers[key] !== "undefined") {
					map.removeLayer(thisInstance.cacheLayerMarkers[key]);
				}
				var markersCache = L.markerClusterGroup({
					maxClusterRadius: 10
				});
				coordinates = cache[key];
				coordinates.forEach(function (e) {
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

		var footer = this.container.find('.modal-footer');
		if (typeof response.result.legend !== "undefined") {
			var html = '';
			var legend = response.result.legend;
			legend.forEach(function (e) {
				html += '<div class="pull-left"><span class="leegendIcon" style="background:' + e.color + '"></span> ' + e.value + '</div>';
			});
			footer.html(html);
		} else {
			footer.html('');
		}
		if (markerArray.length)
			map.fitBounds(markerArray);
		this.container.find('.groupNeighbours').prop('checked', true);
	},
	showCalculateBtn: function () {
		var container = this.container;
		var endAddress = container.find('.end').val();
		var startAddress = container.find('.start').val();
		if (endAddress.length > 0 && startAddress.length > 0) {
			container.find('.calculateTrack').removeClass('d-none');
		}
	},
	registerCacheEvents: function (container) {
		var thisInstance = this;
		container.find('.showRecordsFromCache').on('change', function (e) {
			var currentTarget = $(e.currentTarget);
			var moduleName = currentTarget.data('module');
			if (currentTarget.is(':checked')) {
				var params = {
					module: 'OpenStreetMap',
					action: 'GetMarkers',
					srcModule: app.getModuleName(),
					cache: [moduleName],
				};
				AppConnector.request(params).done(function (response) {
					thisInstance.setMarkersByResponse(response);
				});
			} else {
				thisInstance.mapInstance.removeLayer(thisInstance.cacheLayerMarkers[moduleName]);
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
					type: 'success',
				});
				var countRecords = container.find('.countRecords' + app.getModuleName());
				countRecords.html(response.result);
				countRecords.closest('.cacheModuleContainer').find('.deleteClipBoard').removeClass('d-none');
			});
		});
		container.find('.deleteClipBoard').on('click', function (e) {
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
					type: 'success',
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
					type: 'success',
				});
				container.find('.countRecords' + moduleName).html(response.result.count);
				var moduleContainer = currentTarget.closest('.cacheModuleContainer');
				moduleContainer.find('.showRecordsFromCache').prop('checked', true);
				moduleContainer.find('.showRecordsFromCache').trigger('change');
				if (response.result.count != '0')
					moduleContainer.find('.deleteClipBoard').removeClass('d-none');
			});
		});
	},
	getCacheParamsToRequest: function () {
		var container = this.container;
		var params = [];
		container.find('.showRecordsFromCache').each(function () {
			var currentObject = $(this);
			if (currentObject.is(':checked'))
				params.push(currentObject.data('module'));
		});
		return params;
	},
	registerSearchCompany: function () {
		var container = this.container;
		var searchValue = container.find('.searchCompany');
		var searchModule = container.find('.searchModule');
		var addButton = container.find('.addRecord');
		var thistInstance = this;
		addButton.on('click', function () {
			var map = thistInstance.mapInstance;
			var markers = thistInstance.layerMarkers;
			var crmId = addButton.data('crmId');
			if (crmId == '')
				return false;
			AppConnector.request({
				module: 'OpenStreetMap',
				action: 'ClipBoard',
				mode: 'addRecord',
				record: crmId,
				srcModuleName: searchModule.val()
			}).done(function (response) {
				addButton.data('crmId', '');
				if (response.result.length == 1) {
					var marker = L.marker([response.result[0].lat, response.result[0].lon], {
						icon: L.AwesomeMarkers.icon({
							icon: 'home',
							markerColor: 'cadetblue',
							prefix: 'fa',
							iconColor: response.result[0].color
						})
					}).bindPopup(response.result[0].label);
					markers.addLayer(marker);
					map.addLayer(markers);
					map.setView(new L.LatLng(response.result[0].lat, response.result[0].lon), 14);
				} else {
					Vtiger_Helper_Js.showMessage({
						title: app.vtranslate('JS_LBL_PERMISSION'),
						text: response.result,
						type: 'error',
					});
				}
				searchValue.val('');
			});
		});
		$.widget("custom.ivAutocomplete", $.ui.autocomplete, {
			_create: function () {
				this._super();
				this.widget().menu("option", "items", "> :not(.ui-autocomplete-category)");
			},
			_renderMenu: function (ul, items) {
				var that = this, currentCategory = "";
				$.each(items, function (index, item) {
					var li;
					if (item.category != currentCategory) {
						ul.append("<li class='ui-autocomplete-category'>" + item.category + "</li>");
						currentCategory = item.category;
					}
					that._renderItemData(ul, item);
				});
			},
			_renderItemData: function (ul, item) {
				return this._renderItem(ul, item).data("ui-autocomplete-item", item);
			},
			_renderItem: function (ul, item) {
				return $("<li>")
					.data("item.autocomplete", item)
					.append($("<a></a>").html(item.label))
					.appendTo(ul);
			},
		});
		searchValue.ivAutocomplete({
			delay: '600',
			minLength: '3',
			source: function (request, response) {
				AppConnector.request({
					module: searchModule.val(),
					curentModule: app.getModuleName(),
					searchModule: searchModule.val(),
					view: 'BasicAjax',
					mode: 'showSearchResults',
					value: searchValue.val(),
					html: false,
				}).done(function (responseAjax) {
					responseAjax = JSON.parse(responseAjax);
					var reponseDataList = responseAjax.result;
					if (reponseDataList.length <= 0) {
						reponseDataList.push({
							label: app.vtranslate('JS_NO_RESULTS_FOUND'),
							type: 'no results',
							category: ''
						});
					}
					response(reponseDataList);
				});
			},
			select: function (event, ui) {
				var selected = ui.item;
				addButton.data('crmId', selected.id);
			},
			close: function (event, ui) {

			}

		});
	},
	registerBasicModal: function () {
		var thisInstance = this;
		var container = this.container;
		var map = thisInstance.mapInstance;
		var layer, description;
		thisInstance.registerCacheEvents(container);
		container.find('.groupBy').on('click', function () {
			var progressIndicatorElement = jQuery.progressIndicator({
				'position': container,
				'blockInfo': {
					'enabled': true
				}
			});
			var params = {
				module: 'OpenStreetMap',
				action: 'GetMarkers',
				srcModule: app.getModuleName(),
				groupBy: container.find('.fieldsToGroup').val(),
				searchValue: container.find('.searchValue').val(),
				radius: container.find('.js-radius').val(),
				cache: thisInstance.getCacheParamsToRequest(),
			};
			params = $.extend(thisInstance.selectedParams, params);
			AppConnector.request(params).done(function (response) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				thisInstance.setMarkersByResponse(response);
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
		container.find('.searchBtn').on('click', function (e) {
			var progressIndicatorElement = jQuery.progressIndicator({
				'position': container,
				'blockInfo': {
					'enabled': true
				}
			});
			var params = {
				module: 'OpenStreetMap',
				action: 'GetMarkers',
				srcModule: app.getModuleName(),
				searchValue: container.find('.searchValue').val(),
				cache: thisInstance.getCacheParamsToRequest(),
			};
			var radiusValue = container.find('.js-radius').val();
			if (radiusValue !== '' && parseInt(radiusValue)) {
				params['radius'] = radiusValue;
			}
			params = $.extend(thisInstance.selectedParams, params);
			AppConnector.request(params).done(function (response) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				thisInstance.setMarkersByResponse(response);
			});
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
			description = description.replace(/\<br\>/gi, ", ");
			startElement.val(description);
			startElement.data('lat', coordinates.data('lat'));
			startElement.data('lon', coordinates.data('lon'));
			var marker = L.marker([coordinates.data('lat'), coordinates.data('lon')], {
				icon: L.AwesomeMarkers.icon({
					icon: 'truck',
					markerColor: 'green',
					prefix: 'fa',
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
			description = description.replace(/\<br\>/gi, ", ");
			endElement.val(description);
			endElement.data('lat', coordinates.data('lat'));
			endElement.data('lon', coordinates.data('lon'));
			var marker = L.marker([coordinates.data('lat'), coordinates.data('lon')], {
				icon: L.AwesomeMarkers.icon({
					icon: 'flag-checkered',
					markerColor: 'red',
					prefix: 'fa',
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
			description = description.replace(/\<br\>/gi, ", ");
			if (typeof thisInstance.indirectPointLayer[description] !== "undefined") {
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
					prefix: 'fa',
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
				'position': container,
				'blockInfo': {
					'enabled': true
				}
			});
			var params = {
				module: 'OpenStreetMap',
				action: 'GetMarkers',
				srcModule: app.getModuleName(),
				radius: container.find('.radius').val(),
				lat: coordinates.data('lat'),
				lon: coordinates.data('lon'),
				cache: thisInstance.getCacheParamsToRequest(),
			};
			params = $.extend(thisInstance.selectedParams, params);
			AppConnector.request(params).done(function (response) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				thisInstance.setMarkersByResponse(response);
			});
		});
		container.find('.calculateTrack').on('click', function () {
			var indirectLon = [];
			var indirectLat = [];
			container.find('.indirectContainer:not(.d-none) input.indirect').each(function () {
				var currentTarget = $(this);
				indirectLat.push(currentTarget.data('lat'));
				indirectLon.push(currentTarget.data('lon'));
			});
			var endElement = container.find('.end');
			var startElement = container.find('.start');
			var progressIndicatorElement = jQuery.progressIndicator({
				'position': container,
				'blockInfo': {
					'enabled': true
				}
			});
			var params = {
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
			};
			AppConnector.request(params).done(function (response) {
				progressIndicatorElement.progressIndicator({mode: 'hide'});
				if (thisInstance.routeLayer) {
					map.removeLayer(thisInstance.routeLayer);
				}
				var route = L.geoJson(response.result.geoJson);
				thisInstance.routeLayer = L.featureGroup([route]);
				map.addLayer(thisInstance.routeLayer);
				container.find('.descriptionContainer').removeClass('d-none');
				container.find('.descriptionContent .instruction').html(response.result.properties.description);
				container.find('.descriptionContent .distance').html(App.Fields.Double.formatToDisplay(response.result.properties.distance));
				container.find('.descriptionContent .travelTime').html(App.Fields.Double.formatToDisplay(response.result.properties.traveltime / 60));
			});
		});
		container.on('click', '.setView', function (e) {
			var currentTarget = $(e.currentTarget);
			var inputInstance = currentTarget.closest('.input-group').find('.end,.start,.indirect');
			var lat = inputInstance.data('lat');
			var lon = inputInstance.data('lon');
			if (!(typeof lat === "undefined" && typeof lon === "undefined")) {
				map.setView(new L.LatLng(lat, lon), 14);
			}
		});
		this.registerSearchCompany();
	},
	registerModalView: function (container) {
		var thisInstance = this;
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': container,
			'blockInfo': {
				'enabled': true
			}
		});
		var heightMap = $('body').height();
		this.container = container;
		var startCoordinate = [0, 0];
		var startZoom = 2;
		$('#mapid').css({
			height: heightMap - 200
		});
		this.registerMap(startCoordinate, startZoom);
		var params = {
			module: 'OpenStreetMap',
			action: 'GetMarkers',
			srcModule: app.getModuleName(),
		};
		params = $.extend(this.selectedParams, params);
		thisInstance.registerBasicModal();
		AppConnector.request(params).done(function (response) {
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			thisInstance.setMarkersByResponse(response);

		});
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
				$map.height($('.mainBody').height() - ($('.detailViewTitle').height() + $('.detailViewContainer .related').height() + 25));
			} else {
				$map.height(1000);
			}
		} else {
			if ($('.bodyContents').height() < 1000) {
				$map.height($('.bodyContents').height() - ($('.detailViewTitle').height() + $('.detailViewContainer .related').height() + 25));
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
});

