// ==============================================================
// Javascript login functions
// --------------------------------------------------------------

var core = {
	
	cfg: {
		
		paths: {
			root: ""
		}, 
		
		app: {
			module: "", 
			section: "", 
			action: ""
		}
		
	}
	
};

var login = {
	
	init: function() {
		
		$("#login form").submit(function(event) {
			event.preventDefault();
			login.send();
		});
		
	}, 
	
	send: function() {
		
		login.msgClear();
		login.errorClear();
		
		var username = $("#i_username").val(), 
				password = $("#i_password").val(), 
				valid = true;
		
		if (username == "") {
			login.msgShow("i_username");
			valid = false;
		}
		if (password == "") {
			login.msgShow("i_password");
			valid = false;
		}
		
		if (valid) {
			
			login.buttonLock("Accediendo...");
			
			$.ajax({
				url: core.cfg.paths.root + "login/", 
				type: "POST", 
				dataType: "json", 
				accepts: "json", 
				data: {
					username: username, 
					password: password
				}, 
				success: function(data, textStatus, jqXHR) {
					if (data.result == "ok") {
						document.location.href = core.cfg.paths.root + "home/";
					} else {
						login.errorShow(data.error_msg);
					}
				}, 
				error: function(jqXHR, textStatus, errorThrown) {
					login.errorFatal("<h4>Se produjo un error desconocido al acceder a la aplicación.</h4><p>Por favor, vuelva a intentarlo.</p>");
				}
			});
			
		}
		
		return false;
		
	}, 
	
	msgShow: function(field) {
		$("#"+field).addClass("has-error");
	}, 
	
	msgClear: function() {
		$("input").removeClass("has-error");
	}, 
	
	buttonLock: function() {
		$("#login form button")
					.attr("disabled", "disabled")
					.attr("data-text", $("#login form button").text())
					.text("Accediendo...");
	}, 
	
	buttonUnlock: function() {
		$("#login form button")
			.text($("#login form button").attr("data-text"))
			.removeAttr("disabled");
	}, 
	
	errorShow: function(text) {
		login.buttonUnlock("#login form button");
		$("#login .alert").removeClass("alert-warning alert-danger").addClass("alert-warning");
		$("#login .alert").css("display", "none").html(text).slideDown(300);
	}, 
	
	errorFatal: function(text) {
		login.buttonUnlock("#login form button");
		$("#login .alert").removeClass("alert-warning alert-danger").addClass("alert-danger");
		$("#login .alert").css("display", "none").html(text).slideDown(300);
	}, 
	
	errorClear: function() {
		$("#login .alert").slideUp(100);
	}
	
};