/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class("OpenStreetMap_Map_Js", {}, {
	container: false,
	mapInstance: false,
	selectedParams: false,
	layerMarkers: false,
	markers: false,
	polygonLayer: false,
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
		var markerArray = [];
		var coordinates = response.result.coordinates;
		var container = this.container;
		var map = this.mapInstance;
		var markers = L.markerClusterGroup({
			maxClusterRadius: 10
		});
		map.removeLayer(this.layerMarkers);
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
		});
		map.removeLayer(this.polygonLayer);
		if (typeof response.result.coordinatesCeneter != 'undefined') {
			if (typeof response.result.coordinatesCeneter.error == 'undefined') {
				var radius = container.find('.radius').val();
				markerArray.push([response.result.coordinatesCeneter.lat, response.result.coordinatesCeneter.lon]);
				var marker = L.marker([response.result.coordinatesCeneter.lat, response.result.coordinatesCeneter.lon], {
					icon: L.AwesomeMarkers.icon({
						icon: 'search',
						markerColor: 'red',
						prefix: 'fa',
					})
				});
				markers.addLayer(marker);
				if($.isNumeric(radius)){
					radius = parseInt(radius) * 1000;
					var circle = L.circle([response.result.coordinatesCeneter.lat, response.result.coordinatesCeneter.lon], radius, {
						color: 'red',
						fillColor: '#f03',
						fillOpacity: 0.3
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
		this.markers = coordinates;
		this.layerMarkers = markers;
		map.addLayer(markers);
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
	registerBasicModal: function () {
		var thisInstance = this;
		var container = this.container;
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
				radius: container.find('.radius').val()
			};
			$.extend(params, thisInstance.selectedParams);
			AppConnector.request(params).then(function (response) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				thisInstance.setMarkersByResponse(response);
			});
		});
		container.find('.groupNeighbours').on('change', function (e) {
			var currentTarget = $(e.currentTarget);
			var map = thisInstance.mapInstance;
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
				radius: container.find('.radius').val()
			};
			$.extend(params, thisInstance.selectedParams);
			AppConnector.request(params).then(function (response) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				thisInstance.setMarkersByResponse(response);
			});
		});
	},
	registerModalView: function (container) {
		var thisInstance = this;
		app.showBtnSwitch(container.find('.switchBtn'));
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': container,
			'blockInfo': {
				'enabled': true
			}
		});
		this.container = container;
		var startCoordinate = [0, 0];
		var startZoom = 2;
		$('#mapid').css({
			height: 500
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

