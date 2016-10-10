/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class("OpenStreetMap_Map_Js", {}, {
	container: false,
	mapInstance: false,
	selectedParams: false,
	layerMarkers: false,
	markers: false,
	polygonLayer: false,
	routeLayer: false,
	recordsIds: false,
	cacheLayerMarkers: {},
	setSelectedParams: function (params) {
		this.selectedParams = params;
	},
	registerMap: function (startCoordinate, startZoom) {
		var myMap = L.map('mapid').setView(startCoordinate, startZoom);
		L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			maxZoom: 19,
			attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'

		}).addTo(myMap);
		this.mapInstance = myMap;
		return myMap;
	},
	setMarkersByResponse: function (response) {
		var thisInstance = this;
		var markerArray = [];
		var container = this.container;
		var map = this.mapInstance;
		
		if (typeof response.result.coordinates != 'undefined') {
			var coordinates = response.result.coordinates;
			var markers = L.markerClusterGroup({
				maxClusterRadius: 10
			});
			map.removeLayer(this.layerMarkers);
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
		map.removeLayer(this.polygonLayer);
		if (typeof response.result.coordinatesCeneter != 'undefined') {
			if (typeof response.result.coordinatesCeneter.error == 'undefined') {
				var radius = container.find('.radius').val();
				markerArray.push([response.result.coordinatesCeneter.lat, response.result.coordinatesCeneter.lon]);
				var popup = '<span class="description">' + container.find('.searchValue').val() + '</span><br><input type=hidden class="coordinates" data-lon="' + response.result.coordinatesCeneter.lon + '" data-lat="' + response.result.coordinatesCeneter.lat + '">';
				popup += '<button class="btn btn-success btn-xs startTrack marginRight10"><span class="fa fa-truck"></span></button>';
				popup += '<button class="btn btn-danger btn-xs endTrack"><span class="fa fa-flag-checkered"></span></button>';
				var marker = L.marker([response.result.coordinatesCeneter.lat, response.result.coordinatesCeneter.lon], {
					icon: L.AwesomeMarkers.icon({
						icon: 'search',
						markerColor: 'red',
						prefix: 'fa',
					})
				}).bindPopup(popup);
				markers.addLayer(marker);
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
					animation: 'show'
				};
				Vtiger_Helper_Js.showMessage(params);
			}
		}
		if (typeof response.result.cache != 'undefined') {
			var cache = response.result.cache;
			Object.keys(cache).forEach(function (key) {
				if (typeof thisInstance.cacheLayerMarkers[key] != 'undefined') {
					map.removeLayer(thisInstance.cacheLayerMarkers[key]);
				}
				var markersCache = L.markerClusterGroup({
					maxClusterRadius: 10
				});
				coordinates = cache[key];
				coordinates.forEach(function (e) {
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
				});
				map.addLayer(markersCache);
				thisInstance.cacheLayerMarkers[key] = markersCache;
			});
		}
		
		var footer = this.container.find('.modal-footer');
		if (typeof response.result.legend != 'undefined') {
			var html = '';
			var legend = response.result.legend;
			legend.forEach(function (e) {
				html += '<div class="pull-left"><span class="leegendIcon" style="background:' + e.color + '"></span> ' + e.value + '</div>'
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
			container.find('.calculateTrack').removeClass('hide');
		}
	},
	registerCacheEvents: function (container) {
		var thisInstance = this;
		container.find('.showRecordsFromCache').on('change', function(e){
			var currentTarget = $(e.currentTarget);
			var moduleName = currentTarget.data('module');
			if(currentTarget.is(':checked')){
				var params = {
					module: 'OpenStreetMap',
					action: 'GetMarkers',
					srcModule: app.getModuleName(),
					cache: [moduleName],
				};
				AppConnector.request(params).then(function(response){
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
			AppConnector.request(params).then(function (response) {
				var params = {
					title: app.vtranslate('JS_LBL_PERMISSION'),
					text: app.vtranslate('JS_NOTIFY_COPY_TEXT'),
					type: 'success',
					animation: 'show'
				};
				Vtiger_Helper_Js.showMessage(params);
				container.find('.countRecords' + app.getModuleName()).html(response.result);
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
			AppConnector.request(params).then(function (response) {
				var params = {
					title: app.vtranslate('JS_LBL_PERMISSION'),
					text: app.vtranslate('JS_SAVE_NOTIFY_OK'),
					type: 'success',
					animation: 'show'
				};
				Vtiger_Helper_Js.showMessage(params);
				container.find('.countRecords' + moduleName).html('');
			});
		});
	},
	getCacheParamsToRequest: function(){
		var container = this.container;
		var params = [];
		container.find('.showRecordsFromCache').each(function(){
			var currentObject = $(this);
			if(currentObject.is(':checked'))
				params.push(currentObject.data('module'));
		});
		return params;
	},
	registerBasicModal: function () {
		var thisInstance = this;
		var container = this.container;
		var map = thisInstance.mapInstance;
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
				radius: container.find('.radius').val(),
				cache: thisInstance.getCacheParamsToRequest(),
			};
			$.extend(params, thisInstance.selectedParams);
			AppConnector.request(params).then(function (response) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				thisInstance.setMarkersByResponse(response);
			});
		});
		container.find('.groupNeighbours').on('change', function (e) {
			var currentTarget = $(e.currentTarget);
			map.removeLayer(thisInstance.layerMarkers);
			var markers = thisInstance.markers;
			if (currentTarget.is(':checked')) {
				var layer = L.markerClusterGroup({
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
				var layer = L.featureGroup(markerArray);
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
				radius: container.find('.radius').val(),
				cache: thisInstance.getCacheParamsToRequest(),
			};
			$.extend(params, thisInstance.selectedParams);
			AppConnector.request(params).then(function (response) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				thisInstance.setMarkersByResponse(response);
			});
		});
		var startIconLayer = false;
		container.on('click', '.startTrack', function (e) {
			map.removeLayer(startIconLayer);
			var currentTarget = $(e.currentTarget);
			var containerPopup = currentTarget.closest('.leaflet-popup-content');
			var description = containerPopup.find('.description').html();
			var startElement = container.find('.start');
			var coordinates = containerPopup.find('.coordinates');
			var description = description.replace(/\<br\>/gi, ", ");
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
		var endIconLayer = false
		container.on('click', '.endTrack', function (e) {
			map.removeLayer(endIconLayer);
			var currentTarget = $(e.currentTarget);
			var containerPopup = currentTarget.closest('.leaflet-popup-content');
			var description = containerPopup.find('.description').html();
			var endElement = container.find('.end');
			var coordinates = containerPopup.find('.coordinates');
			var description = description.replace(/\<br\>/gi, ", ");
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
		container.on('click', '.searchInRadius', function (e) {
			map.removeLayer(endIconLayer);
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
			$.extend(params, thisInstance.selectedParams);
			AppConnector.request(params).then(function (response) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				thisInstance.setMarkersByResponse(response);
			});
		});
		container.find('.calculateTrack').on('click', function () {
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
					tlon: endElement.data('lon'),
					tlat: endElement.data('lat')
				},
				dataType: 'html'
			};
			AppConnector.request(params).then(function (response) {
				progressIndicatorElement.progressIndicator({mode: 'hide'});
				map.removeLayer(thisInstance.routeLayer);
				var response = JSON.parse(response);
				var route = L.geoJson(response);
				thisInstance.routeLayer = L.featureGroup([route]);
				map.addLayer(thisInstance.routeLayer);
				container.find('.descriptionContainer').removeClass('hide');
				container.find('.descriptionContent .instruction').html(response.properties.description);
				container.find('.descriptionContent .distance').html(app.parseNumberToShow(response.properties.distance));
				container.find('.descriptionContent .travelTime').html(app.parseNumberToShow(response.properties.traveltime / 60));
			});
		});
		container.find('.setView').on('click', function (e) {
			var currentTarget = $(e.currentTarget);
			var inputInstance = currentTarget.closest('.input-group').find('.end,.start');
			var lat = inputInstance.data('lat');
			var lon = inputInstance.data('lon');
			if (!(typeof lat == 'undefined' && typeof lon == 'undefined')) {
				map.setView(new L.LatLng(lat, lon), 14);
			}
		});

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
		$.extend(params, this.selectedParams);
		AppConnector.request(params).then(function (response) {
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			thisInstance.setMarkersByResponse(response);
			thisInstance.registerBasicModal();
		});
	},
	registerDetailView: function (container) {
		this.container = container;
		var coordinates = container.find('#coordinates').val();
		coordinates = JSON.parse(coordinates);
		var startCoordinate = [0, 0];
		var startZoom = 2;
		if (coordinates.length) {
			startCoordinate = coordinates[0];
			startZoom = 6;
		}
		var postionTop = $('#mapid').position();
		var positionBottom = $('.footerContainer ').position();
		$('#mapid').css({
			height: positionBottom.top - postionTop.top - 281
		});
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

