function toggleForm() {
    var datos = document.getElementById("datos_form");
    var pass = document.getElementById("password_form");
    //var pass = document.getElementById("toggle");
    if (datos.style.display === "block") {
        datos.style.display = "none";
        pass.style.display = "block";
        toggle.textContent="Modificar mis datos";
    } else {
        datos.style.display = "block";
        pass.style.display = "none";
        toggle.textContent="Modificar contraseña"
    }
    
}

function validarCambioPassword(){

	var passwrd = document.getElementById("pass").value;
    var passwrd1 = document.getElementById("pass1").value;

	if (passwrd.length < 8) {
        alert("La contrasena ingresada no es valida, debe poseer al menos 8 caracteres");
        return false;
    }

    if (!(/^\w*\d+\w*$/.test(passwrd))) {
    	alert("La contrasena ingresada no es valida, debe estar formada por letras, numeros o guienes bajos, y tener al menos un numero");
        return false;
    }

    if (passwrd1 != passwrd){
        alert("Las contraseñas no coinciden");
        return false;
    }

	return true;
}

function validarCambioDatos(){

	var nombre = document.getElementById("nombre").value;
    var apellido = document.getElementById("apellido").value;
    var email = document.getElementById("email").value;
    var fecha = document.getElementById("nacimiento").value;

    if (nombre == null || nombre.length == 0 || !/^([A-Za-z] {0,1})+[A-Za-z]$/.test(nombre)) { //NO TIENE QUE PERMITIR NUMEROS
        alert("El nombre ingresado no es valido");
        return false;
    }
    if (apellido == null || apellido.length == 0 || !/^([A-Za-z]+ {0,1})+[A-Za-z]$/.test(apellido)) {  //NO TIENE QUE PERMITIR NUMEROS
        alert("El apellido ingresado no es valido");
        return false;
    }
 
    if (!(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/.test(email))){
        alert("El email ingresado no es valido");
        return false;
    }

   	var fecha1 = new Date(fecha);
   	fecha1.setHours(fecha1.getHours()+3);
   	
   	var diaD = new Date();
    diaD.setFullYear(diaD.getFullYear() - 16);
    
    if (diaD < fecha1) {
        alert("Debe ser mayor de 16 años");
        return false;
    }

    return true;
}