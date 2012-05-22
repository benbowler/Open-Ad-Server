var Sppc = {};

Sppc.TemplateEditor$Class = {
	templateId: null,
	controller: null,
	currentFile: null,
	currentSheme: null,
	currentTemplateType: null,
	isFileModified: false,
	isShemeModified: false,
	previewLink: null,
	
	previousSheme: {
		id: null,
		title: null,
		content: null,
		modified: false
	},
	rte: null,
	
	constructor: function() {
		jQuery.extend(true, this, Sppc.TemplateEditor$Class);
	},
	
	initialize: function(config) {
		this.templateId = config.templateId;
		this.controller = config.controller;
		this.currentFile = config.file;
		this.currentSheme = config.sheme;
		this.currentTemplateType = config.templateType;
		
		jQuery(this.previewLink).trigger('click');
		
		jQuery('#template_type').change(function(){
			Sppc.TemplateEditor.switchTemplateType(jQuery(this).val());
		});
		
		jQuery('#template_files').change(function(){
			if (Sppc.TemplateEditor.isFileModified) {
				if (!confirm(Sppc.LL.fileModifiedMsg)) {
					jQuery('#template_files').val(Sppc.TemplateEditor.currentFile);
					return false;
				}
			}
			Sppc.TemplateEditor.loadTemplateFile(jQuery(this).val());
		});
		
		if (this.currentTemplateType == 'one_page') {
			jQuery('#template_files').attr('disabled', true);
		}
		
		jQuery('#template_shemes').change(function(){
			Sppc.TemplateEditor.loadSheme(jQuery(this).val());
		});
		
		jQuery('#file_head').change(function(){Sppc.TemplateEditor.isFileModified = true;});
		jQuery('#file_body').change(function(){Sppc.TemplateEditor.isFileModified = true;});
		
		jQuery('#sheme_content').change(function(){Sppc.TemplateEditor.isShemeModified = true;});
		
		this._initializeRTE();
	},
	switchTemplateType: function(templateType) {
		if (this.currentTemplateType != templateType) {
			jQuery.post(site_url + this.controller + 'change_template_type', 
				{'id': this.templateId, 'type': templateType},
				function(response) {
					if (!checkAjaxLogin(response)) {
						try {
							response = JSON.parse(response);
						} catch (e) {
							return ;
						}
						
						if (response.status == 'ok') {
							Sppc.TemplateEditor.currentTemplateType = response.type;
							
							$templateFilesOptions = '';
							for(var i = 0; i < response.files.length; i++) {
								var file = response.files[i];
								$templateFilesOptions += '<option value="'+file.file+'">'+file.title+'</option>';
							}
							jQuery('#template_files').html($templateFilesOptions);
							
							if (response.type == 'one_page') {
								jQuery('#template_files').attr('disabled', true);
							} else {
								jQuery('#template_files').attr('disabled', false);
							}
							
							jQuery('#template_files').val(response.files[0].file);
							Sppc.TemplateEditor.loadTemplateFile(response.files[0].file);
						} else {
							alert(response.error);
						}
					}
				}
			);
		}
	},
	loadTemplateFile: function(file) {
		jQuery('#templateFileLoader').show();
		jQuery.post(site_url + this.controller + 'get_template_file',
			{'id': this.templateId, 'file_type': file},
			function(response) {
				jQuery('#templateFileLoader').hide();
				if (!checkAjaxLogin(response)) {
					try {
						response = JSON.parse(response);
					} catch (e) {
						return ;
					}
					
					if (response.status == 'ok') {
						Sppc.TemplateEditor.currentFile = response.type;
						Sppc.TemplateEditor.isFileModified = false;
						
						jQuery('#file_head').val(response.head);
						jQuery('#file_body').val(response.body);
						Sppc.TemplateEditor.rte.setData(response.body);
						Sppc.TemplateEditor.rte.resetDirty();
					} else {
						alert(response.error);
					}
				}
			}
		);
	},
	loadSheme: function(sheme) {
		jQuery('#shemeFileLoader').show();
		jQuery.post(site_url+this.controller+'get_color_sheme', {'templateId': this.templateId, 'id': sheme},
			function(response) {
				if (!checkAjaxLogin(response)) {
					jQuery('#shemeFileLoader').hide();
					try {
						response = JSON.parse(response);
						
						if (response.status == 'ok') {
							jQuery('#template_shemes').val(response.id);
							jQuery('#sheme_title').val(response.title);
							jQuery('#sheme_content').val(response.content);
							Sppc.TemplateEditor.currentSheme = response.id;
							Sppc.TemplateEditor.isShemeModified = false;
						} else {
							alert(response.error);
						}
					} catch (e) {
						
					}
				}
			}
		);
	},
	saveCurrentSheme: function() {
		if (this.isShemeModified == true) {
			jQuery('#shemeFileLoader').show();
			jQuery.post(site_url + this.controller + 'save_color_sheme',
				{
					'templateId': this.templateId,
					'id': this.currentSheme,
					'title': jQuery('#sheme_title').val(),
					'content': jQuery('#sheme_content').val()
				},
				function (response) {
					if (!checkAjaxLogin(response)) {
						try {
							jQuery('#shemeFileLoader').hide();
							jQuery('#template_shemes').attr('disabled', false);
							jQuery('#createColorShemeButton').attr('disabled', false);
							jQuery('#deleteColorShemeButton').attr('disabled', false);
							jQuery('#cancelShemeCreationButton').hide();
							jQuery('#reloadShemeButton').show();
							
							response = JSON.parse(response);
							
							if (response.status == 'ok') {
								var shemesCount = response.colorShemes.length;
								var shemesOptions = '';
								for(var i = 0; i < shemesCount; i++) {
									var curSheme = response.colorShemes[i];
									shemesOptions += '<option value="'+curSheme.id+'">'+curSheme.title+'</option>';
								}
								jQuery('#template_shemes').html(shemesOptions);
								jQuery('#template_shemes').val(response.id);
								
								Sppc.TemplateEditor.currentSheme = response.id;
								Sppc.TemplateEditor.isShemeModified = false;
							} else {
								alert(response.error);
							}
						} catch (e) {
							
						}
					}
				}
			);
		}
	},
	reloadCurrentSheme: function() {
		if (this.isShemeModified) {
			if (!confirm(Sppc.LL.shemeModifiedMsg)) {
				return ;
			}
			this.loadSheme(this.currentSheme);
		}
	},
	saveCurrentFile: function() {
		if (this.isFileModified == true) {
			jQuery.post(site_url + this.controller + 'save_template_file',
				{
					'id': this.templateId,
					'type': this.currentFile,
					'head': jQuery('#file_head').val(),
					'body': this.rte.getData()
				},
				function(response){
					if (!checkAjaxLogin(response)) {
						try {
							response = JSON.parse(response);
						} catch (e) {
							return ;
						}
						
						if (response.status == 'ok') {
							Sppc.TemplateEditor.isFileModified = false;
							Sppc.TemplateEditor.rte.resetDirty();
						} else {
							alert(response.error);
						}
					}
				}
			);
		}
	},
	reloadCurrentFile: function() {
		if (this.isFileModified) {
			if (confirm(Sppc.LL.fileReloadMsg)) {
				this.loadTemplateFile(this.currentFile);
			}
		}
	},
	previewCurrentFile: function() {
		var url = site_url + this.controller + 'preview_template_file/'+this.currentFile+'/'+this.templateId+'?placeValuesBeforeTB_=savedValues&TB_iframe=true&height=600&width=900';
		tb_show(Sppc.LL.templatePreview, url);
		
	},
	deleteCurentSheme: function() {
		if (confirm(Sppc.LL.shemeDeleteMsg)) {
			jQuery('#shemeFileLoader').show();
			jQuery.post(site_url+this.controller+'delete_color_sheme', 
				{'templateId': this.templateId, 'id': this.currentSheme},
				function(response) {
					if (!checkAjaxLogin(response)) {
						jQuery('#shemeFileLoader').hide();
						try {
							response = JSON.parse(response);
							
							if (response.status == 'ok') {
								var shemesCount = response.shemes.length;
								var responseOptions = '';
								for(var i = 0; i < shemesCount; i++) {
									var curSheme = response.shemes[i];
									responseOptions += '<option value="'+curSheme.id+'">'+curSheme.title+'</option>';
								}
								jQuery('#template_shemes').html(responseOptions);
								jQuery('#template_shemes').val(response.shemes[0].id);
								Sppc.TemplateEditor.loadSheme(response.shemes[0].id);
							} else {
								alert(response.error);
							}
						} catch (e) {
							
						}
					}
				}
			);
		}
	},
	createSheme: function() {
		if (this.isShemeModified) {
			if (!confirm(Sppc.LL.shemeModifiedMsg)) {
				return ;
			}
		}
		this.previousSheme.id = this.currentSheme;
		this.previousSheme.title = jQuery('#sheme_title').val();
		this.previousSheme.content = jQuery('#sheme_content').val();
		this.previousSheme.modified = this.isShemeModified;
		
		jQuery('#reloadShemeButton').hide();
		jQuery('#cancelShemeCreationButton').show();
		jQuery('#sheme_title').val('');
		jQuery('#sheme_content').val('');
		jQuery('#template_shemes').attr('disabled', true);
		jQuery('#createColorShemeButton').attr('disabled', true);
		jQuery('#deleteColorShemeButton').attr('disabled', true);
		this.currentSheme = 'new';
		this.isShemeModified = true;
	},
	cancelShemeCreation: function() {
		jQuery('#sheme_title').val(this.previousSheme.title);
		jQuery('#sheme_content').val(this.previousSheme.content);
		this.currentSheme = this.previousSheme.id;
		this.isShemeModified = this.previousSheme.modified;
		
		jQuery('#template_shemes').attr('disabled', false);
		jQuery('#createColorShemeButton').attr('disabled', false);
		jQuery('#deleteColorShemeButton').attr('disabled', false);
		
		jQuery('#cancelShemeCreationButton').hide();
		jQuery('#reloadShemeButton').show();
	},
	_initializeRTE: function() {
		this.rte = CKEDITOR.replace('teditor', {
			toolbar: [
					   ['Source'],
					   ['Cut','Copy','Paste','PasteText','PasteFromWord'],
					   ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
					   '/',
					   ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
					   ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
					   ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
					   ['Link','Unlink','Anchor'],
					   ['Image','Flash','Table','HorizontalRule','SpecialChar'],
					   '/',
					   ['Styles','Format','Font','FontSize'],
					   ['TextColor','BGColor'],
					   ['Maximize', 'ShowBlocks','-','About']
					] 
		});
		CKFinder.SetupCKEditor( this.rte, '../system/ckfinder/' );
		this.rte.on('key', function(){
			this.isFileModified = true;
		}, this);
	}
};
Sppc.TemplateEditor = new Sppc.TemplateEditor$Class.constructor();