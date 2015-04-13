// ==============================================================
// ==============================================================
// Javascript core functions
// --------------------------------------------------------------

var core = {
	
	// --------------------------------------------------
	// Initialization
	
	cfg: {
		
		app: {
			module: "", 
			section: "", 
			action: "", 
			images: ""
		}, 
		
		paths: {
			root: "", 
			images: ""
		}, 
		
		images: {}, 
		
		datepicker_options: {
			altFormat: "dd/mm/yy", 
			closeText: "Cerrar", 
			currentText: "Hoy", 
			dateFormat: "dd/mm/yy", 
			dayNames: [ "Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado" ], 
			dayNamesMin: [ "Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa" ], 
			dayNamesShort: [ "Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sab" ], 
			firstDay: 1, 
			monthNames: [ "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre" ], 
			monthNamesShort: [ "Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic" ], 
			nextText: "Siguiente", 
			prevText: "Anterior", 
			showAnim: ""
		}
		
	}, 
	
	// ========================================================
	// Init
	
	init: function() {
		
		// Resize page
		$(window).resize(function() {
			if ($(".ui-dialog-content").length > 0) {
				$(".ui-dialog-content").dialog("option", "position", { my: "center", at: "center", of: window });
			}
	  	core.locking.pageLoad.resize();
		});
		
		// Scroll page
		$(window).scroll(function() {
			if ($(".ui-dialog-content").length > 0) {
				$(".ui-dialog-content").dialog("option", "position", { my: "center", at: "center", of: window });
			}
			core.locking.pageLoad.resize();
		});
		
		// Form elements
		core.forms.init();
		
		// Init list filters
		core.lists.filters.init();
		
		// Images init
		core.widgets.images.zoomInit();
		
		// Back to top button
		$("div.totop button").click(function() {
	    aux.scroll("#container");
	  });
	  
	  // Stats
		core.stats.init();
		
	}, 
	
	// ========================================================
	// List Functions
	
	lists: {
		
		// Ordering functions
		
		order: function(new_order) {
			
			var options = {
				sorting: new_order
			};
			
			document.location.href = core.lists.url(options);
			
		}, 
		
		// Url functions
		
		url: function(options) {
			
			options = (typeof options === "undefined") ? {} : options;
			
			var module = (typeof options.module === "undefined") ? core.cfg.app.module : options.module, 
					section = (typeof options.section === "undefined") ? core.cfg.app.section : options.section, 
					page = (typeof options.page === "undefined") ? (typeof list_page_num === "undefined") ? 1 : list_page_num : options.page, 
					sorting = (typeof options.sorting === "undefined") ? "" : options.sorting, 
					query = (typeof search_query === "undefined") ? "" : search_query, 
					filter = (typeof options.filter === "undefined") ? "" : options.filter;
			
			var url = core.cfg.paths.root + module + "/";
			if (section != "") {
				url += section + "/";
			}
			url += "?page=" + page;
			if (query != "") {
				url += "&q=" + query;
			}
			if (sorting != "") {
				url += "&sorting=" + sorting;
			}
			if (filter != "") {
				url += "&filter=" + filter;
			}
			
			return url;
			
		}, 
		
		// Item functions
		
		items: {
			
			element: null, 
			item: null, 
			
			activate: function(element, item, value) {
				
				core.locking.pageLoad.lock();
				
				$.ajax({
					url: core.cfg.paths.root+"action/", 
					type: "POST", 
					dataType: "json", 
					accepts: "json", 
					data: {
						'action': "list-item-active", 
						'element': element, 
						'item': item, 
						'value': value
					}, 
					'success': function(data, textStatus, jqXHR) {
						if (data.result == "ok") {
							
							document.location.href = core.lists.url();
							
						} else {
							
							core.locking.pageLoad.unlock();
							core.error("<h4>Se produjo un error al realizar el cambio.</h4><p>"+data.error_msg+"</p>");
							
						}
					}, 
					'error': function(jqXHR, textStatus, errorThrown) {
						
						core.locking.pageLoad.unlock();
						core.errorUnknown();
						
					}
				});
				
			}, 
			
			remove: {
				
				title: "", 
				content: "", 
				
				ask: function(element, item, name) {
					
					core.lists.items.element = element;
					core.lists.items.item = item;
					
					core.dialogs.show({
						type: "confirm", 
						title: core.lists.items.remove.title, 
						content: aux.string.replace("**name**", name, core.lists.items.remove.content), 
						action: core.lists.items.remove.go
					});
					
				}, 
				
				go: function() {
					
					core.locking.dialog.lock("Borrando...");
					
					$.ajax({
						url: core.cfg.paths.root+"action/", 
						type: "POST", 
						dataType: "json", 
						accepts: "json", 
						data: {
							'action': "list-item-delete", 
							'element': core.lists.items.element, 
							'item': core.lists.items.item
						}, 
						'success': function(data, textStatus, jqXHR) {
							if (data.result == "ok") {
								
								document.location.href = core.lists.url();
								
							} else {
								
								core.locking.dialog.unlock();
								core.error("<h4>Se produjo un error al borrar el elemento.</h4><p>"+data.error_msg+"</p>");
								
							}
						}, 
						'error': function(jqXHR, textStatus, errorThrown) {
							
							core.locking.dialog.unlock();
							core.errorUnknown();
							
						}
					});
					
				}
				
			}, 
			
			order: function(element, item, mode, value) {
				
				core.locking.pageLoad.lock();
				
				value = typeof value !== "undefined" ? value : 0;
				
				$.ajax({
					url: core.cfg.paths.root+"action/", 
					type: "POST", 
					dataType: "json", 
					accepts: "json", 
					data: {
						'action': "list-item-order", 
						'element': element, 
						'item': item, 
						'mode': mode, 
						'value': value
					}, 
					'success': function(data, textStatus, jqXHR) {
						if (data.result == "ok") {
							
							document.location.href = core.lists.url();
							
						} else {
							
							core.locking.pageLoad.unlock();
							core.error("<h4>Se produjo un error al realizar el cambio.</h4><p>"+data.error_msg+"</p>");
							
						}
					}, 
					'error': function(jqXHR, textStatus, errorThrown) {
						
						core.locking.pageLoad.unlock();
						core.errorUnknown();
						
					}
				});
				
			}
			
		}, 
		
		// Filters functions
		
		filters: {
			
			init: function() {
				
				$("div.filters button.filters-add").unbind("click").click(function() {
					
					if ($("#accordion-filters").css("display") == "block") {
						
						$("#accordion-filters").fadeOut(150, function() {
							$("#accordion-filters").remove();
						});
						
					} else {
						
						var content = "<div id=\"accordion-filters\" class=\"popover left in filters-options\">" + 
													"<div class=\"arrow\"></div>" + 
													"<h3 class=\"popover-title\">Añadir un filtro</h3>" + 
													"<div class=\"popover-content\">" + 
													$("div.filters div.filters-menu").html() + 
													"</div></div>";
						
						$(this).parent().append(content);
						
						var left = $(this).offset().left - parseInt($("#accordion-filters").width()) - 4, 
								top = $(this).offset().top - 43;
						
						$("#accordion-filters").css({
							'display': "none", 
							'left': left+"px", 
							'top': top+"px"
						}).fadeIn(300, function() {
							core.widgets.accordion.init("#accordion-filters");
						});
						
					}
					
				});
				
			}, 
			
			add: function(field, value) {
				
				var options = {
					filter: "add|"+escape(field)+"|"+escape(value)
				};
				
				document.location.href = core.lists.url(options);
				
			}, 
			
			remove: function(field) {
				
				var options = {
					filter: "remove|"+escape(field)
				};
				
				document.location.href = core.lists.url(options);
				
			}
			
		}
		
	}, 
	
	// ========================================================
	// Widgets
	
	widgets: {
		
		// ......................................................
		// Images browser
		
		imagesBrowser: {
			
			options: {}, 
			locked: false, 
			dialog: 0, 
			
			open: function(options) {
				
				options = typeof options !== "undefined" ? options : {};
				options['gallery-show'] = typeof options['gallery-show'] !== "undefined" ? options['gallery-show'] : true;
				options['gallery-selected'] = typeof options['gallery-selected'] !== "undefined" ? options['gallery-selected'] : 0;
				options['gallery-editable'] = typeof options['gallery-editable'] !== "undefined" ? options['gallery-editable'] : true;
				options['image-selected'] = typeof options['image-selected'] !== "undefined" ? options['image-selected'] : 0;
				options['image-allow-edit'] = typeof options['image-allow-edit'] !== "undefined" ? options['image-allow-edit'] : true;
				options['image-allow-delete'] = typeof options['image-allow-delete'] !== "undefined" ? options['image-allow-delete'] : true;
				options['image-allow-add'] = typeof options['image-allow-add'] !== "undefined" ? options['image-allow-add'] : true;
				options['text-info'] = typeof options['text-info'] !== "undefined" ? options['text-info'] : "Seleccione la imagen deseada haciendo clic en ella y pulse en aceptar.";
				options['action'] = typeof options['action'] !== "undefined" ? options['action'] : function() { core.dialogs.close(core.widgets.imagesBrowser.dialog); };
				
				core.widgets.imagesBrowser.options = options;
				
				var html = "";
				
				html += "<div class=\"widget-images-browser\">";
				
				if (options['gallery-show']) {
					
					html += "<div class=\"galleries\"><h5>Galerías</h5><ul></ul>";
					if (options['gallery-editable']) {
						html += "<div class=\"action\"><button type=\"button\" class=\"btn btn-success btn-sm add\" title=\"Añadir nueva galería\"><i class=\"glyphicon glyphicon-plus\"></i> Nueva galería</button></div>";
					}
					html += "</div>";
					
				}
				
				html += 	"<div class=\"images\"><h5>Imágenes</h5><div class=\"text-info\">"+options['text-info']+"</div><ul></ul>";
				if (options['image-allow-add']) {
					html += 	"<div class=\"action\">";
					html += 		"<div class=\"btn btn-success fileinput-button\">" + 
												"<i class=\"glyphicon glyphicon-cloud-upload\"></i> " + 
												"<span>Añadir nueva imagen</span>" + 
												"<input name=\"f_img_upload\" type=\"file\" />" + 
											"</div>" + 
											"<div class=\"upload-progress\">" + 
												"<div class=\"bar\"><div class=\"indicator\" style=\"width: 0%;\"></div></div>" + 
												"<p>Subiendo imagen...</p>" + 
											"</div>";
					html += 	"</div>";
				}
				html += 	"</div>";
				
				html += "</div>";
				
				core.widgets.imagesBrowser.dialog = core.dialogs.show({
					type: "edit", 
					title: "Seleccionar imagen", 
					content: html, 
					width: "auto", 
					text_button1: "Aceptar", 
					onOpen: core.widgets.imagesBrowser.init, 
					action: core.widgets.imagesBrowser.select
				});
				
			}, 
			
			init: function() {
				
				if (core.widgets.imagesBrowser.options['gallery-show']) {
					
					// Load galleries
					core.widgets.imagesBrowser.gallery.load();
					
				}
				
			}, 
			
			select: function() {
				
				if (core.widgets.imagesBrowser.images.selected == 0) {
					
					core.error("No ha seleccionado ninguna imagen. Tiene que seleccionar alguna.");
					return false;
					
				}
				
				core.widgets.imagesBrowser.options['action'](core.widgets.imagesBrowser.images.list[core.widgets.imagesBrowser.images.selected]);
				core.dialogs.close(core.widgets.imagesBrowser.dialog);
				
			}, 
			
			// -----------------------------
			gallery: {
		 		
				list: {}, 
				selected: 0, 
				
				load: function() {
					
					$("div.widget-images-browser div.galleries ul")
								.html("")
								.css({
									'background': "#ffffff url("+core.cfg.paths.root+"img/anim-loader1.gif) center center no-repeat"
								});
					
					$("div.widget-images-browser div.galleries button.add").attr("disabled", "disabled");
					
					$.ajax({
						url: core.cfg.paths.root+"action/", 
						type: "POST", 
						dataType: "json", 
						accepts: "json", 
						data: {
							'action': "image-gallery-list"
						}, 
						'success': function(data, textStatus, jqXHR) {
							if (data.result == "ok") {
								
								core.widgets.imagesBrowser.gallery.list = data.list;
								core.widgets.imagesBrowser.gallery.init();
								
							} else {
								$("div.widget-images-browser div.galleries ul")
											.css({
												'background': "#ffffff"
											})
											.html("<li>Se ha producido un error al cargar las galerías.</li>");
							}
						}, 
						'error': function(jqXHR, textStatus, errorThrown) {
							$("div.widget-images-browser div.galleries ul")
										.css({
											'background': "#ffffff"
										})
										.html("<li>Se ha producido un error desconocido al cargar las galerías.</li>");
						}
					});
					
				}, 
				
				init: function() {
					
					var selected = 0;
					
					$("div.widget-images-browser div.galleries ul")
								.css({
									'background': "#ffffff"
								});
					
					core.widgets.imagesBrowser.gallery.refresh();
					
					$("div.widget-images-browser div.galleries button.add")
								.removeAttr("disabled")
								.unbind("click")
								.click(function() {
									core.widgets.imagesBrowser.gallery.edit.open(0);
								});
					
					if (core.widgets.imagesBrowser.gallery.selected == 0) {
						if (core.widgets.imagesBrowser.options['gallery-selected'] != 0
								&& $("div.widget-images-browser div.galleries ul li[data-id='"+core.widgets.imagesBrowser.options['gallery-selected']+"']").length > 0) {
							selected = core.widgets.imagesBrowser.options['gallery-selected'];
						}
						
						if (selected == 0) {
							for (var first in core.widgets.imagesBrowser.gallery.list) break;
							selected = first;
						}
					} else {
						selected = core.widgets.imagesBrowser.gallery.selected;
					}
					
					core.widgets.imagesBrowser.gallery.select(selected);
					
				}, 
				
				select: function(id) {
					
					if (core.widgets.imagesBrowser.locked) {
						return false;
					}
					core.widgets.imagesBrowser.locked = true;
					
					core.widgets.imagesBrowser.gallery.selected = id;
					
					$("div.widget-images-browser div.galleries ul li")
								.removeClass("active");
					
					$("div.widget-images-browser div.galleries ul li[data-id='"+id+"']")
								.addClass("active");
					
					core.widgets.imagesBrowser.images.load(id);
					
				}, 
				
				refresh: function() {
					
					var html = "";
					
					if (core.widgets.imagesBrowser.gallery.list.length == 0) {
						html += "<li>No hay galerías.</li>";
					} else {
						
						for (var id in core.widgets.imagesBrowser.gallery.list) {
							html += "<li data-id=\""+id+"\">";
							html += 	"<button type=\"button\" " + 
												"class=\"select\" " + 
												"onclick=\"core.widgets.imagesBrowser.gallery.select('"+id+"');\" " + 
												"title=\"Cambiar a esta galería\">" + 
													core.widgets.imagesBrowser.gallery.list[id] + 
												"</button>";
							if (core.widgets.imagesBrowser.options['gallery-editable']) {
								html += "<button type=\"button\" " + 
												"class=\"btn btn-success btn-xs edit\" " + 
												"data-id=\""+id+"\" " + 
												"onclick=\"core.widgets.imagesBrowser.gallery.edit.open('"+id+"');\" " + 
												"title=\"Editar el nombre de esta galería\"><i class=\"glyphicon glyphicon-edit\"></i></button>";
							}
							if (core.widgets.imagesBrowser.options['gallery-editable']) {
								html += "<button type=\"button\" " + 
												"class=\"btn btn-danger btn-xs delete\" " + 
												"data-id=\""+id+"\" " + 
												"onclick=\"core.widgets.imagesBrowser.gallery.remove.ask('"+id+"');\" " + 
												"title=\"Borrar esta galería\"><i class=\"glyphicon glyphicon-remove\"></i></button>";
							}
							html += "</li>";
						}
						
					}
					
					$("div.widget-images-browser div.galleries ul").html(html);
					
				}, 
				
				edit: {
					
					gallery: 0, 
					dialog: 0, 
					
					open: function(id) {
						
						core.widgets.imagesBrowser.gallery.edit.gallery = id;
						
						var html = "", 
								name = id != 0 ? $("div.widget-images-browser div.galleries ul li[data-id='"+id+"'] button.select").text() : "";
						
						html += "<div class=\"overlay-form form-horizontal\" role=\"form\">" + 
										"<div class=\"form-group\"><label class=\"col-sm-4 control-label\">Nombre</label>" + 
										"<div class=\"col-sm-8\"><input type=\"text\" name=\"name\" class=\"form-control\" value=\""+name+"\" /></div></div>" + 
										"<div class=\"alert\"></div>" + 
										"</div>";
						
						core.widgets.imagesBrowser.gallery.edit.dialog = core.dialogs.show({
							type: "edit", 
							title: id != 0 ? "Editar galería" : "Nueva galería", 
							text_button1: "Guardar", 
							content: html, 
							action: core.widgets.imagesBrowser.gallery.edit.save
						});
						
					}, 
					
					save: function() {
						
						$(".overlay-form div.form-group").removeClass("has-error");
						
						var name = $(".overlay-form input[name='name']").val();
						
						if (name == "" || name.length < 3) {
							$(".overlay-form input[name='name']").parents("div.form-group").addClass("has-error");
							core.widgets.imagesBrowser.gallery.edit.error("Debe indicar un nombre para la galería de al menos 3 caracteres.");
							return false;
						}
						
						core.locking.dialog.lock("Guardando...", core.widgets.imagesBrowser.gallery.edit.dialog);
						
						$.ajax({
							url: core.cfg.paths.root+"action/", 
							type: "POST", 
							dataType: "json", 
							accepts: "json", 
							data: {
								'action': "form-save", 
								'element': "images_galleries", 
								'mode': core.widgets.imagesBrowser.gallery.edit.gallery != 0 ? "edit" : "new", 
								'item': core.widgets.imagesBrowser.gallery.edit.gallery, 
								'data': {
									'name': name
								}
							}, 
							'success': function(data, textStatus, jqXHR) {
								if (data.result == "ok") {
									
									core.widgets.imagesBrowser.gallery.edit.ok(data.item);
									
								} else {
									core.widgets.imagesBrowser.gallery.edit.error("<strong>Se ha producido un error:</strong> "+data.error_msg);
								}
							}, 
							'error': function(jqXHR, textStatus, errorThrown) {
								core.widgets.imagesBrowser.gallery.edit.error("<strong>Se ha producido un error desconocido.</strong> Por favor, vuelva a intentarlo pasados unos minutos.");
							}
						});
						
					}, 
					
					error: function(text) {
						
						$(".overlay-form div.alert")
										.removeClass("alert-succes alert-warning alert-danger")
										.addClass("alert-danger")
										.html(text)
										.hide()
										.slideDown();
						core.locking.dialog.unlock(core.widgets.imagesBrowser.gallery.edit.dialog);
						
					}, 
					
					ok: function(id) {
						
						if (core.widgets.imagesBrowser.gallery.edit.gallery == 0) {
							core.widgets.imagesBrowser.gallery.selected = id;
						}
						core.widgets.imagesBrowser.gallery.load();
						core.dialogs.close(core.widgets.imagesBrowser.gallery.edit.dialog);
						
					}
					
				}, 
				
				remove: {
					
					gallery: 0, 
					dialog: 0, 
					
					ask: function(id) {
						
						core.widgets.imagesBrowser.gallery.remove.gallery = id;
						
						var name = $("div.widget-images-browser div.galleries ul li[data-id='"+id+"'] button.select").text();
						
						var html = "<h4>¿Está seguro de querer borrar la galería '<strong>"+name+"</strong>'?</h4>" + 
												"<p><strong>Advertencia:</strong> Se borrarán también todas las imágenes asociadas a la galería.</p>" + 
												"<p>Esta acción no puede deshacerse.</p>";
						
						core.widgets.imagesBrowser.gallery.remove.dialog = core.dialogs.show({
							type: "confirm", 
							title: "Borrar galería", 
							content: html, 
							action: core.widgets.imagesBrowser.gallery.remove.go
						});
						
					}, 
					
					go: function() {
						
						core.locking.dialog.lock("Borrando...", core.widgets.imagesBrowser.gallery.remove.dialog);
						
						$.ajax({
							url: core.cfg.paths.root+"action/", 
							type: "POST", 
							dataType: "json", 
							accepts: "json", 
							data: {
								'action': "list-item-delete", 
								'element': "images_galleries", 
								'item': core.widgets.imagesBrowser.gallery.remove.gallery
							}, 
							'success': function(data, textStatus, jqXHR) {
								if (data.result == "ok") {
									
									core.widgets.imagesBrowser.gallery.remove.ok();
									
								} else {
									core.locking.dialog.unlock(core.widgets.imagesBrowser.gallery.remove.dialog);
									core.error(data.error_msg);
								}
							}, 
							'error': function(jqXHR, textStatus, errorThrown) {
								core.locking.dialog.unlock(core.widgets.imagesBrowser.gallery.remove.dialog);
								core.errorUnknown();
							}
						});
						
					}, 
					
					ok: function(id) {
						
						core.widgets.imagesBrowser.gallery.load();
						core.dialogs.close(core.widgets.imagesBrowser.gallery.remove.dialog);
						
					}
					
				}
				
			}, 
			
			// -----------------------------
			images: {
				
				list: {}, 
				selected: 0, 
				
				load: function(gallery) {
					
					$("div.widget-images-browser div.images ul")
								.html("")
								.css({
									'background': "#ffffff url("+core.cfg.paths.root+"img/anim-loader1.gif) center center no-repeat"
								});
					
					if (core.widgets.imagesBrowser.options['image-allow-add']) {
						
						// Init upload button
						$("div.widget-images-browser div.fileinput-button").fileupload({
							url: core.cfg.paths.root + "action/?action=image-upload", 
							formData: {
								gallery: core.widgets.imagesBrowser.gallery.selected, 
								outputs: "default"
							}, 
							dataType: "json", 
							autoUpload: true, 
							start: function(e) {
								core.widgets.imagesBrowser.images.uploadStart();
							}, 
							done: function(e, data) {
								// data.result
					    	// data.textStatus;
					    	// data.jqXHR;
								//pcfgListUploadOK(e, data);
								core.widgets.imagesBrowser.images.uploadOK(data);
							}, 
							progressall: function (e, data) {
								core.widgets.imagesBrowser.images.uploadProgress(data);
					    }, 
					    fail: function (e, data) {
								// data.errorThrown
								// data.textStatus;
								// data.jqXHR;
								//pcfgListUploadError(e, data);
								core.widgets.imagesBrowser.images.uploadError(data);
							}
						});
						
						$("div.widget-images-browser div.fileinput-button").animate({
							'opacity': 0
						}, 200);
						
					}
					
					$.ajax({
						url: core.cfg.paths.root+"action/", 
						type: "POST", 
						dataType: "json", 
						accepts: "json", 
						data: {
							'action': "image-list", 
							'gallery': gallery
						}, 
						'success': function(data, textStatus, jqXHR) {
							if (data.result == "ok") {
								
								core.widgets.imagesBrowser.locked = false;
								core.widgets.imagesBrowser.images.list = data.list;
								core.widgets.imagesBrowser.images.init();
								
							} else {
								core.widgets.imagesBrowser.locked = false;
								$("div.widget-images-browser div.images ul")
											.css({
												'background': "#ffffff"
											})
											.html("<li>Se ha producido un error al cargar las imágenes.</li>");
							}
						}, 
						'error': function(jqXHR, textStatus, errorThrown) {
							core.widgets.imagesBrowser.locked = false;
							$("div.widget-images-browser div.images ul")
										.css({
											'background': "#ffffff"
										})
										.html("<li>Se ha producido un error desconocido al cargar las imágenes.</li>");
						}
					});
					
				}, 
				
				init: function() {
					
					core.widgets.imagesBrowser.images.selected = 0;
					
					// Assign new images
					for (var id in core.widgets.imagesBrowser.images.list) {
						
						image = core.widgets.imagesBrowser.images.list[id];
						
						core.cfg.images[image['id']] = {
							gallery: image['gallery'], 
							name: image['name'], 
							filename: image['main']['filename'], 
							width: image['main']['width'], 
							height: image['main']['height']
						};
						
						core.cfg.thumbs[image['id']] = {
							gallery: image['gallery'], 
							name: image['name'], 
							filename: image['thumb']['filename'], 
							width: image['thumb']['width'], 
							height: image['thumb']['height']
						};
						
					}
					
					$("div.widget-images-browser div.images ul")
								.css({
									'background': "#ffffff"
								});
					
					$("div.widget-images-browser div.fileinput-button").animate({
						'opacity': 1
					}, 500);
					
					core.widgets.imagesBrowser.images.refresh();
					
					if (core.widgets.imagesBrowser.options['image-selected'] != 0) {
						core.widgets.imagesBrowser.images.select(core.widgets.imagesBrowser.options['image-selected']);
					}
					
				}, 
				
				select: function(id) {
					
					if (typeof core.widgets.imagesBrowser.images.list[id] === "undefined") {
						return false;
					}
					
					core.widgets.imagesBrowser.images.selected = id;
					
					$("div.widget-images-browser div.images ul li")
								.removeClass("active");
					
					$("div.widget-images-browser div.images ul li[data-id='"+id+"']")
								.addClass("active");
					
				}, 
				
				refresh: function() {
					
					var html = "", 
							id;
					
					if (core.widgets.imagesBrowser.images.list.length == 0) {
						html += "<li>No hay imágenes en esta galería.</li>";
					} else {
						
						for (var id in core.widgets.imagesBrowser.images.list) {
							html += "<li data-id=\""+id+"\">";
							if (core.widgets.imagesBrowser.options['image-allow-delete']) {
								html += "<button type=\"button\" " + 
												"class=\"btn btn-danger btn-xs delete\" " + 
												"data-id=\""+id+"\" " + 
												"onclick=\"core.widgets.imagesBrowser.images.remove.ask('"+id+"');\" " + 
												"title=\"Borrar esta imagen\"><i class=\"glyphicon glyphicon-remove\"></i></button>";
							}
							if (core.widgets.imagesBrowser.options['image-allow-edit']) {
								html += "<button type=\"button\" " + 
												"class=\"btn btn-success btn-xs edit\" " + 
												"data-id=\""+id+"\" " + 
												"onclick=\"core.widgets.imagesBrowser.images.edit('"+id+"');\" " + 
												"title=\"Editar esta imagen\"><i class=\"glyphicon glyphicon-edit\"></i> Editar</button>";
							}
							html += 	"<a href=\"javascript:;\" title=\"Seleccionar esta imagen\" " + 
												"class=\"select\" " + 
												"onclick=\"core.widgets.imagesBrowser.images.select('"+id+"');\">";
							html += 	"<button type=\"button\" " + 
												"class=\"image\" " + 
												"data-id=\""+id+"\" " + 
												"onclick=\"core.widgets.imagesBrowser.images.select('"+id+"');\" " + 
												"title=\"Seleccionar esta imagen\">" + 
												"<img src=\""+core.cfg.paths.images+core.widgets.imagesBrowser.images.list[id]['thumb']['filename']+"\" />" + 
												"</button>";
							html += 	"<p><strong>"+core.widgets.imagesBrowser.images.list[id]['name']+"</strong>" + 
												core.widgets.imagesBrowser.images.list[id]['main']['filename'] + "<br />" + 
												core.widgets.imagesBrowser.images.list[id]['main']['width'] + "px &times; " + core.widgets.imagesBrowser.images.list[id]['main']['height'] + "px";
							
							html += 	"</p>";
							html += 	"</a>";
							html += "</li>";
						}
						
					}
					
					$("div.widget-images-browser div.images ul").html(html);
					
				}, 
				
				uploadStart: function() {
					
					$("div.widget-images-browser div.fileinput-button").hide();
					$("div.widget-images-browser div.upload-progress div.indicator").css("width", "0%");
					$("div.widget-images-browser div.upload-progress").show();
					
				}, 
				
				uploadProgress: function(data) {
					
					var progress = parseInt(data.loaded / data.total * 100, 10);
					$("div.widget-images-browser div.upload-progress div.indicator").css("width", progress+"%");
					
				}, 
				
				uploadOK: function(data) {
					
					if (data.result.result == "ok") {
						
						$("div.widget-images-browser div.upload-progress div.indicator").css("width", "100%");
						$("div.widget-images-browser div.upload-progress").hide();
						$("div.widget-images-browser div.fileinput-button").show();
						
						core.widgets.imagesBrowser.images.add(data.result.image_id, data.result.image);
						
					} else {
						
						$("div.widget-images-browser div.fileinput-button").show();
						$("div.widget-images-browser div.upload-progress").hide();
						core.error("<h4>Se produjo un error.</h4><p>Se ha producido un error al subir el archivo: "+data.result.error_msg+"</p>");
						
					}
					
				}, 
				
				uploadError: function(data) {
					
					$("div.widget-images-browser div.fileinput-button").show();
					$("div.widget-images-browser div.upload-progress").hide();
					core.error("<h4>Se produjo un error desconocido.</h4><p>Se ha producido un error desconocido al subir el archivo.</p>");
					
				}, 
				
				edit: function(image_id) {
					
					core.widgets.images.edit.open(image_id, {
						'show_gallery': true, 
						'action': core.widgets.imagesBrowser.images.update
					});
					
				}, 
				
				add: function(image_id, image) {
					
					core.cfg.images[image_id] = image;
					
					core.widgets.images.edit.open(image_id, {
						'show_gallery': true, 
						'action': core.widgets.imagesBrowser.images.update
					});
					
				}, 
				
				remove: {
					
					image: 0, 
					dialog: 0, 
					
					ask: function(id) {
						
						core.widgets.imagesBrowser.images.remove.image = id;
						
						var name = $("div.widget-images-browser div.images ul li[data-id='"+id+"'] p strong").text();
						
						var html = "<h4>¿Está seguro de querer borrar la imagen '<strong>"+name+"</strong>'?</h4>" + 
												"<p>Esta acción no puede deshacerse.</p>";
						
						core.widgets.imagesBrowser.images.remove.dialog = core.dialogs.show({
							type: "confirm", 
							title: "Borrar imagen", 
							content: html, 
							action: core.widgets.imagesBrowser.images.remove.go
						});
						
					}, 
					
					go: function() {
						
						core.locking.dialog.lock("Borrando...", core.widgets.imagesBrowser.images.remove.dialog);
						
						$.ajax({
							url: core.cfg.paths.root+"action/", 
							type: "POST", 
							dataType: "json", 
							accepts: "json", 
							data: {
								'action': "image-delete", 
								'item': core.widgets.imagesBrowser.images.remove.image
							}, 
							'success': function(data, textStatus, jqXHR) {
								if (data.result == "ok") {
									
									core.widgets.imagesBrowser.images.remove.ok();
									
								} else {
									core.locking.dialog.unlock(core.widgets.imagesBrowser.images.remove.dialog);
									core.error(data.error_msg);
								}
							}, 
							'error': function(jqXHR, textStatus, errorThrown) {
								core.locking.dialog.unlock(core.widgets.imagesBrowser.images.remove.dialog);
								core.errorUnknown();
							}
						});
						
					}, 
					
					ok: function(id) {
						
						core.widgets.imagesBrowser.images.load(core.widgets.imagesBrowser.gallery.selected);
						core.dialogs.close(core.widgets.imagesBrowser.images.remove.dialog);
						
					}
					
				}, 
				
				update: function(image_id, image) {
					
					core.cfg.images[image_id] = image;
					
					core.widgets.imagesBrowser.images.load(core.widgets.imagesBrowser.gallery.selected);
					
				}
				
			}
			
		}, 
		
		// ......................................................
		// Image List
		
		imagesList: {
			
			target: "", 
			prefix: "image_", 
			
			init: function() {
				
				$("div.widget-images-list").each(function() {
					
					var selector = "#"+$(this).attr("id"), 
							gallery = $(this).find("input[name='gallery']").val(), 
							outputs = $(this).find("input[name='outputs']").val();
					
					$(selector+" div.fileinput-button").fileupload({
						url: core.cfg.paths.root + "action/?action=image-upload", 
						formData: {
							gallery: gallery, 
							outputs: outputs
						}, 
						dataType: "json", 
						autoUpload: true, 
						start: function(e) {
							core.widgets.imagesList.uploadStart(selector);
						}, 
						done: function(e, data) {
							// data.result
				    	// data.textStatus;
				    	// data.jqXHR;
							//pcfgListUploadOK(e, data);
							core.widgets.imagesList.uploadOK(selector, data);
						}, 
						progressall: function (e, data) {
							core.widgets.imagesList.uploadProgress(selector, data);
				    }, 
				    fail: function (e, data) {
							// data.errorThrown
							// data.textStatus;
							// data.jqXHR;
							//pcfgListUploadError(e, data);
							core.widgets.imagesList.uploadError(selector, data);
						}
					});
					
					core.widgets.imagesList.refresh(selector);
					
				});
				
			}, 
			
			uploadStart: function(selector) {
				
				$(selector+" div.fileinput-button").slideUp("fast");
				$(selector+" div.upload-progress div.indicator").css("width", "0%");
				$(selector+" div.upload-progress").slideDown("fast");
				
			}, 
			
			uploadProgress: function(selector, data) {
				
				var progress = parseInt(data.loaded / data.total * 100, 10);
				$(selector+" div.upload-progress div.indicator").css("width", progress+"%");
				
			}, 
			
			uploadOK: function(selector, data) {
				
				if (data.result.result == "ok") {
					
					$(selector+" div.upload-progress div.indicator").css("width", "100%");
					$(selector+" div.upload-progress").slideUp();
					$(selector+" div.fileinput-button").slideDown();
					
					core.widgets.imagesList.add(selector, data.result.image_id, data.result.image);
					
				} else {
					
					$(selector+" div.fileinput-button").slideDown();
					$(selector+" div.upload-progress").slideUp();
					core.error("<h4>Se produjo un error.</h4><p>Se ha producido un error al subir el archivo: "+data.result.error_msg+"</p>");
					
				}
				
			}, 
			
			uploadError: function(selector, data) {
				
				$(selector+" div.fileinput-button").slideDown();
				$(selector+" div.upload-progress").slideUp();
				core.error("<h4>Se produjo un error.</h4><p>Se ha producido un error al subir el archivo: "+data.jqXHR.responseText+"</p>");
				
			}, 
			
			add: function(selector, image_id, image) {
				
				var src = $(selector).attr("data-src");
				var list = $("#"+src).val();
				
				if (list == "") {
					list = [];
				} else if (list.indexOf(",") == -1) {
					list = [parseInt(list)];
				} else {
					list = list.split(",");
				}
				
				core.cfg.images[image_id] = image;
				
				list.push(image_id);
				
				$("#"+src).val(list.join(","));
				
				core.widgets.imagesList.refresh(selector);
				
				core.widgets.imagesList.target = selector;
				
				core.widgets.images.edit.open(image_id, {
					'show_gallery': false, 
					'action': core.widgets.imagesList.update
				});
				
			}, 
			
			remove: function(image_id) {
				
				var src = $(core.widgets.imagesList.target).attr("data-src");
				var list = $("#"+src).val();
				
				if (list == "") {
					list = [];
				} else if (list.indexOf(",") == -1) {
					list = [parseInt(list)];
				} else {
					list = list.split(",");
				}
				
				newlist = aux.array.remove(list, image_id);
				
				$("#"+src).val(newlist.join(","));
				
				core.widgets.imagesList.refresh(core.widgets.imagesList.target);
				
				delete(core.cfg.images[image_id]);
				
			}, 
			
			update: function(image_id, image) {
				
				core.cfg.images[image_id] = image;
				
				core.widgets.imagesList.refresh(core.widgets.imagesList.target);
				
			}, 
			
			refresh: function(selector) {
				
				var src = $(selector).attr("data-src");
				var t = $("#"+src).val();
				var list = t == "" ? [] : t.split(","), 
						html = "";
				
				$(selector+" ul").html("");
				
				if (list.length == 0) {
					
					html = "<li>No hay imágenes.</li>";
					
				} else {
					
					for (var i in list) {
						html +=	"<li data-id=\""+core.widgets.imagesList.prefix+list[i]+"\"><button type=\"button\" class=\"btn btn-default btn-sm move\" title=\"Mover esta imagen\"><i class=\"glyphicon glyphicon-align-justify\"></i></button>" + 
										"<a href=\""+core.cfg.paths.images+core.cfg.images[list[i]]['filename']+"."+core.cfg.images[list[i]]['type']+"\" class=\"thumb image-zoom\" title=\""+aux.string.addSlashes(core.cfg.images[list[i]]['name'])+"\" " + 
										"style=\"background: url("+core.cfg.paths.images+core.cfg.images[list[i]]['filename']+"-thumb."+core.cfg.images[list[i]]['type']+") center center no-repeat;\">&nbsp;</a>" + 
										"<span><strong>"+core.cfg.images[list[i]]['name']+"</strong>" + 
										core.cfg.images[list[i]]['filename']+"."+core.cfg.images[list[i]]['type']+"</span>" + 
										"<button type=\"button\" class=\"btn btn-success btn-xs edit\" title=\"Editar esta imagen\" data-image=\""+list[i]+"\"><i class=\"glyphicon glyphicon-edit\"></i> Editar</button>" + 
										"<button type=\"button\" class=\"btn btn-danger btn-xs delete\" title=\"Borrar esta imagen\" data-image=\""+list[i]+"\"><i class=\"glyphicon glyphicon-remove\"></i></button>" + 
										"</li>";
					}
					
				}
				
				$(selector+" ul").html(html);
				
				$(selector+" ul a").magnificPopup({
						type: "image", 
						mainClass: "mfp-fade", 
						tClose: "Cerrar (Esc)",
		  			tLoading: "Cargando...",
						gallery: {
							enabled: true, 
							tPrev: "Anterior",
		    			tNext: "Siguiente",
		    			tCounter: "%curr% de %total%"
					  }
					});
				
				$(selector+" ul button.edit").unbind("click").click(function() {
					core.widgets.imagesList.target = selector;
					core.widgets.images.edit.open($(this).attr("data-image"), {
						'show_gallery': false, 
						'action': core.widgets.imagesList.update
					});
				});
				$(selector+" ul button.delete").unbind("click").click(function() {
					core.widgets.imagesList.target = selector;
					core.widgets.images.remove.ask($(this).attr("data-image"), {
						'action': core.widgets.imagesList.remove
					});
				});
				
				core.widgets.imagesList.drag.init(selector);
				
			}, 
			
			drag: {
				
				init: function(selector) {
					
					$(selector+" ul").sortable({
						axis: "y", 
						cancel: "a,button.edit,button.delete", 
						cursor: "n-resize", 
						delay: 150, 
						handle: "button.move", 
						revert: true, 
						stop: function(event, ui) {
							core.widgets.imagesList.drag.change(selector, $(this).sortable("toArray", {attribute:"data-id"}));
						}
					});
					
				}, 
				
				change: function(selector, newlist) {
					
					var src = $(selector).attr("data-src"), 
							t = [];
					
					for (var i in newlist) {
						t.push(newlist[i].substr(core.widgets.imagesList.prefix.length));
					}
					
					$("#"+src).val(t.join(","));
					
				}
				
			}
			
		}, 
		
		// ......................................................
		// Image
		
		images: {
			
			zoomInit: function() {
				
				if ($(".image-zoom").length > 0) {
					$(".image-zoom").magnificPopup({
						type: "image", 
						mainClass: "mfp-fade", 
						tClose: "Cerrar (Esc)",
		  			tLoading: "Cargando...",
						gallery: {
							enabled: true, 
							tPrev: "Anterior",
		    			tNext: "Siguiente",
		    			tCounter: "%curr% de %total%"
					  }
					});
				}
				
			}, 
			
			edit: {
				
				action: null, 
				dialog: null, 
				
				open: function(image_id, options) {
					
					options = typeof options !== "undefined" ? options : {};
					
					var show_gallery = typeof options['show_gallery'] === "undefined" ? true : options['show_gallery'];
					
					core.widgets.images.edit.action = typeof options['action'] === "undefined" ? function() {} : options['action'];
					
					var html = "Cargando imagen. Por favor, espere...";
					
					core.widgets.images.edit.dialog = core.dialogs.show({
						type: "edit", 
						title: "Editar Imagen", 
						content: html, 
						width: 600, 
						action: function() {
							core.widgets.images.edit.save(image_id);
						}, 
						onOpen: function() {
							core.locking.dialog.lock("Cargando", core.widgets.images.edit.dialog);
							core.widgets.images.edit.load(image_id, show_gallery);
						}
					});
					
				}, 
				
				load: function(image_id, show_gallery) {
					
					$.ajax({
						url: core.cfg.paths.root+"action/", 
						type: "POST", 
						dataType: "json", 
						accepts: "json", 
						data: {
							'action': "image-load", 
							'image': image_id
						}, 
						'success': function(data, textStatus, jqXHR) {
							if (data.result == "ok") {
								
								core.widgets.images.edit.form(data.image, show_gallery);
								
							} else {
								core.dialogs.exterminate();
								core.error(data.error_msg);
							}
						}, 
						'error': function(jqXHR, textStatus, errorThrown) {
							core.dialogs.exterminate();
							core.errorUnknown();
						}
					});
					
				}, 
				
				form: function(image, show_gallery) {
					
					show_gallery = typeof show_gallery !== "undefined" ? show_gallery : true;
															
					var html = $("div.image-edit").html(), 
							selector = ".ui-dialog[aria-describedby='modal_"+core.widgets.images.edit.dialog+"']";
					
					$(selector+" .ui-dialog-content").html(html);
					
					$(selector+" .ui-dialog-content #form-images").attr("id", "form-images-overlay");
					
					$(selector+" .ui-dialog-content #form-images-overlay #f_image_container div.form-control-static a")
							.html("<img src=\""+core.cfg.paths.images+core.cfg.thumbs[image['image_id']]['filename']+"\" alt=\"\" />")
							.attr("href", core.cfg.paths.images+core.cfg.images[image['image_id']]['filename']);
					$(selector+" .ui-dialog-content #form-images-overlay #f_gallery_id").val(image['gallery_id']);
					if (!show_gallery) {
						$(".ui-dialog-content #form-images-overlay #f_gallery_id_container").hide();
					}
					$(selector+" .ui-dialog-content #form-images-overlay #f_name").val(image['name']);
					$(selector+" .ui-dialog-content #form-images-overlay #f_filename").val(image['filename']);
					$(selector+" .ui-dialog-content #form-images-overlay #f_alt").val(image['alt']);
															
					core.dialogs.refresh();
					
					$(selector+" .ui-dialog-content #form-images-overlay #f_image_container div.form-control-static a").magnificPopup({
						type: "image", 
						mainClass: "mfp-fade", 
						closeOnContentClick: true, 
						showCloseBtn: false, 
						tClose: "Cerrar (Esc)",
		  			tLoading: "Cargando..."
					});
					
					core.locking.dialog.unlock(core.widgets.images.edit.dialog);
					
				}, 
				
				save: function(image_id) {
					
					var check = core.forms.check("images", "#form-images-overlay");
					
					if (check['errors'].length > 0) {
						
						core.dialogs.show({
							type: "warning", 
							title: "Datos incompletos", 
							content: "<h4>Por favor, corrija lo siguiente antes de continuar:</h4><ul><li>"+check['errors'].join("</li><li>")+"</ul>"
						});
						
					} else {
						
						core.locking.dialog.lock("Guardando. Por favor, espere...", core.widgets.images.edit.dialog);
						
						$.ajax({
							url: core.cfg.paths.root + "action/", 
							type: "POST", 
							dataType: "json", 
							accepts: "json", 
							data: {
								action: "image-save", 
								item: image_id, 
								data: check['form_data']
							}, 
							success: function(data, textStatus, jqXHR) {
								if (data.result == "ok") {
									
									core.widgets.images.edit.saveOK(data.image_id, data.image);
									
								} else {
									
									core.locking.dialog.unlock(core.widgets.images.edit.dialog);
									core.error("<h4>Se produjo un error.</h4><p>"+data.error_msg+"</p>");
									
								}
							}, 
							error: function(jqXHR, textStatus, errorThrown) {
								
								core.locking.dialog.unlock(core.widgets.images.edit.dialog);
								core.errorUnknown();
								
							}
						});
						
					}
					
				}, 
				
				saveOK: function(image_id, image) {
					
					core.dialogs.close(core.widgets.images.edit.dialog);
					core.widgets.images.edit.action(image_id, image);
					
				}
				
			}, 
			
			remove: {
				
				action: null, 
				dialog: null, 
				
				ask: function(image_id, options) {
					
					options = typeof options !== "undefined" ? options : {};
					
					core.widgets.images.remove.action = typeof options['action'] === "undefined" ? function() {} : options['action'];
					
					core.widgets.images.remove.dialog = core.dialogs.show({
						type: "confirm", 
						title: "Borrar imagen", 
						content: "<strong>¿Está seguro de querer borrar esta imagen?</strong><br />Esto no puede deshacerse.", 
						action: function() {
							core.widgets.images.remove.go(image_id);
						}
					});
					
				}, 
				
				go: function(image_id) {
					
					core.locking.dialog.lock("Borrando. Por favor, espere...", core.widgets.images.edit.dialog);
					
					$.ajax({
						url: core.cfg.paths.root + "action/", 
						type: "POST", 
						dataType: "json", 
						accepts: "json", 
						data: {
							action: "image-delete", 
							item: image_id
						}, 
						success: function(data, textStatus, jqXHR) {
							if (data.result == "ok") {
								
								core.widgets.images.remove.ok(data.image_id);
								
							} else {
								
								core.locking.dialog.unlock(core.widgets.images.edit.dialog);
								core.error("<h4>Se produjo un error.</h4><p>"+data.error_msg+"</p>");
								
							}
						}, 
						error: function(jqXHR, textStatus, errorThrown) {
							
							core.locking.dialog.unlock(core.widgets.images.edit.dialog);
							core.errorUnknown();
							
						}
					});
					
				}, 
				
				ok: function(image_id) {
					
					core.dialogs.close(core.widgets.images.remove.dialog);
					core.widgets.images.remove.action(image_id);
					
				}
				
			}
			
		}, 
		
		// ......................................................
		// file upload
		
		fileUpload: {
			
			init: function() {
				
				$("div.widget-file-upload").each(function() {
					
					var selector = "#"+$(this).attr("id"), 
							outputs = $(this).find("input[name='outputs']").val();
					
					$(selector+" div.fileinput-button").fileupload({
						url: core.cfg.paths.root + "action/?action=file-upload", 
						formData: {
							outputs: outputs
						}, 
						dataType: "json", 
						autoUpload: true, 
						start: function(e) {
							core.widgets.fileUpload.uploadStart(selector);
						}, 
						done: function(e, data) {
							// data.result
				    	// data.textStatus;
				    	// data.jqXHR;
							//pcfgListUploadOK(e, data);
							core.widgets.fileUpload.uploadOK(selector, data);
						}, 
						progressall: function (e, data) {
							core.widgets.fileUpload.uploadProgress(selector, data);
				    }, 
				    fail: function (e, data) {
							// data.errorThrown
							// data.textStatus;
							// data.jqXHR;
							//pcfgListUploadError(e, data);
							core.widgets.fileUpload.uploadError(selector, data);
						}
					});
					
				});
				
			}, 
			
			uploadStart: function(selector) {
				
				$(selector+" div.fileinput-button").slideUp("fast");
				$(selector+" div.upload-progress div.indicator").css("width", "0%");
				$(selector+" div.upload-progress").slideDown("fast");
				
			}, 
			
			uploadProgress: function(selector, data) {
				
				var progress = parseInt(data.loaded / data.total * 100, 10);
				$(selector+" div.upload-progress div.indicator").css("width", progress+"%");
				
			}, 
			
			uploadOK: function(selector, data) {
				
				if (data.result.result == "ok") {
					
					$(selector+" div.upload-progress div.indicator").css("width", "100%");
					$(selector+" div.upload-progress").slideUp();
					$(selector+" div.fileinput-button").slideDown();
					
					$(selector+" p.form-control-static").html(data.result.filename);
					$(selector+" input[id='"+$(selector).attr("data-src")+"']").val(data.result.filename);
					
				} else {
					
					$(selector+" div.fileinput-button").slideDown();
					$(selector+" div.upload-progress").slideUp();
					core.error("<h4>Se produjo un error.</h4><p>Se ha producido un error al subir el archivo: "+data.result.error_msg+"</p>");
					
				}
				
			}, 
			
			uploadError: function(selector, data) {
				
				$(selector+" div.fileinput-button").slideDown();
				$(selector+" div.upload-progress").slideUp();
				core.error("<h4>Se produjo un error.</h4><p>Se ha producido un error al subir el archivo: "+data.jqXHR.responseText+"</p>");
				
			}
			
		}, 
		
		// ......................................................
		// Autocomplete
		
		autocomplete: {
			
			searching: false, 
			lists: {}, 
			
			init: function() {
				
				$("div.widget-autocomplete-list").each(function() {
					
					var group = $(this);
					var id = group.attr("data-src"), 
							source = $("#"+id+"_source").val(), 
							value = $("#"+id).val();
					
					core.widgets.autocomplete.lists[id] = [];
					
					$("#"+id+"_search").unbind("keyup").keyup(function() {
						var v = $(this).val();
						if (v.length > 4) {
							core.widgets.autocomplete.search(id, source, v);
						} else {
							$("#"+id+"_autocomplete ul.widget-autocomplete-list-options").stop(true).fadeOut(200);
						}
					});
					
					if (value != "") {
						
						$("#"+id+"_autocomplete ul.widget-autocomplete-list-items").html("<li class=\"loading\">Cargando...</li>");
						$("#"+id).val("");
						core.widgets.autocomplete.load(id, source, value);
						
					}
					
				});
				
			}, 
			
			load: function(id, element, value) {
				
				$.ajax({
					url: core.cfg.paths.root+"action/", 
					type: "POST", 
					dataType: "json", 
					accepts: "json", 
					data: {
						'action': "autocomplete-init", 
						'element': element, 
						'value': value
					}, 
					'success': function(data, textStatus, jqXHR) {
						if (data.result == "ok") {
							$("#"+id+"_autocomplete ul.widget-autocomplete-list-items").html("");
							for (var i in data.list) {
								core.widgets.autocomplete.add(id, i, data.list[i]);
							}
						} else {
							core.widgets.autocomplete.error();
						}
					}, 
					'error': function(jqXHR, textStatus, errorThrown) {
						core.widgets.autocomplete.error();
					}
				});
				
			}, 
			
			search: function(id, element, value) {
				
				if (!core.widgets.autocomplete.searching) {
					
					var v = $("#"+id+"_search").val(), 
							source = $("#"+id+"_source").val();
					
					core.widgets.autocomplete.searching = true;
					
					$.ajax({
						url: core.cfg.paths.root+"action/", 
						type: "POST", 
						dataType: "json", 
						accepts: "json", 
						data: {
							'action': "autocomplete-search", 
							'element': element, 
							'value': value
						}, 
						'success': function(data, textStatus, jqXHR) {
							if (data.result == "ok") {
								core.widgets.autocomplete.list(id, data.list);
							} else {
								core.widgets.autocomplete.error();
							}
						}, 
						'error': function(jqXHR, textStatus, errorThrown) {
							core.widgets.autocomplete.error();
						}
					});
					
				}
				
			}, 
			
			list: function(id, list) {
				
				html = "<li class=\"close\"><button type=\"button\" title=\"Cerrar\" onclick=\"core.widgets.autocomplete.close('"+id+"');\"><i class=\"glyphicon glyphicon-remove\"></i></button></li>";
				
				if (list.length == 0) {
					html += "<li class=\"empty\">No se encontraron resultados.</li>";
				} else {
					for (var i in list) {
						html += "<li><button type=\"button\" title=\"Añadir\" onclick=\"core.widgets.autocomplete.add('"+id+"','"+i+"','"+list[i].replace(/'/g, "\\'")+"');\"><i class=\"glyphicon glyphicon-plus-sign\"></i> "+list[i]+"</button></li>";
					}
				}
				
				$("#"+id+"_autocomplete ul.widget-autocomplete-list-options").html(html);
				$("#"+id+"_autocomplete ul.widget-autocomplete-list-options").fadeIn(350);
				
				core.widgets.autocomplete.searching = false;
				
			}, 
			
			add: function(id, k, v) {
				
				if (!aux.array.in_array(k, core.widgets.autocomplete.lists[id])) {
					
					var html = "<li id=\"f_autocomplete_item_"+id+"_"+k+"\"><i class=\"glyphicon glyphicon-check\"></i> "+v+" <button type=\"button\" title=\"Quitar este elemento\" onclick=\"core.widgets.autocomplete.remove($(this),'"+id+"','"+k+"');\"><i class=\"glyphicon glyphicon-remove\"></i></button></li>";
					
					$(html).hide().appendTo("#"+id+"_autocomplete ul.widget-autocomplete-list-items").slideDown(250);
					
					core.widgets.autocomplete.lists[id] = aux.array.add(core.widgets.autocomplete.lists[id], k);
					
					$("#"+id).val(core.widgets.autocomplete.lists[id].join(",")).trigger("change");
					
				}
				
			}, 
			
			remove: function(obj, id, k) {
				
				obj.parent().slideUp(250, function() {
					$(this).remove();
				});
				
				core.widgets.autocomplete.lists[id] = aux.array.remove(core.widgets.autocomplete.lists[id], k);
				
				$("#"+id).val(core.widgets.autocomplete.lists[id].join(",")).trigger("change");
				
			}, 
			
			error: function() {
				
				core.widgets.autocomplete.searching = false;
				
			}, 
			
			close: function(id) {
				
				$("#"+id+"_autocomplete ul.widget-autocomplete-list-options").fadeOut(200);
				
			}
			
		}, 
		
		// Accordion
		
		accordion: {
			
			init: function(selector) {
				
				$(selector).each(function() {
					
					$(this).find("[data-target]").unbind("click").click(function() {
						
						$($(this).attr("data-parent")+" .core-accordion-collapse").stop(true).slideUp(350);
						$($(this).attr("data-parent")+" .core-accordion-switch i").removeClass("glyphicon-chevron-down").addClass("glyphicon-chevron-right");
						
						var collapse = $($(this).attr("data-target"));
						
						if (collapse.css("display") == "block") {
							collapse.stop(true).slideUp(350);
							$(this).find(".core-accordion-switch i").removeClass("glyphicon-chevron-down").addClass("glyphicon-chevron-right");
						} else {
							collapse.stop(true).slideDown(350);
							$(this).find(".core-accordion-switch i").removeClass("glyphicon-chevron-right").addClass("glyphicon-chevron-down");
						}
						
					});
					
				});
				
			}
			
		}
		
	}, 
	
	// ========================================================
	// Editing forms functions
	
	forms: {
		
		fields: [], 
		
		// Initialization
		
		init: function() {
			
			$("div.form-action button.form-save").unbind("click").click(function() {
				$("input[name='destination']").val($(this).attr("data-destination"));
				core.forms.save($(this).attr("data-form"));
			});
			
			// For site config
			$("div.form-action button.form-config").unbind("click").click(function() {
				core.forms.configSave($(this).attr("data-form"));
			});
			
			$("[data-previous]").change(function() {
				core.forms.changeCheck();
			});
			
			// Init elements
			core.forms.elements.init();
			
		}, 
		
		changeCheck: function() {
			
			var changed = false;
			
			$("[data-previous]").each(function() {
				if ($(this).val() != $(this).attr("data-previous")) {
					changed = true;
				}
			});
			
			if (changed) {
				core.locking.links.lock();
			} else {
				core.locking.links.unlock();
			}
			
		}, 
		
		selectOnchange: function(target, element, parent, selected) {
			
			$(target).html("<option>Cargando...</option>");
			$(target).attr("disabled", "disabled");
			
			$.ajax({
				url: core.cfg.paths.root + "action/", 
				type: "POST", 
				dataType: "json", 
				accepts: "json", 
				data: {
					action: "get-list", 
					element: element, 
					options: {
						parent_id: parent
					}
				}, 
				success: function(data, textStatus, jqXHR) {
					if (data.result == "ok") {
						
						var s;
						if (typeof selected !== "undefined") {
							s = selected;
						} else if ($(target+"_selected").val() != "") {
							s = $(target+"_selected").val();
						} else {
							s = "";
						}
						
						var options = "";
						for (var i in data.list) {
							options += "<option value=\""+aux.string.addSlashes(i)+"\""+(s == i ? " selected=\"selected\"" : "")+">"+data.list[i]+"</option>";
						}
						$(target).html(options).change();
						$(target).removeAttr("disabled");
						
					} else {
						
						$(target).html("<option>Error al cargar.</option>");
						
					}
				}, 
				error: function(jqXHR, textStatus, errorThrown) {
					
					$(target).html("<option>Error al cargar.</option>");
					
				}
			});
			
		}, 
		
		save: function(form) {
			
			var form_selector = "#form-"+form;
			var check = core.forms.check(form, form_selector);
			
			if (check['errors'].length > 0) {
				
				core.dialogs.show({
					type: "warning", 
					title: "Datos incompletos", 
					content: "<h4>Por favor, corrija lo siguiente antes de continuar:</h4><ul><li>"+check['errors'].join("</li><li>")+"</ul>"
				});
				
			} else {
				
				core.locking.pageLoad.lock("Guardando. Por favor, espere...");
				
				$.ajax({
					url: core.cfg.paths.root + "action/", 
					type: "POST", 
					dataType: "json", 
					accepts: "json", 
					data: {
						action: "form-save", 
						element: $(form_selector+" input[name='element']").val(), 
						fields: $(form_selector+" input[name='fields']").val(), 
						mode: $(form_selector+" input[name='mode']").val(), 
						item: $(form_selector+" input[name='item']").val(), 
						data: check['form_data']
					}, 
					success: function(data, textStatus, jqXHR) {
						if (data.result == "ok") {
							
							if ($("input[name='destination']").val() != "") {
								document.location.href = aux.url.addParam($("input[name='destination']").val(), "item", data.item);
							} else {
								document.location.href = aux.url.addParam(document.location, "ok");
							}
							
						} else {
							
							core.locking.pageLoad.unlock();
							core.error("<h4>Se produjo un error.</h4><p>"+data.error_msg+"</p>");
							
						}
					}, 
					error: function(jqXHR, textStatus, errorThrown) {
						
						core.locking.pageLoad.unlock();
						core.errorUnknown();
						
					}
				});
				
			}
			
		}, 
		
		configSave: function(form) {
			
			var form_selector = "#form-"+form;
			var check = core.forms.check(form, form_selector);
			
			if (check['errors'].length > 0) {
				
				core.dialogs.show({
					type: "warning", 
					title: "Datos incompletos", 
					content: "<h4>Por favor, corrija lo siguiente antes de continuar:</h4><ul><li>"+check['errors'].join("</li><li>")+"</ul>"
				});
				
			} else {
				
				core.locking.pageLoad.lock("Guardando. Por favor, espere...");
				
				$.ajax({
					url: core.cfg.paths.root + "action/", 
					type: "POST", 
					dataType: "json", 
					accepts: "json", 
					data: {
						action: "config-save", 
						data: check['form_data']
					}, 
					success: function(data, textStatus, jqXHR) {
						if (data.result == "ok") {
							
							document.location.href = aux.url.addParam(document.location, "ok");
							
						} else {
							
							core.locking.pageLoad.unlock();
							core.error("<h4>Se produjo un error.</h4><p>"+data.error_msg+"</p>");
							
						}
					}, 
					error: function(jqXHR, textStatus, errorThrown) {
						
						core.locking.pageLoad.unlock();
						core.errorUnknown();
						
					}
				});
				
			}
			
		}, 
		
		// Form validation
		
		check: function(form, form_selector) {
			
			var form_data = {}, 
					errors = [], 
					v, t, s;
			
			core.forms.validation.msgs.clear(form_selector);
			
			for (var f in core.forms.fields[form]) {
				
				if (core.forms.fields[form][f]['type'] != "uneditable") {
					
					v = $(form_selector + " #f_"+f).val();
					
					if (core.forms.fields[form][f]['required'] && v == "") {
						
						errors.push("'<strong>"+core.forms.fields[form][f]['label']+"</strong>': es obligatorio.");
						core.forms.validation.msgs.error(form_selector, f);
						
					} else if (typeof core.forms.fields[form][f]['validation'] !== "undefined" && core.forms.fields[form][f]['validation'] != "") {
						
						if (!core.forms.validation.validate(core.forms.fields[form][f]['validation'], v, f)) {
							
							if (typeof core.forms.fields[form][f]['validation-msg'] !== "undefined") {
								errors.push("'<strong>"+core.forms.fields[form][f]['label']+"</strong>': "+core.forms.fields[form][f]['validation-msg']);
							} else {
								errors.push("'<strong>"+core.forms.fields[form][f]['label']+"</strong>': incorrecto.");
							}
							core.forms.validation.msgs.error(form_selector, f);
							
						}
						
					}
					
					form_data[f] = v;
					
				}
				
			}
			
			return {
				form_data: form_data, 
				errors: errors
			};
			
		}, 
		
		// Validation
		
		validation: {
			
			validate: function(type, str, name) {
				
				switch (type) {
					
					case "username":
					case "password":
						if (str != "") {
							return str.length >= 8;
						} else {
							return true;
						}
					break;
					
					case "password-check":
						var s = $("input[name='"+(name.substr(0, name.length-1))+"']").val();
						if (str != "" || s != "") {
							return s == str;
						} else {
							return true;
						}
					break;
					
					case "email":
						if (str != "") {
							var filter  = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
							return filter.test(str);
						} else {
							return true;
						}
					break;
					
					case "phone":
						if (str != "") {
							if (str.length < 9 || !aux.validation.validChars(str, "+() 0123456789")) {
								return false;
							} else {
								return true;
							}
						} else {
							return true;
						}
					break;
					
					case "list":
						if (str.indexOf(",") != -1) {
							return false;
						} else {
							return true;
						}
					break;
					
					case "list-email":
						if (str.indexOf(",") != -1) {
							return false;
						} else {
							return core.forms.validation.validate("email", str, name);
						}
					break;
					
					default:
						return (str != "");
					break;
					
				}
				
				return true;
				
			}, 
			
			// Validation messages
			
			msgs: {
				
				clear: function(selector) {
					
					$(selector+" div.form-group").removeClass("has-error");
					
				}, 
				
				error: function(selector, fieldname) {
					
					$(selector+" #f_"+fieldname).closest("div.form-group").addClass("has-error");
					
				}, 
				
				show: function(selector, fieldname, text, opts) {
					
					var field = $(selector+" [name='"+fieldname+"']"), 
							left, 
							top, 
							type, 
							ref, 			
							w;
					
					$(field).attr("title", text);
					var tp = $(field).tooltip({
						position: { my: "left bottom", at: "right-10 top+10", collision: "flipfit" }, 
						tooltipClass: "validation_msg"
					});
					tp.tooltip("open");
					
					field.attr("src", field.css("border-color"));
					field.css("border-color", "#c60000");
					
					field.closest(".form-group").addClass("has-error");
					
				}
				
			}
			
		}, 
		
		// ---------------------------------
		// Form elements
		
		elements: {
			
			init: function() {
				
				// Switches
				core.forms.elements.switches.init();
				
				// Switch list
				core.forms.elements.switchLists.init();
				
				// Checkboxes
				core.forms.elements.checkboxes.init();
				
				// Button groups
				core.forms.elements.buttonGroups.init();
				
				// Add lists
				core.forms.elements.addLists.init();
				
				// Add lists codes
				core.forms.elements.addListsTexts.init();
				
				// Autocomplete combos
				core.widgets.autocomplete.init();
				
				// Images Lists
				core.widgets.imagesList.init();
				
				// File uploads
				core.widgets.fileUpload.init();
				
			}, 
			
			// Switches
			
			switches: {
				
				init: function() {
					
					$("div.form-switch").each(function() {
						
						var group = $(this), 
								byes = group.find("button.form-switch-yes"), 
								bno = group.find("button.form-switch-no");
						
						byes.removeClass("btn-default btn-success btn-danger btn-well");
						bno.removeClass("btn-default btn-success btn-danger btn-well");
						
						if ($("#"+group.attr("data-src")).val() == 1) {
							byes.addClass("btn-success btn-well");
							bno.addClass("btn-default");
						} else {
							byes.addClass("btn-default");
							bno.addClass("btn-danger btn-well");
						}
						
						byes.click(function() {
							$(this).removeClass("btn-default").addClass("btn-success btn-well");
							bno.removeClass("btn-danger btn-well").addClass("btn-default");
							$("#"+group.attr("data-src")).val($(this).attr("data-value")).trigger("change");
						});
						
						bno.click(function() {
							$(this).removeClass("btn-default").addClass("btn-danger btn-well");
							byes.removeClass("btn-success btn-well").addClass("btn-default");
							$("#"+group.attr("data-src")).val($(this).attr("data-value")).trigger("change");
						});
						
					});
					
				}
				
			}, 
			
			// Switch lists
			
			switchLists: {
				
				list: {}, 
				
				init: function() {
					
					$("div.form-switch-list").each(function() {
						
						var group = $(this);
						var id = group.attr("data-src");
						core.forms.elements.switchLists.list[id] = ($("#"+id).val() != "") ? $("#"+id).val().split(",") : [];
						
						group.find("div.form-switch-list-item").each(function() {
							
							var byes = $(this).find("button.form-switch-yes"), 
									bno = $(this).find("button.form-switch-no"), 
									pos = $(this).attr("data-pos");
							
							if (core.forms.elements.switchLists.list[id][pos] == 1) {
								byes.addClass("btn-success btn-well");
								bno.addClass("btn-default");
							} else {
								byes.addClass("btn-default");
								bno.addClass("btn-danger btn-well");
							}
							
							byes.click(function() {
								$(this).removeClass("btn-default").addClass("btn-success btn-well");
								bno.removeClass("btn-danger btn-well").addClass("btn-default");
								core.forms.elements.switchLists.list[id][pos] = $(this).attr("data-value");
								$("#"+id).val(core.forms.elements.switchLists.list[id].join(",")).trigger("change");
							});
							
							bno.click(function() {
								$(this).removeClass("btn-default").addClass("btn-danger btn-well");
								byes.removeClass("btn-success btn-well").addClass("btn-default");
								core.forms.elements.switchLists.list[id][pos] = $(this).attr("data-value");
								$("#"+id).val(core.forms.elements.switchLists.list[id].join(",")).trigger("change");
							});
							
						});
						
					});
					
				}
				
			}, 
			
			// Checkboxes
			
			checkboxes: {
				
				list: {}, 
				
				init: function() {
					
					$("div.form-checkbox-list").each(function() {
						
						var group = $(this);
						var id = group.attr("data-src");
						core.forms.elements.checkboxes.list[id] = ($("#"+id).val() != "") ? $("#"+id).val().split(",") : [];
						
						group.find("button").each(function() {
							
							var v = $(this).attr("data-value");
							
							if (aux.array.in_array(v, core.forms.elements.checkboxes.list[id])) {
								$(this).removeClass("btn-default").addClass("btn-success").find("i").removeClass("glyphicon-unchecked").addClass("glyphicon-check");
							}
							
							$(this).unbind("click").click(function() {
								if ($(this).hasClass("btn-success")) {
									$(this).removeClass("btn-success").addClass("btn-default").find("i").removeClass("glyphicon-check").addClass("glyphicon-unchecked");
									core.forms.elements.checkboxes.list[id] = aux.array.remove(core.forms.elements.checkboxes.list[id], $(this).attr("data-value"));
								} else {
									$(this).removeClass("btn-default").addClass("btn-success").find("i").removeClass("glyphicon-unchecked").addClass("glyphicon-check");
									core.forms.elements.checkboxes.list[id] = aux.array.add(core.forms.elements.checkboxes.list[id], $(this).attr("data-value"));
								}
								$("#"+id).val(core.forms.elements.checkboxes.list[id].join(",")).trigger("change");
							});
							
						});
						
					});
					
				}
				
			}, 
			
			// Button groups
			
			buttonGroups: {
				
				init: function() {
					
					$("div.form-button-group").each(function() {
						
						var group = $(this);
						var value = $("#"+group.attr("data-src")).val();
						
						group.find("button[data-value='"+value+"']").removeClass("btn-default").addClass("btn-primary btn-well");
						
						group.find("button").unbind("click").click(function() {
							group.find("button").removeClass("btn-primary btn-well").addClass("btn-default");
							$(this).removeClass("btn-default").addClass("btn-primary btn-well");
							$("#"+group.attr("data-src")).val($(this).attr("data-value")).trigger("change");
						});
						
					});
					
				}
				
			}, 
			
			// Add lists
			
			addLists: {
				
				lists: {}, 
				
				init: function() {
					
					$("div.form-add-list").each(function() {
						
						var group = $(this);
						var id = group.attr("data-src"), 
								value = $("#"+id).val();
						
						core.forms.elements.addLists.lists[id] = [];
						
						group.find("button.form-add-list-button").unbind("click").click(function() {
							var v = $("#"+id+"_name").val();
							if (v == "") {
								core.error("<p>Por favor, indique un nombre.</p>");
								return false;
							}
							var f = id.substr(2);
							if ($("#"+id+"_validation").length > 0) {
								if (!core.forms.validation.validate($("#"+id+"_validation").val(), v, f)) {
									var form = $(this).parents("form[data-form]").attr("data-form");
									core.error("<p>"+core.forms.fields[form][f]['validation-msg']+"</p>");
									return false;
								}
							}
							core.forms.elements.addLists.add(id, v);
							$("#"+id+"_name").val("");
						});
						
						if (value != "") {
							var t = value.split(",");
							for (var i in t) {
								core.forms.elements.addLists.add(id, t[i]);
							}
						}
						
					});
					
				}, 
				
				add: function(id, v) {
					
					if (!aux.array.in_array(v, core.forms.elements.addLists.lists[id])) {
						
						var k = core.forms.elements.addLists.lists[id].length;
						
						var html = "<li id=\"f_addlist_item_"+id+"_"+k+"\">"+v+" <button type=\"button\" class=\"delete\" title=\"Quitar este elemento\" onclick=\"core.forms.elements.addLists.remove($(this),'"+id+"','"+v+"');\"><i class=\"glyphicon glyphicon-remove\"></i></button></li>";
						
						$(html).hide().appendTo("#"+id+"_addlist ul.form-add-list-items").slideDown(250);
						
						core.forms.elements.addLists.lists[id] = aux.array.add(core.forms.elements.addLists.lists[id], v);
						
						$("#"+id).val(core.forms.elements.addLists.lists[id].join(",")).trigger("change");
						
					}
					
				}, 
				
				remove: function(obj, id, v) {
					
					obj.parent().slideUp(250, function() {
						$(this).remove();
					});
					
					core.forms.elements.addLists.lists[id] = aux.array.remove(core.forms.elements.addLists.lists[id], v);
					
					$("#"+id).val(core.forms.elements.addLists.lists[id].join(",")).trigger("change");
					
				}
				
			}, 
			
			// Add lists codes
			
			addListsTexts: {
				
				lists: {}, 
				id: 0, 
				num: 0, 
				dialog: 0, 
				title: "", 
				
				init: function() {
					
					$("div.form-add-list-texts").each(function() {
						
						var group = $(this);
						var id = group.attr("data-src");
						
						group.find("div.form-add-list-texts-add button").unbind("click").click(function() {
							core.forms.elements.addListsTexts.edit($(this).attr("data-id"), "");
						});
						
						for (var n in core.forms.elements.addListsTexts.lists[id]) {
							core.forms.elements.addListsTexts.add(id, n);
						}
						
					});
					
				}, 
				
				edit: function(id, num) {
					
					var html = "";
					
					core.forms.elements.addListsTexts.id = id;
					core.forms.elements.addListsTexts.num = num;
					
					var name = num !== "" ? core.forms.elements.addListsTexts.lists[id][num]['name'] : "", 
							text = num !== "" ? core.forms.elements.addListsTexts.lists[id][num]['text'] : "";
					
					html += "<div class=\"overlay-form form-horizontal form form-add-list-texts-overlay\">" + 
									"<div class=\"form-group\"><label class=\"col-sm-4 control-label\">Nombre</label>" + 
									"<div class=\"col-sm-8\"><input type=\"text\" name=\"name\" class=\"form-control\" value=\""+name+"\" />" + 
									"<span class=\"legend\">Nombre para identificar el "+core.forms.elements.addListsTexts.title+".</span>" + 
									"</div></div>" + 
									"<div class=\"form-group\"><label class=\"col-sm-4 control-label\">"+core.forms.elements.addListsTexts.title+"</label>" + 
									"<div class=\"col-sm-8\"><textarea name=\"text\" class=\"form-control\">" + 
									aux.string.Base64.decode(text) + 
									"</textarea>" + 
									"<span class=\"legend\">Copie y pegue aquí el "+core.forms.elements.addListsTexts.title+".</span>" + 
									"</div></div>" + 
									"</div>";
					
					core.forms.elements.addListsTexts.dialog = core.dialogs.show({
						type: "edit", 
						width: "auto", 
						title: num !== "" ? "Editar "+core.forms.elements.addListsTexts.title : "Nuevo "+core.forms.elements.addListsTexts.title, 
						text_button1: "Guardar", 
						content: html, 
						action: core.forms.elements.addListsTexts.save
					});
					
				}, 
				
				save: function() {
					
					var name = $("div.form-add-list-texts-overlay input[name='name']").val(), 
							text = $("div.form-add-list-texts-overlay textarea[name='text']").val(), 
							errors = "";
					
					$("div.form-add-list-texts-overlay div.form-group").removeClass("has-error");
					
					if (name == "" || name.length < 3) {
						$("div.form-add-list-texts-overlay input[name='name']").parents("div.form-group").addClass("has-error");
						errors += "<li>Debe indicar un nombre de al menos 3 caracteres.</li>";
					}
					if (text == "") {
						$("div.form-add-list-texts-overlay textarea[name='text']").parents("div.form-group").addClass("has-error");
						errors += "<li>Debe indicar un "+core.forms.elements.addListsTexts.title+".</li>";
					}
					
					if (errors != "") {
						core.error("<p>Por favor, corrija lo siguiente para continuar:</p><ul>"+errors+"</ul>");
						return false;
					}
					
					var id = core.forms.elements.addListsTexts.id, 
							num = core.forms.elements.addListsTexts.num;
					
					if (core.forms.elements.addListsTexts.num === "") {
						// Add
						
						core.forms.elements.addListsTexts.lists[id].push({
							name: name, 
							text: aux.string.Base64.encode(text)
						});
						
						num = core.forms.elements.addListsTexts.num = core.forms.elements.addListsTexts.lists[id].length - 1;
						
						core.forms.elements.addListsTexts.add(id, num);
						
					} else {
						// Edit
						
						core.forms.elements.addListsTexts.lists[id][num] = {
							name: name, 
							text: aux.string.Base64.encode(text)
						};
						
						core.forms.elements.addListsTexts.update(id, num);
						
					}
					
				}, 
				
				add: function(id, num) {
					
					num = num === "" ? 0 : num;
					
					var name = core.forms.elements.addListsTexts.lists[id][num]['name'];
					
					var html = "<li data-num=\""+num+"\"><strong>"+name+"</strong>" + 
											"<button type=\"button\" class=\"edit\" title=\"Editar este "+core.forms.elements.addListsTexts.title+"\" onclick=\"core.forms.elements.addListsTexts.edit('"+id+"',"+num+");\"><i class=\"glyphicon glyphicon-edit\"></i></button>" + 
											"<button type=\"button\" class=\"delete\" title=\"Quitar este "+core.forms.elements.addListsTexts.title+"\" onclick=\"core.forms.elements.addListsTexts.remove.ask('"+id+"',"+num+");\"><i class=\"glyphicon glyphicon-remove\"></i></button>" + 
											"</li>";
					
					$(html).hide().appendTo("#f_"+id+"_addlist ul.form-add-list-texts-items").slideDown(250);
					
					$("#f_"+id).val(aux.string.Base64.encode(JSON.stringify(core.forms.elements.addListsTexts.lists[id])));
					
					core.dialogs.close(core.forms.elements.addListsTexts.dialog);
					
				}, 
				
				update: function(id, num) {
					
					num = num === "" ? 0 : num;
					
					var name = core.forms.elements.addListsTexts.lists[id][num]['name'];
					
					$("#f_"+id+"_addlist ul.form-add-list-texts-items li[data-num='"+num+"'] strong").html(name);
					
					$("#f_"+id).val(aux.string.Base64.encode(JSON.stringify(core.forms.elements.addListsTexts.lists[id])));
					
					core.dialogs.close(core.forms.elements.addListsTexts.dialog);
					
				}, 
				
				remove: {
					
					ask: function(id, num) {
						
						core.forms.elements.addListsTexts.id = id;
						core.forms.elements.addListsTexts.num = num;
						
						var name = core.forms.elements.addListsTexts.lists[id][num]['name'];
						
						core.forms.elements.addListsTexts.dialog = core.dialogs.show({
							type: "confirm", 
							title: "Borrar "+core.forms.elements.addListsTexts.title, 
							content: "<h4>¿Está seguro de querer borrar el "+core.forms.elements.addListsTexts.title+" '"+name+"'?</h4><p>Esta operación no puede deshacerse.</p>", 
							action: core.forms.elements.addListsTexts.remove.go
						});
						
					}, 
					
					go: function() {
						
						var id = core.forms.elements.addListsTexts.id, 
								num = core.forms.elements.addListsTexts.num;
						
						core.forms.elements.addListsTexts.lists[id].splice(num, 1);
						
						$("#f_"+id+"_addlist ul.form-add-list-texts-items li[data-num='"+num+"']").slideUp();
						
						$("#f_"+id).val(aux.string.Base64.encode(JSON.stringify(core.forms.elements.addListsTexts.lists[id])));
						
						core.dialogs.close(core.forms.elements.addListsTexts.dialog);
						
					}
					
				}
				
			}
			
		}
	
	}, 
	
	// ========================================================
	// Stats
	
	stats: {
		
		load_num: 0, 
		load_stack: {}, 
		
		charts: {}, 
		options: {}, 
		
		item: null, 
		
		init: function(section) {
			
			if (core.stats.load_stack.length > 0) {
				core.stats.dates.init();
				core.stats.load();
			}
			
		}, 
		
		// Dates
		
		dates: {
			
			type: null, 
			year: null, 
			month: null, 
			start: null, 
			end: null, 
			min: null, 
			max: null, 
			
			init: function() {
				
				core.stats.options = {
					item: core.stats.item, 
					type: core.stats.dates.type, 
					year: 0, 
					month: 0, 
					start: core.stats.dates.start, 
					end: core.stats.dates.end
				};
				
				core.stats.dates.selectYear(core.stats.dates.year);
				core.stats.dates.selectMonth(core.stats.dates.month);
				
				$("div.dates-panel div.date div.years button").unbind("click").click(function() {
					core.stats.dates.selectYear($(this).attr("data-item"));
				});
				
				$("div.dates-panel div.date div.months button").unbind("click").click(function() {
					core.stats.dates.selectMonth($(this).attr("data-item"));
				});
				
				$("div.dates-panel div.range input[name='selector_start']").val(core.stats.dates.start);
				$("div.dates-panel div.range input[name='selector_end']").val(core.stats.dates.end);
				
				core.cfg.datepicker_options.minDate = core.stats.dates.min;
				core.cfg.datepicker_options.maxDate = core.stats.dates.max;
				
				datepicker_options_start = core.cfg.datepicker_options;
				datepicker_options_start.onClose = function(selectedDate) {
					$("div.dates-panel div.range input[name='selector_end']").datepicker("option", "minDate", selectedDate);
				};
				$("div.dates-panel div.range input[name='selector_start']").datepicker(datepicker_options_start);
				$("div.dates-panel div.range input[name='selector_start']").parent().find("button").unbind("click").click(function() {
					$("div.dates-panel div.range input[name='selector_start']").datepicker("show");
				});
				
				var datepicker_options_end = core.cfg.datepicker_options;
				datepicker_options_end.onClose = function(selectedDate) {
					$("div.dates-panel div.range input[name='selector_start']").datepicker("option", "maxDate", selectedDate);
				};
				$("div.dates-panel div.range input[name='selector_end']").datepicker(datepicker_options_end);
				$("div.dates-panel div.range input[name='selector_end']").parent().find("button").unbind("click").click(function() {
					$("div.dates-panel div.range input[name='selector_end']").datepicker("show");
				});
				
				$("div.dates-panel div.action button.switch").unbind("click").click(core.stats.dates.swap);
				$("div.dates-panel div.action button.refresh").unbind("click").click(core.stats.refresh);
				
				//core.stats.refresh();
				
			}, 
			
			selectYear: function(y) {
				
				$("div.dates-panel div.date div.years button").removeClass("btn-success active").addClass("btn-default");
				$("div.dates-panel div.date div.years button[data-item='"+y+"']").addClass("btn-success active");
				core.stats.options.year = y;
				
			}, 
			
			selectMonth: function(m) {
				
				$("div.dates-panel div.date div.months button").removeClass("btn-success active").addClass("btn-default");
				
				if (core.stats.options.month == m) {
					core.stats.options.month = 0;
				} else {
					$("div.dates-panel div.date div.months button[data-item='"+m+"']").addClass("btn-success active");
					core.stats.options.month = m;
				}
				
			}, 
			
			swap: function() {
				
				if (core.stats.options.type == "date") {
					$("div.dates-panel div.date").slideUp("fast");
					$("div.dates-panel div.range").slideDown("fast");
					$("div.dates-panel div.action button.switch").html("<i class=\"glyphicon glyphicon-th-large\"></i> Elegir fecha");
					core.stats.options.type = "range";
				} else {
					$("div.dates-panel div.date").slideDown("fast");
					$("div.dates-panel div.range").slideUp("fast");
					$("div.dates-panel div.action button.switch").html("<i class=\"glyphicon glyphicon-calendar\"></i> Elegir días");
					core.stats.options.type = "date";
				}
				
			}
			
		}, 
		
		load: function() {
			
			if (typeof core.stats.load_stack === "undefined" || core.stats.load_num >= core.stats.load_stack.length) {
				$("div.dates-panel div.action button.refresh").removeAttr("disabled");
				return;
			}
			
			var target = "stats-"+core.stats.load_stack[core.stats.load_num];
			$("#"+target).parent().find("div.loading").show();
			$("#"+target).hide();
			
			$.ajax({
				url: core.cfg.paths.root + "stats/chart/", 
				type: "POST", 
				dataType: "json", 
				accepts: "json", 
				timeout: 600000, 
				data: {
					item: core.stats.load_stack[core.stats.load_num], 
					options: core.stats.options
				}, 
				success: function(data, textStatus, jqXHR) {
					if (data.result == "ok") {
						core.stats.show(data.data);
					} else {
						$("#"+target).parent().html("<p class=\"error\">Error al cargar la gráfica: "+data.error_msg+"</p>");
					}
				}, 
				error: function(jqXHR, textStatus, errorThrown) {
					$("#"+target).parent().html("<p class=\"error\">Error desconocido al cargar la gráfica.</p>");
				}
			});
			
		}, 
		
		show: function(data) {
			
			var series = [], 
					target = "stats-"+core.stats.load_stack[core.stats.load_num];
			
			$("#"+target).parent().find("div.loading").fadeOut("fast", function() {
				$("#"+target).html("").show().animate({
					'width': data.width, 
					'height': data.height
				}, 500, function() {
					
					if (data.type == "table") {
						
						$("#"+target).html(data.html);
						
					} else {
						
						var menu = $("#"+target).parents("div.chart").find("div.menu ul"), 
								menu_html = "";
						
						for (var d in data.series) {
							series.push(data.series[d].data);
							if (data.graph_type == "lines") {
								menu_html += "<li><label><input type=\"checkbox\" data-item=\""+core.stats.load_stack[core.stats.load_num]+"\" data-num=\""+d+"\" checked /><em style=\"background:"+data.series[d].color+";\"></em><span>"+data.series[d].title+"</span></label></li>";
							}
						}
						
						if (data.graph_type == "lines" && typeof data.nolegend === "undefined") {
							menu.html(menu_html);
						} else {
							$("#"+target).parents("div.chart").find("div.menu p").hide();
						}
						
						menu.find("input[type='checkbox']").unbind("click").click(function() {
							if ($(this).is(":checked")) {
								core.stats.charts[$(this).attr("data-item")].series[$(this).attr("data-num")].show = true;
								//core.stats.charts[$(this).attr("data-item")].drawSeries({pointLabels:{show:true}}, $(this).attr("data-num"));
							} else {
								core.stats.charts[$(this).attr("data-item")].series[$(this).attr("data-num")].show = false;
								//core.stats.charts[$(this).attr("data-item")].drawSeries({pointLabels:{show:false}}, $(this).attr("data-num"));
							}
							core.stats.charts[$(this).attr("data-item")].replot({resetAxes:true});
						});
						
						// Post options
						switch (data.graph_type) {
							
							case "lines":
								data.options.axes.xaxis.renderer = $.jqplot.DateAxisRenderer;
								data.options.axes.xaxis.min = data.dates_min;
								data.options.axes.xaxis.max = data.dates_max;
								data.options.axes.xaxis.tickOptions = { formatString: "%d %b" };
								//data.options.axes.xaxis.ticks = data.labels;
							break;
							
							case "bars-horizontal":
								data.options.seriesDefaults.renderer = $.jqplot.BarRenderer;
								data.options.seriesDefaults.pointLabels = { show: true, location: "e", edgeTolerance: -15 };
								data.options.seriesDefaults.rendererOptions = { barDirection: "horizontal", shadowOffset: 1 };
								data.options.seriesDefaults.shadowAngle = 135;
								data.options.axes.yaxis.renderer = $.jqplot.CategoryAxisRenderer;
								data.options.axes.yaxis.ticks = data.labels;
								data.options.highlighter.show = false;
							break;
							
						}
						
						core.stats.charts[core.stats.load_stack[core.stats.load_num]] = $.jqplot(target, series, data.options);
						
					}
					
					$("#"+target).hide().fadeIn("slow");
					
					$("#"+target).parents("div.chart").find("div.menu button").unbind("click").click(function() {
						var img = $("#"+$(this).attr("data-item")).jqplotToImageStr({});
						core.dialogs.show({
							'type': "accept", 
							'title': "Guardar imagen", 
							'content': "<p>Haga clic en la imagen con el botón derecho y seleccione 'guardar imagen como' para guardar la gráfica en su disco duro.</p>" + 
													"<img src=\""+img+"\" style=\"width: 100%; height: auto;\" alt=\"\" />"
						});
					}).fadeIn();
					
					core.stats.load_num++;
					core.stats.load();
					
				});
			});
			
		}, 
		
		refresh: function() {
			
			core.stats.options.start = $("div.dates-panel div.range input[name='selector_start']").val();
			core.stats.options.end = $("div.dates-panel div.range input[name='selector_end']").val();
			
			$("div.dates-panel div.action button.refresh").attr("disabled", "disabled");
			
			core.stats.load_num = 0;
			core.stats.load();
			
		}
		
	}, 
	
	// ========================================================
	// DOWNLOADS
	
	downloads: {
		
		list: [], 
		
		init: function() {
			
			$("div.complete button").unbind("click").click(function() {
				core.downloads.download($(this).attr("data-type"));
			});
			
			core.widgets.accordion.init(".accordion");
			
			$(".core-accordion-collapse input[type='checkbox']").removeAttr("checked");
			$(".core-accordion-collapse input[type='number']").val("");
			$(".core-accordion-collapse input[type='text']").val("");
			
			$(".core-accordion-collapse input.form-date").datepicker({
				altFormat: "dd/mm/yy", 
				closeText: "Cerrar", 
				currentText: "Hoy", 
				dateFormat: "dd/mm/yy", 
				dayNames: [ "Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado" ], 
				dayNamesMin: [ "Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa" ], 
				dayNamesShort: [ "Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sab" ], 
				firstDay: 1, 
				monthNames: [ "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre" ], 
				monthNamesShort: [ "Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic" ], 
				nextText: "Siguiente", 
				prevText: "Anterior", 
				showAnim: ""
			});
			
			for (var i in core.downloads.list) {
				core.downloads.filters.init(core.downloads.list[i]);
			}
			
			$("div.list button").unbind("click").click(function() {
				var type = $(this).attr("data-type");
				core.downloads.download(type, core.downloads.filters.list[type]);
			});
			
		}, 
		
		download: function(type, filters) {
			
			$("form[name='download'] input[name='type']").val(type);
			
			if (typeof filters === "undefined") {
				$("form[name='download'] input[name='filters']").val("");
			} else {
				$("form[name='download'] input[name='filters']").val(JSON.stringify(filters));
			}
			
			$("form[name='download']").submit();
			
		}, 
		
		filters: {
			
			list: {}, 
			
			init: function(type) {
				
				core.downloads.filters.list[type] = {};
				
				$("div."+type+" div.filters input[type='checkbox']").unbind("click").click(function() {
					core.downloads.filters.check($(this));
				});
				$("div."+type+" div.filters input.form-date").unbind("change").change(function() {
					core.downloads.filters.check($(this));
				});
				$("div."+type+" div.filters input.form-number").unbind("keyup").keyup(function() {
					core.downloads.filters.check($(this));
				});
				
				core.downloads.filters.show(type);
				
			}, 
			
			check: function(obj) {
				
				var f = obj.attr("data-filter"), 
						r = obj.attr("data-ref"), 
						type = obj.attr("type"), 
						k, v;
				
				if (type == "checkbox") {
					
					k = obj.val();
					
					if (obj.is(":checked")) {
						
						v = $("div."+r+" label[for='filter_"+r+"_"+f+"_"+k+"']").html();
						
					} else {
						
						v = "";
						
					}
					
				} else {
					
					k = obj.attr("data-prefix");
					v = $("div."+r+" label[for='"+obj.attr("id")+"']").html() + " " + obj.val();
					
				}
				
				if (v != "") {
					
					if (typeof core.downloads.filters.list[r][f] === "undefined") {
						core.downloads.filters.list[r][f] = {
							title: $("div[data-target='#filter-"+r+"-"+f+"'] em").html(), 
							type: type, 
							list: {}
						};
					}
					
					core.downloads.filters.list[r][f]['list'][k] = v;
					
				} else {
					
					delete(core.downloads.filters.list[r][f]['list'][k]);
					
					if (aux.object.getLength(core.downloads.filters.list[r][f]['list']) == 0) {
						delete(core.downloads.filters.list[r][f]);
					}
					
				}
				
				core.downloads.filters.show(r);
				
			}, 
			
			show: function(type) {
				
				var count = aux.object.getLength(core.downloads.filters.list[type]);
				
				if (count == 0) {
					
					$("div."+type+" div.list div.load").html("<p>No se ha aplicado ningún filtro.</p>");
					
					$("div."+type+" div.list div.action").slideUp(100);
					
				} else {
					
					var html = "<p>Descargar elementos que solamente incluyan lo siguiente:</p>";
					html += "<ul>";
					for (var f in core.downloads.filters.list[type]) {
						html += "<li><strong>"+core.downloads.filters.list[type][f]['title'] + ": </strong>";
						t = [];
						for (var i in core.downloads.filters.list[type][f]['list']) {
							t.push(core.downloads.filters.list[type][f]['list'][i]);
						}
						html += t.join(", ");
						html += ".</li>";
					}
					html += "</ul>";
					$("div."+type+" div.list div.load").html(html);
					
					$("div."+type+" div.list div.action").slideDown(200);
					
				}
				
			}
			
		}
		
	}, 
	
	// ========================================================
	// UI functions
	
	error: function(text) {
		
		core.dialogs.show({
			type: "error", 
			title: "Error", 
			content: text
		});
		
	}, 
	
	errorUnknown: function() {
		
		core.error("<h4>Se produjo un error desconocido.</h4><p>Por favor, vuelva a intentarlo.</p>");
		
	}, 
	
	// ========================================================
	// Dialog functions
	
	dialogs: {
		
		count: 0, 
		
		show: function(options) {
			
			var buttons;
			
			if (typeof options === "undefined") { return false; }
			if (typeof options.content === "undefined") { return false; }
			
			var type = (typeof options.type === "undefined") ? "alert" : options.type, 
					title = (typeof options.title === "undefined") ? "" : options.title, 
					content = options.content, 
					action = (typeof options.action === "undefined") ? function() { core.dialogs.close($(this).attr("data-num")); } : options.action, 
					onOpen = (typeof options.onOpen === "undefined") ? function(event, ui) {} : options.onOpen, 
					onClose = (typeof options.onClose === "undefined") ? function(event, ui) { $(this).remove(); } : options.onClose, 
					modal = (typeof options.modal === "undefined") ? true : options.modal, 
					draggable = (typeof options.draggable === "undefined") ? false : options.draggable, 
					width = (typeof options.width === "undefined") ? 520 : options.width, 
					height = (typeof options.height === "undefined") ? "auto" : options.height, 
					text_button1 = (typeof options.text_button1 === "undefined") ? ((type == "edit") ? "Guardar" : "Aceptar") : options.text_button1, 
					text_button2 = (typeof options.text_button2 === "undefined") ? "Cancelar" : options.text_button2, 
					classes = "dialog-"+type, 
					buttons;
			
			core.dialogs.count++;
			
			var html = "<div id=\"modal_"+core.dialogs.count+"\" data-num=\""+core.dialogs.count+"\">" + content + "</div>";
			
			$("body").append(html);
			
			switch (type) {
				
				case "confirm":
					buttons = [
										{
											text: text_button1, 
											priority: "primary", 
											'class': "accept", 
											click: action
										}, 
										{
											text: text_button2, 
											priority: "secondary", 
											'class': "cancel", 
											click: function() {
												core.dialogs.close($(this).attr("data-num"));
											}
										}
										];
					classes += " no-close";
				break;
				
				case "edit":
					buttons = [
										{
											text: text_button1, 
											priority: "primary", 
											'class': "accept", 
											click: action
										}, 
										{
											text: text_button2, 
											priority: "secondary", 
											'class': "cancel", 
											count: core.dialogs.count, 
											click: function() {
												core.dialogs.close($(this).attr("data-num"));
											}
										}
										];
					classes += " no-close";
				break;
				
				default:
					buttons = [
										{
											text: text_button1, 
											priority: "primary", 
											'class': "accept", 
											click: function() {
												core.dialogs.close($(this).attr("data-num"));
											}
										}
										];
				break;
				
			}
			
			$("#modal_"+core.dialogs.count).dialog({
				appendTo: "body", 
				autoOpen: true, 
				buttons: buttons, 
				closeOnEscape: (modal) ? false : true, 
				closeText: "Cerrar", 
				dialogClass: classes, 
				draggable: draggable, 
				height: height, 
				hide: 250, 
				maxHeight: 700, 
				maxWidth: 800, 
				minHeight: 150, 
				minWidth: 150, 
				modal: modal, 
				position: { my: "center", at: "center", of: window }, 
				resizable: false, 
				show: 500, 
				title: title, 
				width: width, 
				open: onOpen, 
				close: onClose
			});
			
			return core.dialogs.count;
			
		}, 
		
		close: function(count) {
			
			$("#modal_"+count).dialog("close");
			$(".validation_msg").each(function() {
				$(this).remove();
			});
			
		}, 
		
		closeCurrent: function() {
			
			$("#modal_"+core.dialogs.count).dialog("close");
			
		}, 
		
		exterminate: function() {
			
			$(".ui-dialog-content").dialog("close");
			$(".validation_msg").each(function() {
				$(this).remove();
			});
			
		}, 
		
		refresh: function() {
			
			$(".ui-dialog-content").dialog("option", "position", { my: "center", at: "center", of: window });
			
		}
		
	}, 
	
	// ========================================================
	// Misc Functions
	
	// Locking functions
	
	locking: {
		
		button: {
			
			lock: function(selector, text) {
				
				var b = $(selector);
				
				if (b.is("button") || b.is("a")) {
					
					b.attr({
						'data-text': b.find("span").html(), 
						disabled: "disabled"
					}).css({
						opacity: 0.5
					}).find("span").html(text);
					
				}
				
			}, 
			
			unlock: function(selector) {
				
				var b = $(selector);
				
				if (b.is("button") || b.is("a")) {
					
					b.removeAttr("disabled").css({
						opacity: 1
					}).find("span").html(b.attr("data-text"));
					
				}
				
			}
			
		}, 
		
		dialog: {
			
			lock: function(text, dialog) {
				
				var selector = typeof dialog === "undefined" ? ".ui-dialog" : ".ui-dialog[aria-describedby='modal_"+dialog+"']";
				
				$(selector+" .ui-dialog-buttonpane").append("<div class=\"ui-loading-msg\"><img src=\""+core.cfg.paths.root+"img/anim-loader1.gif\" alt=\"\" /><p>"+text+"</p></div>");
				$(selector+" .ui-dialog-buttonset").css("display", "none");
				$(selector+" .ui-dialog-titlebar-close").css("display", "none");
				$(selector+" .ui-loading-msg").css("display", "block");
				
			}, 
			
			unlock: function(dialog) {
				
				var selector = typeof dialog === "undefined" ? ".ui-dialog" : ".ui-dialog[aria-describedby='modal_"+dialog+"']";
				
				$(selector+" .ui-dialog-buttonset").css("display", "block");
				$(selector+" .ui-dialog-titlebar-close").css("display", "block");
				$(selector+" .ui-loading-msg").remove();
				
			}
			
		}, 
		
		links: {
			
			title: "¡Cuidado!", 
			msg: "<h4>No has guardado los cambios realizados.</h4><p>¿Estás seguro de querer abandonar esta página?</p>", 
			button_yes: "Sí, abandonar la página", 
			button_no: "No, permanecer en la página", 
			target: "", 
			
			lock: function() {
				
				$("a[href]").not(".nocheck").each(function() {
					$(this).unbind("click", core.locking.links.dialog).bind("click", core.locking.links.dialog);
				});
				
				/*window.onbeforeunload = function() {
				  return "¿Seguro que quieres abandonar la página? Los cambios realizados no se guardarán.";
				}*/
				
			}, 
			
			unlock: function() {
				
				$("a").each(function() {
					$(this).unbind("click", core.locking.links.dialog);
				});
				
				//*window.onbeforeunload = null;*/
				
			}, 
			
			dialog: function(e) {
				
				core.locking.links.target = e.target;
				
				e.preventDefault();
				
				core.dialogs.show({
					type: "confirm", 
					title: core.locking.links.title, 
					content: core.locking.links.msg, 
					text_button1: core.locking.links.button_yes, 
					text_button2: core.locking.links.button_no, 
					action: function() {
						core.locking.links.unlock();
						document.location.href = $(core.locking.links.target).attr("href");
					}
				});
				
			}
			
		}, 
		
		pageLoad: {
			
			lock: function(text) {
				
				var t = typeof text === "undefined" ? "Cargando. Por favor, espere..." : text;
				$("body").append("<div class=\"core-loader\"><p>"+t+"</p></div>");
				core.locking.pageLoad.resize();
				
			}, 
			
			resize: function() {
				
				$("div.core-loader").css({
					'width': $(window).outerWidth()+"px", 
					'height': $(window).outerHeight()+"px", 
					'left': $(document).scrollLeft()+"px", 
					'top': $(document).scrollTop()+"px"
				});
				
			}, 
			
			unlock: function() {
				
				$("div.core-loader").remove();
				
			}
			
		}
		
	}, 
	
	// Loader
	
	loader: {
		
		show: function(id) {
			
			var o = $(id);
			$(id+" .core-loader").remove();
			o.css("position", "relative");
			o.append("<div class=\"core-loader\"><p>Cargando...</p></div>");
			var l = $(id+" .core-loader");
			l.css({
				'width': o.outerWidth()+"px", 
				'height': o.outerHeight()+"px", 
				'z-index': "10"
			});
			
		}, 
		
		clear: function(id) {
			$(id+" .core-loader").remove();
		}
		
	}, 
	
};