function validarModificacionDeViaje(){

	var fecha= document.getElementById("fecha").value; 
	var precio= document.getElementById("precio").value; 
	var duracion= document.getElementById("duracion").value;
	var distancia= document.getElementById("distancia").value;
	var hora_salida= document.getElementById("hora_salida").value;

    if (!(/^\d+$/).test(duracion)){
    	alert('Debe ingresar el numero estimado de horas');
    	return false;
    }

    if (!(/^\d+$/).test(distancia)){
    	alert('Debe ingresar el numero estimado de kilometros');
    	return false;
    }

    if (!(/^\d+([,|.]\d{0,2}){0,1}$/).test(precio)){
    	alert('Debe ingresar el precio estimado del viaje');
    	return false;
    }

    if (!(/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/).test(hora_salida)){
    	alert('Debe ingresarse una hora valida');
    	return false;
    }

    //VALIDO LA FECHA
   	var res = fecha.concat(' ',hora_salida);
   	var fecha1 = new Date(res);
    var hoy = new Date();
    if (hoy > fecha1) {
        alert("No puede publicar viajes en el pasado. Un Aventon no avala los viajes en el tiempo!");
        return false;
    }
	
	//alert('Todo funciona bien!');
    return true;
}