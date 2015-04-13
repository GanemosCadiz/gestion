// ==============================================================
// Javascript aux functions
// --------------------------------------------------------------

var aux = {
	
	// Scrolling
	
	scroll: function(target, offset) {
		
		$.smoothScroll({
	    scrollTarget: target, 
	    offset: (typeof offset !== "undefined") ? offset : 0, 
	    speed: 1000
	  });
	  
	}, 
	
	// History
	
	history: {
		
		back: function() {
			history.go(-1);
		}
		
	}, 
	
	// Validation functions
			
	validation: {
		
		isNumber: function(value) {
			return !isNaN(parseFloat(value)) && isFinite(value);
		}, 
		
		email: function(value) {
			return /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(value);
		}, 
		
		date: function(value) {
			if (value.length != 10) {
				return false;
			} else if (value.indexOf("-") == -1) {
				return false;
			} else {
				var s = (value != "") ? value.split("-") : [];
				if (s.length != 3) {
					return false;
				} else {
					if (s[0].length != 4 || !aux.validation.isNumber(s[0])) {
						return false;
					} else if (s[1].length != 2 || !aux.validation.isNumber(s[1])) {
						return false;
					} else if (s[2].length != 2 || !aux.validation.isNumber(s[2])) {
						return false;
					}
				}
			}
			return true;
		}, 
		
		dateTime: function(value) {
			if (value.length != 19) {
				return false;
			} else if (value.indexOf(" ") != 10) {
				return false;
			} else {
				var t = (value != "") ? value.split(" ") : [];
				if (!aux.validation.date(t[0])) {
					return false;
				} else {
					if (t[1].length != 8) {
						return false;
					} else if (t[1].indexOf(":") == -1) {
						return false;
					} else {
						var t = (t[1] != "") ? t[1].split(":") : [];
						if (t.length != 3) {
							return false;
						}
					}
				}
			}
			return true;
		}, 
		
		validChars: function(value, valid) {
			var c, i;
			for (i=0; i<value.length; i++) {
				c = value.charAt(i);
				if (valid.indexOf(c) == -1) {
					return false;
				}
			}
			return true;
		}, 
		
		url: function(value) {
		  var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
		  '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name
		  '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
		  '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
		  '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
		  '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
		  if(!pattern.test(value)) {
		    return false;
		  } else {
		    return true;
		  }
		}, 
		
		textOnly: function(value) {
			return !/^\d+$/.test(value);
		}, 
		
		numberOnly: function(value) {
			return /^\d+$/.test(value);
		}, 
		
		lengthIs: function(value, len) {
			len = (typeof len === "undefined" || len == "") ? 0 : len;
			return value.length == parseInt(len);
		}, 
		
		lengthMin: function(value, len) {
			len = (typeof len === "undefined" || len == "") ? 0 : len;
			return value.length >= parseInt(len);
		}, 
		
		lengthMax: function(value, len) {
			len = (typeof len === "undefined" || len == "") ? 0 : len;
			return value.length <= parseInt(len);
		}, 
		
		postal: function(value) {
			return 	aux.validation.numberOnly(value) && 
							aux.validation.lengthIs(value, 5);
		}, 
		
		phone9: function(value) {
			return 	aux.validation.numberOnly(value) && 
							aux.validation.lengthIs(value, 9) && 
							(value.charAt(0) == "9" || value.charAt(0) == "7" || value.charAt(0) == "8" || value.charAt(0) == "6") && 
							value.substr(0, 3) != "800" && value.substr(0, 3) != "900" && value.substr(0, 3) != "901" && value.substr(0, 3) != "902" && value.substr(0, 3) != "905" && value.substr(0, 3) != "908" && value.substr(0, 3) != "909";
		}, 
		
		nif: function(value) {
			var ok = true;
			var temp = value.toUpperCase();
			var cadenadni = "TRWAGMYFPDXBNJZSQVHLCKE";
			if (temp !== '') {
				//algoritmo para comprobacion de codigos tipo CIF
				suma = parseInt(temp[2]) + parseInt(temp[4]) + parseInt(temp[6]);
				for (i = 1; i < 8; i += 2) {
					temp1 = 2 * parseInt(temp[i]);
					temp1 += '';
					temp1 = temp1.substring(0,1);
					temp2 = 2 * parseInt(temp[i]);
					temp2 += '';
					temp2 = temp2.substring(1,2);
					if (temp2 == '') {
						temp2 = '0';
					}
					suma += (parseInt(temp1) + parseInt(temp2));
				}
				suma += '';
				n = 10 - parseInt(suma.substring(suma.length-1, suma.length));
				//si no tiene un formato valido devuelve error
				if ((!/^[A-Z]{1}[0-9]{7}[A-Z0-9]{1}$/.test(temp) && !/^[T]{1}[A-Z0-9]{8}$/.test(temp)) && !/^[0-9]{8}[A-Z]{1}$/.test(temp)) {
					if ((temp.length == 9) && (/^[0-9]{9}$/.test(temp))) {
						var posicion = temp.substring(8,0) % 23;
						var letra = cadenadni.charAt(posicion);
						var letradni = temp.charAt(8);
						ok = false;
					} else if (temp.length == 8) {
						if (/^[0-9]{1}/.test(temp)) {
							var posicion = temp.substring(8,0) % 23;
							var letra = cadenadni.charAt(posicion);
							var tipo = 'NIF';
						} else if (/^[KLM]{1}/.test(temp)) {
							var letra = String.fromCharCode(64 + n);
							var tipo = 'NIF';
						} else if (/^[ABCDEFGHJNPQRSUVW]{1}/.test(temp)) {
							var letra = String.fromCharCode(64 + n);
							var tipo = 'CIF';
						} else if (/^[T]{1}/.test(temp)) {
							var letra = String.fromCharCode(64 + n);
							var tipo = 'NIE';
						} else if (/^[XYZ]{1}/.test(temp)) {
							var pos = aux.string.replace(['X', 'Y', 'Z'], ['0','1','2'], temp).substring(0, 8) % 23;
							var letra = cadenadni.substring(pos, pos + 1);
							var tipo = 'NIE';
						}
						if (letra !== '') {
							ok = false;
						} else {
							ok = false;
						}
					} else if (temp.length < 8) {
						ok = false;
					} else {
						ok = false;
					}
				} else if (/^[0-9]{8}[A-Z]{1}$/.test(temp)) {
					//comprobacion de NIFs estandar
					var posicion = temp.substring(8,0) % 23;
					var letra = cadenadni.charAt(posicion);
					var letradni = temp.charAt(8);
					if (letra == letradni) {
						return ok;
					} else if (letra != letradni) {
						ok = false;
					} else {
						ok = false;
					}
				} else if (/^[KLM]{1}/.test(temp)) {
					//comprobacion de NIFs especiales (se calculan como CIFs)
					if (temp[8] == String.fromCharCode(64 + n)) {
						return ok;
					} else if (temp[8] != String.fromCharCode(64 + n)) {
						ok = false;
					} else {
						ok = false;
					}
				} else if (/^[ABCDEFGHJNPQRSUVW]{1}/.test(temp)) {
					//comprobacion de CIFs
					var temp_n = n + '';
					if (temp[8] == String.fromCharCode(64 + n) || temp[8] == parseInt(temp_n.substring(temp_n.length-1, temp_n.length))) {
						return ok;
					} else if (temp[8] != String.fromCharCode(64 + n)) {
						ok = false;
					} else if (temp[8] != parseInt(temp_n.substring(temp_n.length-1, temp_n.length))) {
						ok = false;
					} else {
						ok = false;
					}
				} else if (/^[T]{1}/.test(temp)) {
					//comprobacion de NIEs
					//T
					if (temp[8] == /^[T]{1}[A-Z0-9]{8}$/.test(temp)) {
						return ok;
					} else if (temp[8] != /^[T]{1}[A-Z0-9]{8}$/.test(temp)) {
						var letra = String.fromCharCode(64 + n);
						var letranie = temp.charAt(8);
						if (letra != letranie) {
							ok = false;
						} else {
							ok = false;
						}
					}
				} else if (/^[XYZ]{1}/.test(temp)) {
					//XYZ
					var pos = aux.string.replace(['X', 'Y', 'Z'], ['0','1','2'], temp).substring(0, 8) % 23;
					var letra = cadenadni.substring(pos, pos + 1);
					var letranie = temp.charAt(8);
					if (letranie == letra) {
						return ok;
					} else if (letranie != letra) {
						ok = false;
					} else {
						ok = false;
					}
				}
			} else {
				ok = false;
			}
			return ok;
		}
		
	}, 
	
	// ......................................................
	// AUX
	
	// Url functions
	
	url: {
		
		addParam: function(url, param, value) {
			
			if (typeof url !== "string") {
				url = url.toString();
			}
			
			var t = (url != "") ? url.split("?") : [];
			
			var path = t[0], 
					query = (typeof t[1] === "undefined") ? "" : t[1];
			
			var params = aux.url.parseQuery(query);
			
			var v = (typeof value === "undefined" || value == "") ? "" : value;
			
			params[escape(param)] = escape(v);
			
			result_query = [];
			
			for (var i in params) {
				if (params[i] == "") {
					result_query.push(i);
				} else {
					result_query.push(i+"="+params[i]);
				}
			}
			
			return path + "?" + result_query.join("&");
			
		}, 
		
		parseQuery: function(query) {
			// http://feather.elektrum.org/book/src.html
			// modified for supporting empty params
			var Params = new Object();
			if (!query) return Params;
			var Pairs = (query != "") ? query.split(/[;&]/) : [];
			for (var i=0; i<Pairs.length; i++) {
				var KeyVal = (Pairs[i] != "") ? Pairs[i].split('=') : [];
				if (!KeyVal) continue;
				var key = unescape(KeyVal[0]);
				var val = (typeof KeyVal[1] !== "undefined") ? unescape(KeyVal[1]) : "";
				val = val.replace(/\+/g, ' ');
				Params[key] = val;
			}
			return Params;
		}
		
	}, 
	
	// Array functions
	
	array: {
		
		add: function(arr, v) {
			if (!aux.array.in_array(v, arr)) {
				arr.push(v);
			}
			return arr;
		}, 
		
		remove: function(arr, v) {
			var p;
			for (var i in arr) {
				if (arr[i] == v) {
					pos = i;
				}
			}
			if (typeof pos !== "undefined") {
				arr.splice(pos, 1);
			}
			return arr;
		}, 
		
		in_array: function(needle, haystack, argStrict) {
		  // From: http://phpjs.org/functions
		  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		  // +   improved by: vlado houba
		  // +   input by: Billy
		  // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
		  // *     example 1: in_array('van', ['Kevin', 'van', 'Zonneveld']);
		  // *     returns 1: true
		  // *     example 2: in_array('vlado', {0: 'Kevin', vlado: 'van', 1: 'Zonneveld'});
		  // *     returns 2: false
		  // *     example 3: in_array(1, ['1', '2', '3']);
		  // *     returns 3: true
		  // *     example 3: in_array(1, ['1', '2', '3'], false);
		  // *     returns 3: true
		  // *     example 4: in_array(1, ['1', '2', '3'], true);
		  // *     returns 4: false
		  var key = '',
		    strict = !! argStrict;
		
		  if (strict) {
		    for (key in haystack) {
		      if (haystack[key] === needle) {
		        return true;
		      }
		    }
		  } else {
		    for (key in haystack) {
		      if (haystack[key] == needle) {
		        return true;
		      }
		    }
		  }
		
		  return false;
		}, 
		
		firstKey: function(arr) {
			for (var i in arr)
		    return i;
		}, 
		
		firstValue: function(arr) {
			for (var i in arr)
		    return arr[i];
		}
		
	}, 
	
	// String functions
	
	string: {
		
		trim: function(str, charlist) {
		  // From: http://phpjs.org/functions
		  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		  // +   improved by: mdsjack (http://www.mdsjack.bo.it)
		  // +   improved by: Alexander Ermolaev (http://snippets.dzone.com/user/AlexanderErmolaev)
		  // +      input by: Erkekjetter
		  // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		  // +      input by: DxGx
		  // +   improved by: Steven Levithan (http://blog.stevenlevithan.com)
		  // +    tweaked by: Jack
		  // +   bugfixed by: Onno Marsman
		  // *     example 1: trim('    Kevin van Zonneveld    ');
		  // *     returns 1: 'Kevin van Zonneveld'
		  // *     example 2: trim('Hello World', 'Hdle');
		  // *     returns 2: 'o Wor'
		  // *     example 3: trim(16, 1);
		  // *     returns 3: 6
		  var whitespace, l = 0,
		    i = 0;
		  str += '';
		
		  if (!charlist) {
		    // default list
		    whitespace = " \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
		  } else {
		    // preg_quote custom list
		    charlist += '';
		    whitespace = charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '$1');
		  }
		
		  l = str.length;
		  for (i = 0; i < l; i++) {
		    if (whitespace.indexOf(str.charAt(i)) === -1) {
		      str = str.substring(i);
		      break;
		    }
		  }
		
		  l = str.length;
		  for (i = l - 1; i >= 0; i--) {
		    if (whitespace.indexOf(str.charAt(i)) === -1) {
		      str = str.substring(0, i + 1);
		      break;
		    }
		  }
		
		  return whitespace.indexOf(str.charAt(0)) === -1 ? str : '';
		}, 
		
		isNumber: function(n) {
			// http://stackoverflow.com/a/1830844
	  	return !isNaN(parseFloat(n)) && isFinite(n);
		}, 
		
		addSlashes: function(str) {
		  // From: http://phpjs.org/functions
		  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		  // +   improved by: Ates Goral (http://magnetiq.com)
		  // +   improved by: marrtins
		  // +   improved by: Nate
		  // +   improved by: Onno Marsman
		  // +   input by: Denny Wardhana
		  // +   improved by: Brett Zamir (http://brett-zamir.me)
		  // +   improved by: Oskar Larsson Högfeldt (http://oskar-lh.name/)
		  // *     example 1: addslashes("kevin's birthday");
		  // *     returns 1: 'kevin\'s birthday'
		  return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
		}, 
		
		stripSlashes: function(str) {
		  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		  // +   improved by: Ates Goral (http://magnetiq.com)
		  // +      fixed by: Mick@el
		  // +   improved by: marrtins
		  // +   bugfixed by: Onno Marsman
		  // +   improved by: rezna
		  // +   input by: Rick Waldron
		  // +   reimplemented by: Brett Zamir (http://brett-zamir.me)
		  // +   input by: Brant Messenger (http://www.brantmessenger.com/)
		  // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
		  // *     example 1: stripslashes('Kevin\'s code');
		  // *     returns 1: "Kevin's code"
		  // *     example 2: stripslashes('Kevin\\\'s code');
		  // *     returns 2: "Kevin\'s code"
		  return (str + '').replace(/\\(.?)/g, function (s, n1) {
		    switch (n1) {
		    case '\\':
		      return '\\';
		    case '0':
		      return '\u0000';
		    case '':
		      return '';
		    default:
		      return n1;
		    }
		  });
		}, 
		
		replace: function(search, replace, subject, count) {
		  // From: http://phpjs.org/functions
		  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		  // +   improved by: Gabriel Paderni
		  // +   improved by: Philip Peterson
		  // +   improved by: Simon Willison (http://simonwillison.net)
		  // +    revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
		  // +   bugfixed by: Anton Ongson
		  // +      input by: Onno Marsman
		  // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		  // +    tweaked by: Onno Marsman
		  // +      input by: Brett Zamir (http://brett-zamir.me)
		  // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		  // +   input by: Oleg Eremeev
		  // +   improved by: Brett Zamir (http://brett-zamir.me)
		  // +   bugfixed by: Oleg Eremeev
		  // %          note 1: The count parameter must be passed as a string in order
		  // %          note 1:  to find a global variable in which the result will be given
		  // *     example 1: str_replace(' ', '.', 'Kevin van Zonneveld');
		  // *     returns 1: 'Kevin.van.Zonneveld'
		  // *     example 2: str_replace(['{name}', 'l'], ['hello', 'm'], '{name}, lars');
		  // *     returns 2: 'hemmo, mars'
		  var i = 0,
		    j = 0,
		    temp = '',
		    repl = '',
		    sl = 0,
		    fl = 0,
		    f = [].concat(search),
		    r = [].concat(replace),
		    s = subject,
		    ra = Object.prototype.toString.call(r) === '[object Array]',
		    sa = Object.prototype.toString.call(s) === '[object Array]';
		  s = [].concat(s);
		  if (count) {
		    this.window[count] = 0;
		  }
		
		  for (i = 0, sl = s.length; i < sl; i++) {
		    if (s[i] === '') {
		      continue;
		    }
		    for (j = 0, fl = f.length; j < fl; j++) {
		      temp = s[i] + '';
		      repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
		      s[i] = (temp).split(f[j]).join(repl);
		      if (count && s[i] !== temp) {
		        this.window[count] += (temp.length - s[i].length) / f[j].length;
		      }
		    }
		  }
		  return sa ? s : s[0];
		}, 
		
		Base64: {
			
			/**
			*
			*  Base64 encode / decode
			*  http://www.webtoolkit.info/
			*
			**/
			
			// private property
			_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
			
			// public method for encoding
			encode : function (input) {
			    var output = "";
			    var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
			    var i = 0;
			
			    input = aux.string.Base64._utf8_encode(input);
			
			    while (i < input.length) {
			
			        chr1 = input.charCodeAt(i++);
			        chr2 = input.charCodeAt(i++);
			        chr3 = input.charCodeAt(i++);
			
			        enc1 = chr1 >> 2;
			        enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			        enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			        enc4 = chr3 & 63;
			
			        if (isNaN(chr2)) {
			            enc3 = enc4 = 64;
			        } else if (isNaN(chr3)) {
			            enc4 = 64;
			        }
			
			        output = output +
			        aux.string.Base64._keyStr.charAt(enc1) + aux.string.Base64._keyStr.charAt(enc2) +
			        aux.string.Base64._keyStr.charAt(enc3) + aux.string.Base64._keyStr.charAt(enc4);
			
			    }
			
			    return output;
			},
			
			// public method for decoding
			decode : function (input) {
			    var output = "";
			    var chr1, chr2, chr3;
			    var enc1, enc2, enc3, enc4;
			    var i = 0;
			
			    input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
			
			    while (i < input.length) {
			
			        enc1 = aux.string.Base64._keyStr.indexOf(input.charAt(i++));
			        enc2 = aux.string.Base64._keyStr.indexOf(input.charAt(i++));
			        enc3 = aux.string.Base64._keyStr.indexOf(input.charAt(i++));
			        enc4 = aux.string.Base64._keyStr.indexOf(input.charAt(i++));
			
			        chr1 = (enc1 << 2) | (enc2 >> 4);
			        chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			        chr3 = ((enc3 & 3) << 6) | enc4;
			
			        output = output + String.fromCharCode(chr1);
			
			        if (enc3 != 64) {
			            output = output + String.fromCharCode(chr2);
			        }
			        if (enc4 != 64) {
			            output = output + String.fromCharCode(chr3);
			        }
			
			    }
			
			    output = aux.string.Base64._utf8_decode(output);
			
			    return output;
			
			},
			
			// private method for UTF-8 encoding
			_utf8_encode : function (string) {
			    string = string.replace(/\r\n/g,"\n");
			    var utftext = "";
			
			    for (var n = 0; n < string.length; n++) {
			
			        var c = string.charCodeAt(n);
			
			        if (c < 128) {
			            utftext += String.fromCharCode(c);
			        }
			        else if((c > 127) && (c < 2048)) {
			            utftext += String.fromCharCode((c >> 6) | 192);
			            utftext += String.fromCharCode((c & 63) | 128);
			        }
			        else {
			            utftext += String.fromCharCode((c >> 12) | 224);
			            utftext += String.fromCharCode(((c >> 6) & 63) | 128);
			            utftext += String.fromCharCode((c & 63) | 128);
			        }
			
			    }
			
			    return utftext;
			},
			
			// private method for UTF-8 decoding
			_utf8_decode : function (utftext) {
			    var string = "";
			    var i = 0;
			    var c = c1 = c2 = 0;
			
			    while ( i < utftext.length ) {
			
			        c = utftext.charCodeAt(i);
			
			        if (c < 128) {
			            string += String.fromCharCode(c);
			            i++;
			        }
			        else if((c > 191) && (c < 224)) {
			            c2 = utftext.charCodeAt(i+1);
			            string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
			            i += 2;
			        }
			        else {
			            c2 = utftext.charCodeAt(i+1);
			            c3 = utftext.charCodeAt(i+2);
			            string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
			            i += 3;
			        }
			
			    }
			
			    return string;
			}
			
		}
		
	}, 
	
	// String functions
	
	number: {
		
		number_format: function(number, decimals, dec_point, thousands_sep) {
		  //  discuss at: http://phpjs.org/functions/number_format/
		  // original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
		  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		  // improved by: davook
		  // improved by: Brett Zamir (http://brett-zamir.me)
		  // improved by: Brett Zamir (http://brett-zamir.me)
		  // improved by: Theriault
		  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		  // bugfixed by: Michael White (http://getsprink.com)
		  // bugfixed by: Benjamin Lupton
		  // bugfixed by: Allan Jensen (http://www.winternet.no)
		  // bugfixed by: Howard Yeend
		  // bugfixed by: Diogo Resende
		  // bugfixed by: Rival
		  // bugfixed by: Brett Zamir (http://brett-zamir.me)
		  //  revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
		  //  revised by: Luke Smith (http://lucassmith.name)
		  //    input by: Kheang Hok Chin (http://www.distantia.ca/)
		  //    input by: Jay Klehr
		  //    input by: Amir Habibi (http://www.residence-mixte.com/)
		  //    input by: Amirouche
		  //   example 1: number_format(1234.56);
		  //   returns 1: '1,235'
		  //   example 2: number_format(1234.56, 2, ',', ' ');
		  //   returns 2: '1 234,56'
		  //   example 3: number_format(1234.5678, 2, '.', '');
		  //   returns 3: '1234.57'
		  //   example 4: number_format(67, 2, ',', '.');
		  //   returns 4: '67,00'
		  //   example 5: number_format(1000);
		  //   returns 5: '1,000'
		  //   example 6: number_format(67.311, 2);
		  //   returns 6: '67.31'
		  //   example 7: number_format(1000.55, 1);
		  //   returns 7: '1,000.6'
		  //   example 8: number_format(67000, 5, ',', '.');
		  //   returns 8: '67.000,00000'
		  //   example 9: number_format(0.9, 0);
		  //   returns 9: '1'
		  //  example 10: number_format('1.20', 2);
		  //  returns 10: '1.20'
		  //  example 11: number_format('1.20', 4);
		  //  returns 11: '1.2000'
		  //  example 12: number_format('1.2000', 3);
		  //  returns 12: '1.200'
		  //  example 13: number_format('1 000,50', 2, '.', ' ');
		  //  returns 13: '100 050.00'
		  //  example 14: number_format(1e-8, 8, '.', '');
		  //  returns 14: '0.00000001'
		
		  number = (number + '')
		    .replace(/[^0-9+\-Ee.]/g, '');
		  var n = !isFinite(+number) ? 0 : +number,
		    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
		    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
		    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
		    s = '',
		    toFixedFix = function(n, prec) {
		      var k = Math.pow(10, prec);
		      return '' + (Math.round(n * k) / k)
		        .toFixed(prec);
		    };
		  // Fix for IE parseFloat(0.55).toFixed(0) = 0;
		  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
		    .split('.');
		  if (s[0].length > 3) {
		    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		  }
		  if ((s[1] || '')
		    .length < prec) {
		    s[1] = s[1] || '';
		    s[1] += new Array(prec - s[1].length + 1)
		      .join('0');
		  }
		  return s.join(dec);
		}
		
	}, 
	
	// Object functions
	
	object: {
		
		hasOwnProperty: Object.prototype.hasOwnProperty, 
		
		isEmpty: function(obj) {
			// http://stackoverflow.com/questions/4994201/is-object-empty
	    // null and undefined are "empty"
	    if (obj == null) return true;
	
	    // Assume if it has a length property with a non-zero value
	    // that that property is correct.
	    if (obj.length > 0)    return false;
	    if (obj.length === 0)  return true;
	
	    // Otherwise, does it have any properties of its own?
	    // Note that this doesn't handle
	    // toString and valueOf enumeration bugs in IE < 9
	    for (var key in obj) {
	        if (aux.object.hasOwnProperty.call(obj, key)) return false;
	    }
	
	    return true;
		}, 
		
		getLength: function(obj) {
			
			var count = 0;
			
			for (var i in obj) {
				if (obj.hasOwnProperty(i)) {
					count++;
				}
			}
			
			return count;
			
		}
		
	}
	
};