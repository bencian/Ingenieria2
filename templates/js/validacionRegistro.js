function validarRegistro() {
 
    var nombre = document.getElementById("nombre").value;
    var apellido = document.getElementById("apellido").value;
    var email = document.getElementById("email").value;
    var passwrd = document.getElementById("pass").value;
    var passwrd1 = document.getElementById("pass1").value;
    /*var tyc= document.getElementById("");  terminos y condiciones*/ 
 
    if (nombre == null || nombre.length == 0 || !/^([^0-9]*)$/.test(nombre)) { //NO TIENE QUE PERMITIR NUMEROS
        alert("El nombre ingresado no es valido");
        return false;
    }
    if (apellido == null || apellido.length == 0 || !/^([^0-9]*)$/.test(apellido)) {  //NO TIENE QUE PERMITIR NUMEROS
        alert("El apellido ingresado no es valido");
        return false;
    }
 
    /*if (!(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/.test(email))){
        alert("El email ingresado no es valido");
        return false;
    }*/
     
    if (passwrd.length < 8 || !/^\w*\d+\w*$/.test(passwrd)) {	/*tiene que tener al menos un numero o caracter*/
        alert("La contrasena ingresada no es valida");
        return false;
    }
 
    if (passwrd1 != passwrd){
        alert("Las contraseñas no coinciden");
        return false;
    }
     
    return true;
 
}