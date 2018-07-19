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

function toggleVisibilityGeneral1(nombre, boton) {
    var viajes = document.getElementById(nombre);
    var button = document.getElementById(boton);
    if (viajes.style.display === "block") {
        viajes.style.display = "none";
        button.textContent = "Ver mis proximos viajes ";
    } else {
        viajes.style.display = "block";
        button.textContent = "Ocultar mis proximos viajes";
    }
    
}

function toggleVisibilityGeneral2(nombre, boton) {
    var viajes = document.getElementById(nombre);
    var button = document.getElementById(boton);
    if (viajes.style.display === "block") {
        viajes.style.display = "none";
        button.textContent = "Ver mi historial de viajes ";
    } else {
        viajes.style.display = "block";
        button.textContent = "Ocultar mi historial de viajes";
    }
    
}