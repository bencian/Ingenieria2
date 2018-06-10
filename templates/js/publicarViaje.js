
window.onload=function(){
	
	boton = document.getElementById("botonCambio");
	if(boton){
		boton.addEventListener("click",cambiarBoton);
	} 
	
	function cambiarBoton(){
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
	
	/*n =  new Date();
	y = n.getFullYear();
	m = n.getMonth() + 1;
	if(m<10){
		m = "0" + m;
	}
	d = n.getDate() + 7;
	fechaFinal = y + "-" + m + "-" + d;
	document.getElementById("final").value = fechaFinal;*/
}
	
function toggleTextArea(string) {
	stringCon = "hora_" + string;
	var elems = document.getElementsByClassName(stringCon);
	var check = document.getElementById(string);
	
	if(check.checked == true) {
		for (var i=0;i<elems.length;i+=1){
			elems[i].style.display = 'block';
			elems[i].setAttribute("required","")
		}
	} else {
		for (var i=0;i<elems.length;i+=1){
			elems[i].style.display = 'none';
		}
	}
}


	
