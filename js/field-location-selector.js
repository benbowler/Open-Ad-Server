/*===============================================fields.locations.js==================================================================*/

if (typeof Sppc == 'undefined') {
	var Sppc = {};
}

Sppc.LocationSelectorDialog$Class = {
	_dialog: null,
	_tree: null,
	field: null,
	
	constructor: function(config){
		jQuery.extend(true, this, Sppc.LocationSelectorDialog$Class);

		this.field = config.field;
		
		var ar={
				autoOpen: false,
				buttons: {},
				dialogClass: 'sppc-selector-dialog',
				resizable: false,
				width: 470,
				position: ['center', 'center']
			};
		
		ar.buttons[this.field.getLL('close')] =  function(){jQuery(this).dialog('close'); $(document).unbind('.dialog-overlay'); };
		
		this._dialog = jQuery('<div></div>')
			.html('<h1 class="p0">' + this.field.getLL('selectLocationsDialogTitle') + '</h1><div class="mt10 mb10"><ul id="locations_tree"></ul></div><div class="mt10 clearBoth"></div>')
			.dialog(ar);
		
		var self = this;
		
		this._tree = jQuery('#locations_tree').tree({
			data: {
				type: 'json',
				async: true,
				opts: {
					url: site_url + 'common/locations/get_tree'
				}
			},
			ui: {
				dots: false,
				theme_path: site_url + 'css/jquery-tree/themes/default/style.css'
			},
			types: {
				'default': {
					clickable: false,
					renameable: false,
					deletable: false,
					creatable: false,
					draggable: false
				}
			},
			callback: {
				
				onparse: function(html, tree) {
					var locations = jQuery(html);
					
					var dialog = self;	
					var field = dialog.field;
					
					for (var i = 0; i < locations.length; i++) {
						var continent = jQuery(locations[i]);
						var continentId = continent.attr('id').split('_')[1];
						
						var addAllBtn = jQuery(document.createElement('span'))
							.text('Add All')
							.addClass('action-link')
							.bind('click', {'dialog': dialog}, dialog.onAddContinentBtnClick);
					
						var removeAllBtn = jQuery(document.createElement('span'))
							.text('Remove All')
							.addClass('action-link')
							.bind('click', {'dialog': dialog}, dialog.onRemoveContinentBtnClick);
						
						continent.prepend(addAllBtn);
						continent.prepend(removeAllBtn);
						
						var countries = jQuery('li', continent);
						for (var j = 0; j < countries.length; j++) {
							var country = jQuery(countries[j]);
							var countryId = country.attr('id').split('_')[1];
							
							var addRemoveBtn = jQuery(document.createElement('span'))
								.bind('click', {'dialog': dialog}, dialog.onCountryBtnClicks);
							
							if (field.isLocationSelected(countryId)) {
								addRemoveBtn.text(field.getLL('remove')).addClass('remove-link');
							} else {
								addRemoveBtn.text(field.getLL('add')).addClass('add-link');
							}
							
							country.prepend(addRemoveBtn);
						}
					}
					
					return locations;
				}
			}
		});
	},
	
	open: function() {
		this._dialog.dialog('open');
	},
	
	close: function() {
		this._dialog.dialog('close');
	},
	onCountryBtnClicks: function(e) {
		var dialog = e.data.dialog; 
		var field = dialog.field;
		
		var country = jQuery(this).parent();
		var countryId = country.attr('id').split('_')[1];
		
		if (field.isLocationSelected(countryId)) {
			field.removeLocation(countryId);
			country.removeClass('selected');
		} else {
			var countryName = dialog.trim(jQuery('> a:last', country).text());
			var result = field.addLocation(countryId, countryName);
			
			if (result) {
				country.addClass('selected');
				jQuery(this).text(field.getLL('remove'))
					.removeClass('add-link')
					.addClass('remove-link');
			}
		}
	},
	onRemoveContinentBtnClick: function(e) {
		var field = e.data.dialog.field;
		var continent = jQuery(this).parent();
		var countries = jQuery('li', continent);
		
		for(var i = 0; i < countries.length; i++) {
			var countryId = jQuery(countries[i]).attr('id').split('_')[1];
			field.removeLocation(countryId);
		}
	},
	onAddContinentBtnClick: function(e) {
		var dialog = e.data.dialog; 
		var field = dialog.field;
		var continent = jQuery(this).parent();
		
		var selectedCountries = new Array();
		
		var countries = jQuery('li', continent);
		for(var i = 0; i < countries.length; i++) {
			var country = jQuery(countries[i]);
			var countryId = country.attr('id').split('_')[1];
			var countryName = dialog.trim(jQuery('> a:last', country).text());
			
			selectedCountries.push({'id': countryId, 'name': countryName});
		}
		
		field.addLocations(selectedCountries);
	},
	trim: function(str) {
		return str.replace(/^\s+|\s+$/g,"");
	}
};
Sppc.LocationSelectorDialog = Sppc.LocationSelectorDialog$Class.constructor;

Sppc.LocationSelectorField$Class = {
	selectedLocations: new Array(),
	formField: null,
	opendDialogBtn: null,
	containerEl: null,
	dialog: null,
	maxSelectedLocations: null,
	totalCountries: 246,
	LL: {
		add: 'Add',
		remove: 'Remove',
		selectLocationsDialogTitle: 'Select Locations',
		close: 'Close',
		cantAddMore: 'You can not add more locations',
		allLocations: 'All Countries'
	},
	_allLocationsSelected: false,
	
	constructor: function(options){
		jQuery.extend(true, this, Sppc.LocationSelectorField$Class, options);
		
		this.formField = jQuery('#'+this.formField);
		this.opendDialogBtn = jQuery('#' + this.openDialogBtn);
		this.containerEl = jQuery('#'+this.containerEl);
		
		this.opendDialogBtn.bind('click', {field: this}, function(e){e.data.field.openDialog();});
		
		this.renderSelectedLocations();
		
		if (this.selectedLocations.length >= this.totalCountries) {
			this._allLocationsSelected = true;
			this.showAllLocations();
		}

	},

	renderSelectedLocations: function() {
		var allLocationsEl = jQuery(document.createElement('li'))
			.attr('id', 'selected_location_all').addClass('hide');
		
		var allLocationsTitle = jQuery(document.createElement('a'))
			.text(this.getLL('allLocations'));
		
		
		var allLocationsRemoveBtn = jQuery(document.createElement('a'))
			.text(this.getLL('remove'))
			.addClass('floatr')
			.addClass('remove-link')
			.bind('click', {'field': this}, this.onRemoveAllBtnClick);
		
		allLocationsEl.append(allLocationsRemoveBtn);
		allLocationsEl.append(allLocationsTitle);
		
		this.containerEl.append(allLocationsEl);
		
		for(var i = 0; i < this.selectedLocations.length; i++) {
			var location = this.selectedLocations[i];
			this.renderSelectedLocation(location.id, location.name);
		}
	},
	
	renderSelectedLocation: function(locationId, locationTitle) {
		var locationEl = jQuery(document.createElement('li'))
			.attr('id', 'selected_location_' + locationId);
		
		var locationTitle = jQuery(document.createElement('a'))
			.text(locationTitle);
		
		var locationRemoveBtn = jQuery(document.createElement('a'))
			.text(this.getLL('remove'))
			.addClass('floatr')
			.addClass('remove-link')
			.bind('click', {field: this}, this.onRemoveBtnClick);
		
		locationEl.append(locationRemoveBtn);
		locationEl.append(locationTitle);
		
		this.containerEl.append(locationEl);
	},
	
	addLocation: function(locationId, locationTitle) {
		this.selectedLocations.push({'id': locationId, 'name': locationTitle});
		
		var locations = new Array();
		for(var i = 0; i < this.selectedLocations.length; i++) {
			locations.push(this.selectedLocations[i].id);
		}
		
		this.formField.attr('value', locations.join(','));
		jQuery('p.errorP', this.containerEl.parent()).remove();
		
		this.renderSelectedLocation(locationId, locationTitle);
		
		if (this.selectedLocations.length >= this.totalCountries) {
			if (!this._allLocationsSelected) {
				this._allLocationsSelected = true;
				this.showAllLocations();
			}
		}
		
		return true;
	},
	
	addLocations: function(locations) {
		for(var i = 0; i < locations.length; i++) {
			var location = locations[i];
			if (!this.isLocationSelected(location.id)) {
				this.selectedLocations.push(location);
				this.renderSelectedLocation(location.id, location.name);
				
				jQuery('#country_'+location.id).addClass('selected');
				jQuery('#country_'+location.id+' > span:first')
					.text(this.getLL('remove'))
					.removeClass('add-link')
					.addClass('remove-link');
			}
		}
		
		if (this.selectedLocations.length >= this.totalCountries) {
			if (!this._allLocationsSelected) {
				this._allLocationsSelected = true;
				this.showAllLocations();
			}
		}
		
		var locations = new Array();
		for(var i = 0; i < this.selectedLocations.length; i++) {
			locations.push(this.selectedLocations[i].id);
		}
		
		this.formField.attr('value', locations.join(','));
		jQuery('p.errorP', this.containerEl.parent()).remove();
	},
	
	removeLocation: function(locationId) {
		var selectedLocations = new Array();
		var locations = new Array();
		
		for(var i = 0; i < this.selectedLocations.length; i++) {
			if (this.selectedLocations[i].id != locationId) {
				selectedLocations.push(this.selectedLocations[i]);
				locations.push(this.selectedLocations[i].id);
			}
		}
		this.selectedLocations = selectedLocations;
		this.formField.attr('value', locations.join(','));
		
		jQuery('#selected_location_'+locationId).remove();
		jQuery('#country_'+locationId).removeClass('selected');
		jQuery('#country_'+locationId+' > span:first')
			.text(this.getLL('add'))
			.removeClass('remove-link')
			.addClass('add-link');
		
		if (this.selectedLocations.length < this.totalCountries) {
			if (this._allLocationsSelected) {
				this._allLocationsSelected = false;
				this.hideAllLocations();
			}
		}
	},
	removeAllLocations: function() {
		for(var i = 0; i < this.selectedLocations.length; i++) {
			var location = this.selectedLocations[i];
			
			jQuery('#selected_location_'+location.id).remove();
			jQuery('#country_'+location.id).removeClass('selected');
			jQuery('#country_'+location.id+' > span:first')
				.text(this.getLL('add'))
				.removeClass('remove-link')
				.addClass('add-link');
		}
		
		this.selectedLocations = new Array();
		this.formField.attr('value', '');
		
		if (this._allLocationsSelected) {
			this._allLocationsSelected = false;
			this.hideAllLocations();
		}
	},
	isLocationSelected: function(locationId) {
		for(var i = 0; i < this.selectedLocations.length; i++){
			if (this.selectedLocations[i].id == locationId) {
				return true;
			}
		}
		return false;
	},
	showAllLocations: function() {
		for(var i = 0; i < this.selectedLocations.length; i++) {
			var location = this.selectedLocations[i];
			jQuery('#selected_location_'+location.id).hide();
		}
		jQuery('#selected_location_all').show();
	},
	hideAllLocations: function() {
		for(var i = 0; i < this.selectedLocations.length; i++) {
			var location = this.selectedLocations[i];
			jQuery('#selected_location_'+location.id).show();
		}
		
		jQuery('#selected_location_all').hide();
	},
	openDialog: function(e) {
		if (!this.dialog) {
			this.dialog = new Sppc.LocationSelectorDialog({field: this});
		}
		
		this.dialog.open();
	},
	getLL: function(label) {
		if (this.LL[label]) {
			return this.LL[label];
		} else {
			return label;
		}
	},
	onRemoveBtnClick: function(e) {
		var location = jQuery(this).parent();
		var locationId = location.attr('id').split('_')[2];
		e.data.field.removeLocation(locationId);
	},
	onRemoveAllBtnClick: function(e) {
		e.data.field.removeAllLocations();
	}
};
Sppc.LocationSelectorField = Sppc.LocationSelectorField$Class.constructor;