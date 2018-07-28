function validarDatosDeBusqueda(){

    var destino= document.getElementById("destino").value;
    var origen= document.getElementById("origen").value;
    var salida= document.getElementById("salida").value;

    if (origen==''){
    	alert("Se debe ingresar un origen");
    	return false;
    }

    if (destino != ''){
        if (origen==destino){
    	   alert("No se pueden crear viajes con igual origen y destino, asi que la busqueda no producira resultados! Los viajes se realizan entre distintas ciudades!")
    	   return false;
        }
    }

    var hoy = new Date();
    hoy.setSeconds(hoy.getSeconds()-1);

    var fecha = new Date(salida);
    fecha.setHours(0,0,0,0);
    fecha.setDate(fecha.getDate() + 1);

    var fecha1 = new Date();	//para que tenga las horas, minutos y segundos actuales
    fecha1.setYear(fecha.getFullYear());
    fecha1.setMonth(fecha.getMonth());
    fecha1.setDate(fecha.getDate());

//alert(fecha1);
//alert(hoy);
    if (hoy > fecha1) {
        alert("Solo se pueden buscar viajes a futuro!");
        return false;
    }
	//alert(fecha);
    return true;
}