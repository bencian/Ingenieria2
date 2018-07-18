function validarTarjeta() {
 
    var nombre = document.getElementById("nombre").value;
    var tarjeta = document.getElementById("tarjeta").value;
    var codigo = document.getElementById("codigo").value;
    var vencimiento = document.getElementById("vencimiento").value;
    
    if (nombre == null || nombre.length == 0 || !/^([^0-9]*)$/.test(nombre)) {
        alert("El nombre ingresado no es valido");
        return false;
    }
/*
    if (tarjeta == null || tarjeta.length == 0 || /^5[1-5][0-9]{14}$/.test(tarjeta)) {
        alert("La contrasena ingresada no es valida");
        return false;
    }
*/
    if (codigo == null || codigo.length == 0 || !/^[0-9]{3,4}$/.test(codigo)) {
        alert("El codigo ingresado no es valido");
        return false;
    }

    var hoy             = new Date();
    var fechaFormulario = new Date(vencimiento);
    hoy.setHours(0,0,0,0);
    if (hoy > fechaFormulario) {
        alert("Tarjeta vencida");
        return false;
    }

    return true;
 
}