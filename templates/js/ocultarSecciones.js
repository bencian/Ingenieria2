function toggleVisibility(nombre, boton) {
    var seccion = document.getElementById(nombre);
    var button = document.getElementById(boton);
    if (seccion.style.display === "block") {
        seccion.style.display = "none";
        button.textContent = "Ver detalles";
    } else {
        seccion.style.display = "block";
        button.textContent = "Ocultar detalles";
    }
}

function toggleVisibilityGeneral(nombre) {
    var seccion = document.getElementByClass(nombre);
    if (seccion.style.display === "block") {
        seccion.style.display = "none";
    } else {
        seccion.style.display = "block";
    }
    
}