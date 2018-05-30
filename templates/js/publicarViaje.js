function cambio(){
	var texto = document.getElementById("botonCambio").textContent;
	if(texto == "Cambiar a periodico"){
		document.getElementById("botonCambio").textContent = "Cambiar a ocasional";
		document.getElementById("ocasional").style.display = "none";
		document.getElementById("periodico").style.display = "block";
	} else {
		document.getElementById("botonCambio").textContent = "Cambiar a periodico";
		document.getElementById("ocasional").style.display = "block";
		document.getElementById("periodico").style.display = "none";		
	}
}

function validarDatos(){
	var valor = true;
	var fecha = document.getElementById("fecha").value;
	var vectorFecha = fecha.split("-");
	if(fecha == null || fecha.length == 0 || )
	return true;
}

function dateToday(){
	var today = new Date();
	var dd = today.getDate();
	var mm = today.getMonth()+1; //January is 0!
	var yyyy = today.getFullYear();
	if(dd<10) {
		dd = '0'+dd
	} 
	if(mm<10) {
		mm = '0'+mm
	} 
	today = mm + '/' + dd + '/' + yyyy;
}