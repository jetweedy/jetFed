
function parseJSON(j) {
	var r = new Array();
	try {
		r = eval('('+j+')');
	}
	catch(er) {
		return r;
	}
	return r;
}

// ----------------------------------------------------------------------------------

function ajax_trim(str, chars) {
    return ajax_ltrim(ajax_rtrim(str, chars), chars);
}

function ajax_ltrim(str, chars) {
    chars = chars || "\\s";
    return str.replace(new RegExp("^[" + chars + "]+", "g"), "");
}

function ajax_rtrim(str, chars) {
    chars = chars || "\\s";
    return str.replace(new RegExp("[" + chars + "]+$", "g"), "");
}

// ----------------------------------------------------------------------------------

function GetXmlHttpObject() {
  var xmlHttp=null;
  try {
    // Firefox, Opera 8.0+, Safari, etc
    xmlHttp=new XMLHttpRequest();
  }
  catch (e) {
    // Internet Explorer
    try {
      xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
    }
    catch (e) {
      xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
  }
  return xmlHttp;
}

// ----------------------------------------------------------------------------------

function ajax(method,url,params,callback,scope) {
	var scope = (scope==undefined | !scope) ? window : scope;
	var xmlhttp = new XMLHttpRequest();
	var async = (callback!=undefined && !!callback);
	if (async) {
		xmlhttp.onreadystatechange = function() {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				callback.apply(scope,[ajax_trim(xmlhttp.responseText)]);
			}
		}
	}
	if (method=="POST") {
		xmlhttp.open("POST", url, async);
		xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
//		xmlhttp.setRequestHeader("Content-length", params.length);
//		xmlhttp.setRequestHeader("Connection", "close");
		xmlhttp.send(params);
	} else {
		xmlhttp.open("GET",url+"?"+params,async);
		xmlhttp.send();
	}
	return ajax_trim(xmlhttp.responseText);
}


// ----------------------------------------------------------------------------------

function applyCallbacks(callbacks,x,b) {
	console.log(callbacks, x, b);
	for (var c=0;c<callbacks.length;c++) {
		var b = (typeof b == "undefined") ? true : b;
		var data = callbacks[c].parse ? parseJSON(x) : x;
		var important = (typeof callbacks[c].important == "undefined") ? false : callbacks[c].important;
		if (b || important) {
			callbacks[c].callback.apply(callbacks[c].scope,[data]);
		}
	}
}

function ajaxPlus(options) {
	options.parse = (typeof options.parse=="undefined" || !options.parse) ? false : true;
	options.method = (typeof options.method=="undefined" || !options.method) ? "POST" : options.method;
	options.url = (typeof options.url=="undefined" || !options.url) ? "" : options.url;
	options.params = (typeof options.params=="undefined" || !options.params) ? "" : options.params;
	options.callbacks = (typeof options.callbacks=="undefined" || !options.callbacks) ? [] : options.callbacks;
	options.onError = (typeof options.onError=="undefined" || !options.onError) ? function(e){} : options.onError;
	options.checkRelevance = (typeof options.checkRelevance=="undefined" || !options.checkRelevance) ? function(){} : options.checkRelevance;
	var xmlhttp = new XMLHttpRequest();
	var async = !(typeof options.callbacks=="undefined" || !options.callbacks || options.callbacks.length<1);
	if (async) {
		xmlhttp.onreadystatechange = function() {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
console.log("options.checkRelevance: ", options.checkRelevance);
				var runcallbacks = options.checkRelevance();
				var json = ajax_trim(xmlhttp.responseText);
				var data = parseJSON(json);
				if (typeof data.error != "undefined") {
					options.onError.apply(window, [data.error]);
				} else {
console.log("runcallbacks: ", runcallbacks);
					applyCallbacks(options.callbacks,json,runcallbacks);
				}
			}
		}
	} else {
		xmlhttp.onreadystatechange = function() {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				return xmlhttp.responseText;
			}
		}
	}
	if (options.method=="POST") {
		xmlhttp.open("POST", options.url, async);
		xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xmlhttp.setRequestHeader("Content-length", options.params.length);
		xmlhttp.setRequestHeader("Connection", "close");
		xmlhttp.send(options.params);
	} else {
		xmlhttp.open("GET",options.url+"?"+options.params,async);
		xmlhttp.send();
	}
	return ajax_trim(xmlhttp.responseText);
}


// ----------------------------------------------------------------------------------------
// USEFUL JSON/OBJECT MANIPULATION FUNCTIONS
// http://www.sitepoint.com/javascript-json-serialization/#.T_HibfWWjbg
// ----------------------------------------------------------------------------------------

var JSON = JSON || {};

//// implement JSON.stringify serialization  
//JSON.stringify = JSON.stringify || function (obj) {  
JSON.stringify = function (obj) {  
	var t = typeof (obj);  
	if (t != "object" || obj === null) {  
		// simple data type  
		if (t == "string") obj = '"'+obj+'"';  
		return String(obj);  
	}  
	else {  
		// recurse array or object  
		var n, v, json = [], arr = (obj && obj.constructor == Array);  
		for (n in obj) {  
			v = obj[n]; t = typeof(v);  
			if (t == "string") v = '"'+v+'"';  
			else if (t == "object" && v !== null) v = JSON.stringify(v);  
			json.push((arr ? "" : '"' + n + '":') + String(v));  
		}  
		return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");  
	}  
};  

// implement JSON.parse de-serialization  
JSON.parse = JSON.parse || function (str) {  
	if (str === "") str = '""';  
	eval("var p=" + str + ";");  
	return p;  
};  


// --------------------------------------------
// Added by jetweedy
// --------------------------------------------
JSON.clone = function(json, proto) {
	var r = JSON.parse(json);
	if (!proto) { proto = false; } else {
		for (var i in proto) {
			r[i] = proto[i];
		}
	}
	return r;
}

// ----------------------------------------------------------------------------------------
