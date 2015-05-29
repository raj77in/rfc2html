$("#rwcss").remove();
$("#mwcss").remove();



function add_event(obj, e_type, func)
{
	if(obj.addEventListener){
		obj.addEventListener(e_type, func, false);
		return true;
	} else if(obj.attachEvent){
		var r = obj.attachEvent("on"+e_type, func);
		return r;
	} else {
		return false;
	}
}

function load()
{
	var elms = document.getElementsByTagName('span');
	for(var i=0; i<elms.length; i++) {
		if(elms.item(i).className != 'expand')
			continue;
		//document.write(i + '<br />');
		//elms.item(i).addEventListener("click", expand, false);
		add_event(elms.item(i), "click", expand);
	}

	init_deck();
}

function expand_all()
{
	var ea = document.getElementById('expand_all');
	var elms = document.getElementsByTagName('ul');

	var d = 'block';
	if(ea.innerHTML == 'Expand All') {
		ea.innerHTML = 'Collapse All';
	} else {
		ea.innerHTML = 'Expand All';
		d = 'none';
	}

	for(var i=0; i<elms.length; i++) {
		if(elms.item(i).className != 'ul_toc')
			continue;
		elms.item(i).style.display = d;
	}
	
}

function expand(e)
{
	var elm;
	if(window.event && window.event.srcElement){
		elm = window.event.srcElement;
	} else if(e && e.target){
		elm = e.target;
	}

	if(!elm)
		return;

	var id = 'u' + elm.id.substr(1);
	var ue = document.getElementById(id);
	if(ue) {
		if(ue.style.display == 'block')
			ue.style.display = 'none';
		else
			ue.style.display = 'block';
	}
	
}

function init_deck()
{
	var navlist = document.getElementById('navlist');
	if(!navlist)
		return;

	var tabs = navlist.childNodes;
	for(var i=0; i<tabs.length; i++) {
		if(tabs[i].nodeType != 1 || tabs[i].tagName.toLowerCase() != 'li')
			continue;
		var e = tabs[i].firstChild;
		add_event(e, "click", change);
	}
}

function change(e)
{
	var elm;
	if(window.event && window.event.srcElement){
		elm = window.event.srcElement;
	} else if(e && e.target){
		elm = e.target;
	}

	if(!elm)
		return;

	id = elm.getAttribute('title');
	if(!id) {
		id = elm.innerHTML;
	}

	id = 'deck_' + id;


	var navlist = document.getElementById('navlist');
	if(!navlist)
		return;
	var tabs = navlist.childNodes;
	for(var i=0; i<tabs.length; i++) {
		if(tabs[i].nodeType != 1 || tabs[i].tagName.toLowerCase() != 'li')
			continue;
		tabs[i].className = '';
	}
	elm.parentNode.className = 'select';
	
	var decks = document.getElementById('decks').childNodes;
	for(var i=0; i<decks.length; i++) {
		if(decks[i].nodeType != 1 || decks[i].tagName.toLowerCase() != 'div')
			continue;
		if(decks[i].id == id)
			decks[i].style.display = 'block';
		else
			decks[i].style.display = 'none';
	}
}
