function cambio(){
	texto = document.getElementById("botonCambio").textContent;
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
	
}