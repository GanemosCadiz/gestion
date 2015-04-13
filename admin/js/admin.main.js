// ==============================================================
// Main javascript functions
// --------------------------------------------------------------

var main = {
	
	// --------------------------------------------------
	// INIT
	// --------------------------------------------------
	
	init: function(module, section) {
		
		// Core initialization
		core.init();
		
		switch (module) {
			
			case "home":
				
			break;
			
			case "search":
				
			break;
			
		}
		
	}, 
	
	// --------------------------------------------------
	// Help
	
	help: {
		
		show: function(item) {
			
			$.ajax({
				url: core.cfg.paths.root + "help/", 
				type: "POST", 
				dataType: "json", 
				accepts: "json", 
				data: {
					item: item
				}, 
				success: function(data, textStatus, jqXHR) {
					if (data.result == "ok") {
						$("div.ui-dialog-content div.help").html(data.html);
					} else {
						core.error("<h4>Se produjo un error.</h4><p>"+data.error_msg+"</p>");
					}
				}, 
				error: function(jqXHR, textStatus, errorThrown) {
					core.error("<h4>Se produjo un error desconocido.</h4><p>Por favor, vuelva a intentarlo.</p>", jqXHR);
				}
			});
			
			core.dialogs.show({
				type: "accept", 
				title: "Ayuda", 
				width: 800, 
				content: "<div class=\"help\"><div class=\"loading\">Cargando...</div></div>"
			});
			
		}
		
	}
	
};
