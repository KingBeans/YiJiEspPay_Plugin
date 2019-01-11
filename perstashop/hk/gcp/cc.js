var rsakey;
try{
	setMaxDigits(129);
	rsakey=new RSAKeyPair("010001","","A0AE3F635B6983A3B78F6B23447FB9C156B9F62EA5193C9D6B2251FF6AA9CABB1764D9C7F431CFBBB96B4587BA2616E2C290421FB11CB29BC47136986BDD1DFF2F839FD06DB87D3331D80069B6F4295670CF2D7F6D640244EE041D748A77F819BAA712CC07F982D03230CB5B81E031C68E59857584FD2520ACE318EE5E90E769");
}catch(e){
}
function CardSpace(elm) {
	elm.value = elm.value.replace(/(\d{4})(?=\d)/g,"$1 ");
}
String.prototype.Trim = function() {
	return this.replace(/(^\s*)|(\s*$)/g, "");
}

function validateCreditCard(s) {
	
    var w = removeNonNumerics(s);
	if(w == "" ) 
		return false;
		
	if(checkCardTypes(w) == "OT")
		return false;
	

    return true;
}

function checkCardTypes(num) {
    var arr = {
        'VI': new RegExp('^4[0-9]{12}([0-9]{3})?$'),
        'MC': new RegExp('^5[1-5][0-9]{14}$'),
        'JCB': new RegExp('^(30[0-5][0-9]{13}|3095[0-9]{12}|35(2[8-9][0-9]{12}|[3-8][0-9]{13})|36[0-9]{12}|3[8-9][0-9]{14}|6011(0[0-9]{11}|[2-4][0-9]{11}|74[0-9]{10}|7[7-9][0-9]{10}|8[6-9][0-9]{10}|9[0-9]{11})|62(2(12[6-9][0-9]{10}|1[3-9][0-9]{11}|[2-8][0-9]{12}|9[0-1][0-9]{11}|92[0-5][0-9]{10})|[4-6][0-9]{13}|8[2-8][0-9]{12})|6(4[4-9][0-9]{13}|5[0-9]{14}))$'),
    }

    for (c in arr) {
        if (num.match(arr[c])) return c;
    }

    return "OT";
}

function validateCVV(s) {
	re = /\d{3,4}/  	
    return (re.test(s));
}

function removeNonNumerics(s) {
    var w = s.replace(/[^0-9]/g,"");	
	return w;
}

function ccpaySelect(id) {
	if(document.all)  
	{  
		document.getElementById(id).click();  
	}  
	else  
	{  
		var evt = document.createEvent("MouseEvents");  
		evt.initEvent("click", true, true);  
		document.getElementById(id).dispatchEvent(evt);  
	}
}


function getTime() {
	function fix(num, length) {
		return ('' + num).length < length ? ((new Array(length + 1)).join('0') + num).slice(-length) : '' + num;
	}
	var date = new Date();
	var time = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate() + ' ' + date.getHours() + ':' + fix(date.getMinutes(), 2) + ':' + date.getSeconds();
	var timezoneOffset = (new Date().getTimezoneOffset() / 60) * (-1);
	if (timezoneOffset > 0) {
		time = time + " GMT + " + timezoneOffset
	} else {
		time = time + " GMT - " + timezoneOffset
	}
	if (time.indexOf("NaN") >= 0) {
		time = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate() + ' ' + date.getHours() + ':' + fix(date.getMinutes(), 2) + ':' + date.getSeconds();
	}
	return encodeURIComponent(time);
}

window.cctips = function(){
	var html	= "<div class=\"cc-tipsWrap\" style=\"z-index:1000;display:inline-block;background:#F4FBFF;line-height:1.5em;padding:5px 5px;border:1px solid #2192D3;position:absolute;text-align:left;-webkit-border-radius:3px;-moz-border-radius:3px;-webkit-box-shadow:2px 2px 3px #eee;-moz-box-shadow:2px 2px 3px #eee;\" id=\"tipsWrap-%=r%\"><div>";
	var dg		= function(id){return document.getElementById(id);};
	var dt		= function(parent, nodeName){return  parent.getElementsByTagName(nodeName);};
	var db		= document.body;
	var dd		= document.documentElement;
	var of		= 0;
	var prefix	= 'cc';
	var isie	= /msie\s([\d\.]+)/.test(navigator.userAgent.toLowerCase());
	var w		= window;
	var lock	= true;
	return function(id){
		var elem	= id ? typeof id == "string" ? dg(id) : id : this;
		var offset	= null;
		var	width	= elem.offsetWidth;
		var	height	= elem.offsetHeight;
		var rand	= 0;
		var func	= null;
		var	_this	= {};
		var pos		= {
			left	: function(w, h){return {top:offset.top , left:offset.left - w - of}},
			top		: function(w, h){return {top:offset.top - h - of, left:offset.left}},
			right	: function(w, h){return {top:offset.top , left:offset.left + width + of}},
			bottom	: function(w, h){return {top:offset.top + height + of , left:offset.left}}
		};
		_this.show = function(obj){
			if(elem.lock){
				elem.lock=false;return;
			}else elem.lock=true;
			offset	= elem.getBoundingClientRect();
			var top	= db.scrollTop + dd.scrollTop;
			var left= db.scrollLeft + dd.scrollLeft;
			obj.p = obj.p || 'right';
			var wrap = _this.append(obj.p, obj.closeBtn || false);
			dt(wrap, "DIV")[0].innerHTML = obj.content;
			var p = pos[obj.p](wrap.offsetWidth,wrap.offsetHeight);
			wrap.style.top = p.top + top + "px";
			wrap.style.left = p.left + left + "px";
			obj.time && setTimeout(function(){_this.clear(dg(prefix+rand));}, obj.time);
			obj.fn && obj.fn.call(elem, dg(prefix+rand));
			func = function(a, b){
				return function(){
					var top	= db.scrollTop + dd.scrollTop;
					var left= db.scrollLeft + dd.scrollLeft;
					offset = elem.getBoundingClientRect();
					var c = pos[obj.p](wrap.offsetWidth, wrap.offsetHeight);
					b.style.top = c.top + top + 'px';
					b.style.left = c.left + left + 'px';
				}
			}(elem, wrap);
			isie ? w.attachEvent('onresize', func) : w.addEventListener('resize', func, false);
		}
		_this.append = function(p,closeBtn){
			var r = rand = Math.floor(Math.random() * 10000);
			var x = document.createElement("DIV");
			x.id = prefix + r;
			x.innerHTML = html.replace("%=p%",p).replace(/%=r%/g,r);
			document.body.appendChild(x);
			return dg("tipsWrap-" + r);
		}			
		_this.clear = function(a){
			a && a.parentNode && a.parentNode.removeChild(a);
			isie ? w.detachEvent('onresize',func) : w.removeEventListener('resize', func, false);
			elem.lock = false;
		}
		_this.hide = function(){
			_this.clear(dg(prefix + rand));
		}
		return _this;
	}
}();
