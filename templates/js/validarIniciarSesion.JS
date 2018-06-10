
// funcion para validar el correo
function caracteresCorreoValido(email, div){
    
    var caract = new RegExp(/^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/);

    if (caract.test(email) == false){
        $(div).hide().removeClass('hide').slideDown('fast');
        return false;
    }else{
        $(div).hide().addClass('hide').slideDown('fast');
        return true;
    }
};

function validar(){
    // cuando pierde el foco, este valida si lo que esta en el campo de texto si es un correo o no y muestra una respuesta  
        $('form').find('#usr').blur(function(){
            caracteresCorreoValido($(this).val(), '#xmail');
        });
}