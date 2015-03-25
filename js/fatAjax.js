var Fat = {
	make_hex : function (r,g,b) 
	{
		r = r.toString(16); if (r.length == 1) r = '0' + r;
		g = g.toString(16); if (g.length == 1) g = '0' + g;
		b = b.toString(16); if (b.length == 1) b = '0' + b;
		return "#" + r + g + b;
	},
	fade_all : function ()
	{
		var a = document.getElementsByTagName("*");
		for (var i = 0; i < a.length; i++) 
		{
			var o = a[i];
			var r = /fade-?(\w{3,6})?/.exec(o.className);
			if (r)
			{
				if (!r[1]) r[1] = "";
				if (o.id) Fat.fade_element(o.id,null,null,"#"+r[1]);
			}
		}
	},
	fade_element : function (id, fps, duration, from, to) 
	{
		if (!fps) fps = 30;
		if (!duration) duration = 3000;
		if (!from || from=="#") from = "#FFFF33";
		if (!to) to = this.get_bgcolor(id);
		
		var frames = Math.round(fps * (duration / 1000));
		var interval = duration / frames;
		var delay = interval;
		var frame = 0;
		
		if (from.length < 7) from += from.substr(1,3);
		if (to.length < 7) to += to.substr(1,3);
		
		var rf = parseInt(from.substr(1,2),16);
		var gf = parseInt(from.substr(3,2),16);
		var bf = parseInt(from.substr(5,2),16);
		var rt = parseInt(to.substr(1,2),16);
		var gt = parseInt(to.substr(3,2),16);
		var bt = parseInt(to.substr(5,2),16);
		
		var r,g,b,h;
		while (frame < frames)
		{
			r = Math.floor(rf * ((frames-frame)/frames) + rt * (frame/frames));
			g = Math.floor(gf * ((frames-frame)/frames) + gt * (frame/frames));
			b = Math.floor(bf * ((frames-frame)/frames) + bt * (frame/frames));
			h = this.make_hex(r,g,b);
		
			setTimeout("Fat.set_bgcolor('"+id+"','"+h+"')", delay);

			frame++;
			delay = interval * frame; 
		}
		setTimeout("Fat.set_bgcolor('"+id+"','"+to+"')", delay);
	},
	set_bgcolor : function (id, c)
	{
		var o = document.getElementById(id);
		o.style.backgroundColor = c;
	},
	get_bgcolor : function (id)
	{
		var o = document.getElementById(id);
		while(o)
		{
			var c;
			if (window.getComputedStyle) c = window.getComputedStyle(o,null).getPropertyValue("background-color");
			if (o.currentStyle) c = o.currentStyle.backgroundColor;
			if ((c != "" && c != "transparent") || o.tagName == "BODY") { break; }
			o = o.parentNode;
		}
		if (c == undefined || c == "" || c == "transparent") c = "#FFFFFF";
		var rgb = c.match(/rgb\s*\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*\)/);
		if (rgb) c = this.make_hex(parseInt(rgb[1]),parseInt(rgb[2]),parseInt(rgb[3]));
		return c;
	}
}

// XHTML live Chat
// author: alexander kohlhofer
// version: 1.0
// http://www.plasticshore.com
// http://www.plasticshore.com/projects/chat/
// please let the author know if you put any of this to use
// XHTML live Chat (including this script) is published under a creative commons license
// license: http://creativecommons.org/licenses/by-nc-sa/2.0/

var jal_loadtimes;
var httpReceiveChat;
var httpSendChat;

var theDate = new Date();
var expiryDate = new Date( theDate.getTime() + 5184000000 ).toGMTString();

///////////////////////////////////////
//
//  Generic onload by Brothercake
//  http://www.brothercake.com/
//
///////////////////////////////////////

//onload function

//setup onload function
if(typeof window.addEventListener != 'undefined')
{
	//.. gecko, safari, konqueror and standard
	window.addEventListener('load', initJavaScript, false);
}
else if(typeof document.addEventListener != 'undefined')
{
	//.. opera 7
	document.addEventListener('load', initJavaScript, false);
}
else if(typeof window.attachEvent != 'undefined')
{
	//.. win/ie

	window.attachEvent('onload', initJavaScript);
}

	

function initJavaScript() {
	if (!document.getElementById('chatbarText')) { return; }
	document.forms['chatForm'].elements['chatbarText'].setAttribute('autocomplete','off'); //this non standard attribute prevents firefox' autofill function to clash with this script
	// initiates the two objects for sending and receiving data
	checkStatus(''); //sets the initial value and state of the input comment
	checkName(); //checks the initial value of the input name
	checkUrl();
	
	jal_loadtimes = 1;
	
	httpReceiveChat = getHTTPObject();
	httpSendChat = getHTTPObject();
	
	setTimeout('receiveChatText()', jal_timeout); //initiates the first data query
	
	document.getElementById('shoutboxname').onblur = checkName;
	document.getElementById('shoutboxurl').onblur = checkUrl;
	document.getElementById('chatbarText').onfocus = function () { checkStatus('active'); }	
	document.getElementById('chatbarText').onblur = function () { checkStatus(''); }
	if(document.getElementById('submitchat')) {
		document.getElementById('submitchat').onclick = sendComment;
	}
    document.getElementById('chatForm').onsubmit = function () { return false; }
	// When user mouses over shoutbox
    document.getElementById('chatoutput').onmouseover = function () {
    	if (jal_loadtimes > 9) {
    		jal_loadtimes = 1;
			receiveChatText();
    	}
    	jal_timeout = jal_org_timeout;
    }
}

//initiates the first data query
function receiveChatText() {
	jal_lastID = parseInt(document.getElementById('jal_lastID').value) - 1;
	if (httpReceiveChat.readyState == 4 || httpReceiveChat.readyState == 0) {
  		httpReceiveChat.open("GET",GetChaturl + '&jal_lastID=' + jal_lastID + '&rand='+Math.floor(Math.random() * 1000000), true);
		httpReceiveChat.onreadystatechange = handlehHttpReceiveChat; 
  		httpReceiveChat.send(null);
		jal_loadtimes++;
	    if (jal_loadtimes > 9) jal_timeout = jal_timeout * 5 / 4;
	}
    	setTimeout('receiveChatText()',jal_timeout);

}

function receiveOneChatText() {
	jal_lastID = parseInt(document.getElementById('jal_lastID').value) - 1;
	if (httpReceiveChat.readyState == 4 || httpReceiveChat.readyState == 0) {
  		httpReceiveChat.open("GET",GetChaturl + '&jal_lastID=' + jal_lastID + '&rand='+Math.floor(Math.random() * 1000000), true);
		httpReceiveChat.onreadystatechange = handlehHttpReceiveChat; 
  		httpReceiveChat.send(null);
	}
}

//deals with the servers' reply to requesting new content
function handlehHttpReceiveChat() {
  if (httpReceiveChat.readyState == 4) {
    results = httpReceiveChat.responseText.split('###'); //the fields are seperated by ###
    if (results.length > 4) {
	    for(i=0;i < (results.length-1);i=i+5) { //goes through the result one message at a time
	    	insertNewContent(results[i+1],results[i+2],results[i+3],results[i+4],results[i]); //inserts the new content into the page
	    	document.getElementById('jal_lastID').value = parseInt(results[i]) + 1;
	    }
    	jal_timeout = jal_org_timeout;
    	jal_loadtimes = 1;

    }
  }
}

//inserts the new content into the page
function insertNewContent(liName,liText,lastResponse, liUrl, liId) {
    response = document.getElementById("responseTime");
    response.replaceChild(document.createTextNode(lastResponse), response.firstChild);
	insertO = document.getElementById("outputList");
	oLi = document.createElement('li');
	oLi.setAttribute('id','comment-new'+liId);

	oSpan = document.createElement('span');
	oSpan.setAttribute('class','name');
	
	oName = document.createTextNode(liName);
	
	if (liUrl != "http://" && liUrl != '') {
		oURL = document.createElement('a');
		oURL.href = liUrl;
		oURL.appendChild(oName);
	} else {
		oURL = oName;
	}
	
	oSpan.appendChild(oURL);
	oSpan.appendChild(document.createTextNode(' : '));
	oLi.appendChild(oSpan);
	oLi.innerHTML += liText;
	insertO.insertBefore(oLi, insertO.firstChild);
	Fat.fade_element("comment-new"+liId, 30, 1500, fadefrom, fadeto);
}


//stores a new comment on the server
function sendComment() {
	currentChatText = document.forms['chatForm'].elements['chatbarText'].value;
	if (httpSendChat.readyState == 4 || httpSendChat.readyState == 0) {
		if (currentChatText == '') return;
		currentName = document.getElementById('shoutboxname').value;
		currentUrl = document.getElementById('shoutboxurl').value;
		homepage = document.getElementById('website').value;
		param = 'n='+ encodeURIComponent(currentName)+'&c='+ encodeURIComponent(currentChatText) +'&u='+ encodeURIComponent(currentUrl) +'&h='+ encodeURIComponent(homepage);	
		httpSendChat.open("POST", SendChaturl, true);
		httpSendChat.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
  		httpSendChat.onreadystatechange = receiveOneChatText;
  		httpSendChat.send(param);
  		document.forms['chatForm'].elements['chatbarText'].value = '';
	}
}

// http://www.codingforums.com/showthread.php?t=63818
function pressedEnter(field,event) {
	var theCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
	if (theCode == 13) {
		sendComment();
		return false;
	} 
	else return true;
}


//does clever things to the input and submit
function checkStatus(focusState) {
	currentChatText = document.forms['chatForm'].elements['chatbarText'];
	//oSubmit = document.forms['chatForm'].elements['submit'];
	//if (currentChatText.value != '' || focusState == 'active') {
		//oSubmit.disabled = false;
	//} else {
	//	oSubmit.disabled = true;
	//}
}

function jal_getCookie(name) {
  var dc = document.cookie;
  var prefix = name + "=";
  var begin = dc.indexOf("; " + prefix);
  if (begin == -1) {
    begin = dc.indexOf(prefix);
    if (begin != 0) return null;
  } else
    begin += 2;
  var end = document.cookie.indexOf(";", begin);
  if (end == -1)
    end = dc.length;
  return unescape(dc.substring(begin + prefix.length, end));
}


//autoasigns a random name to a new user
//If the user has chosen a name, use that
function checkName() {
	
	jalCookie = jal_getCookie("jalUserName");
	currentName = document.getElementById('shoutboxname');
	
	if (currentName.value != jalCookie) {
		document.cookie = "jalUserName="+currentName.value+";expires="+expiryDate+";"
	}
		
	if (jalCookie && currentName.value == '') {
		currentName.value = jalCookie;
		return;
	}
	
	if (currentName.value == '') {
		currentName.value = guestName+'_'+ Math.floor(Math.random() * 10000);
	}
	
}

function checkUrl() {
	
	jalCookie = jal_getCookie("jalUrl");
	currentName = document.getElementById('shoutboxurl');

	if (currentName.value == '') {
		document.cookie = "jalUrl=http://;expires="+expiryDate+";"
		return;
	}
		
	if (currentName.value != jalCookie) {
		document.cookie = "jalUrl="+currentName.value+";expires="+expiryDate+";"
		return;
	}
		
	if (jalCookie && ( currentName.value == '' || currentName.value == "http://")) {
		currentName.value = jalCookie;
		return;
	}		
}


//initiates the XMLHttpRequest object
//as found here: http://www.webpasties.com/xmlHttpRequest
function getHTTPObject() {
  var xmlhttp;
  /*@cc_on
  @if (@_jscript_version >= 5)
    try {
      xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
      try {
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
      } catch (E) {
        xmlhttp = false;
      }
    }
  @else
  xmlhttp = false;
  @end @*/
  if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
    try {
      xmlhttp = new XMLHttpRequest();
    } catch (e) {
      xmlhttp = false;
    }
  }
  return xmlhttp;
}