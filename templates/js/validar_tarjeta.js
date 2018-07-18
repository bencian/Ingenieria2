function validarTarjeta() {
 
    var nombre = document.getElementById("nombre").value;
    var tarjeta = document.getElementById("tarjeta").value;
    var codigo = document.getElementById("codigo").value;
    var vencimiento = document.getElementById("vencimiento").value;
    
    if (nombre == null || nombre.length == 0 || !/^([^0-9]*)$/.test(nombre)) {
        alert("El nombre ingresado no es valido");
        return false;
    }

    if (tarjeta == null || tarjeta.length == 0 || !/^(?:4\d([\- ])?\d{6}\1\d{5}|(?:4\d{3}|5[1-5]\d{2}|6011)([\- ])?\d{4}\2\d{4}\2\d{4})$/.test(tarjeta)) {
        alert("El numero de tarjeta ingresado no es valido");
        return false;
    }

    if (codigo == null || codigo.length == 0 || !/^[0-9]{3,4}$/.test(codigo)) {
        alert("El codigo ingresado no es valido");
        return false;
    }

    var hoy = new Date();
    var fecha = new Date(vencimiento);
    fecha.setMonth(fecha.getMonth() + 1);
    hoy.setHours(0,0,0,0);
    if (hoy > fecha) {
        alert("Tarjeta vencida");
        return false;
    }

    return true;
 
}