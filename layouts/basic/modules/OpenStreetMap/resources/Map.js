/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class("OpenStreetMap_Map_Js", {}, {
	container: false,
	mapInstance: false,
	selectedParams: false,
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
	registerModalView: function (container) {
		var progressIndicatorElement = jQuery.progressIndicator({
			'position' : container,
			'blockInfo' : {
				'enabled' : true
			}
		});
		this.container = container;
		var startCoordinate = [0, 0];
		var startZoom = 2;
		$('#mapid').css({
			height: 500
		});
		var myMap = this.registerMap(startCoordinate, startZoom);
		var params = {
			module: 'OpenStreetMap',
			action: 'GetMarkers',
			srcModule: app.getModuleName(),
		};
		$.extend(params, this.selectedParams);
		var markerArray = [];
		AppConnector.request(params).then(function (response) {
			progressIndicatorElement.progressIndicator({'mode': 'hide'})
			var coordinates = response.result;
			coordinates.forEach(function (e) {
				markerArray.push([e[0], e[1]]);
				var marker = L.marker([e[0], e[1]], {
					icon: L.divIcon({
						className: 'fa fa-map-marker fa-3x',
					})
				}).addTo(myMap).bindPopup(e[2]);
				marker.valueOf()._icon.style.color = e['color']; //or any color
			});
			myMap.fitBounds(markerArray);
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
		coordinates.forEach(function (e) {
			L.marker([e[0], e[1]]).addTo(myMap).bindPopup(e[2]);
		});
	}
});

