function new_window(width , height , content) {
	window.open(content,'_blank','toolbar=0, location=0, directories=0, status=0, scrollbars=1, resizable=1, copyhistory=0, menuBar=0, width='+width+', height='+height);
}

function toggleVisibility(elem) {
	var regExp = /visible/;
	if(regExp.test(elem.className)) {
		elem.className = elem.className.replace(/visible/ , 'hidden');
	} else {
		elem.className = elem.className.replace(/hidden/ , 'visible');
	}
}

function confirmDel(uri) {
	if(confirm('Etes vous sur de vouloir supprimer ?')) {
		document.location = uri;
	}
}