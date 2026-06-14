document.addEventListener('DOMContentLoaded', function() {
    var inputsEmail = document.querySelectorAll('input[type="email"]');
    for (var i = 0; i < inputsEmail.length; i++) {
        inputsEmail[i].addEventListener('blur', function() {
            validarEmail(this);
        });
    }
    var inputsCedula = document.querySelectorAll('input[data-tipo="cedula"]');
    for (var j = 0; j < inputsCedula.length; j++) {
        inputsCedula[j].addEventListener('blur', function() {
            validarCedula(this);
        });
    }
    var inputsNumericos = document.querySelectorAll('input[data-tipo="numero"]');
    for (var k = 0; k < inputsNumericos.length; k++) {
        inputsNumericos[k].addEventListener('blur', function() {
            validarNumerico(this);
        });
    }
    var forms = document.querySelectorAll('form[data-validar]');
    for (var l = 0; l < forms.length; l++) {
        forms[l].addEventListener('submit', function(e) {
            if (!validarFormulario(this)) {
                e.preventDefault();
            }
        });
    }
});

function validarEmail(input) {
    var span = input.parentNode.querySelector('.error-msg');
    if (!span) {
        span = document.createElement('span');
        span.className = 'error-msg';
        input.parentNode.appendChild(span);
    }
    var patron = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (input.value && !patron.test(input.value)) {
        input.classList.add('error');
        span.textContent = 'Formato de email invalido.';
    } else {
        input.classList.remove('error');
        span.textContent = '';
    }
}

function validarCedula(input) {
    var span = input.parentNode.querySelector('.error-msg');
    if (!span) {
        span = document.createElement('span');
        span.className = 'error-msg';
        input.parentNode.appendChild(span);
    }
    var patron = /^[VEJ]\d{5,11}$/i;
    if (input.value && !patron.test(input.value)) {
        input.classList.add('error');
        span.textContent = 'Formato: V12345678 o E12345678';
    } else {
        input.classList.remove('error');
        span.textContent = '';
    }
}

function validarNumerico(input) {
    var span = input.parentNode.querySelector('.error-msg');
    if (!span) {
        span = document.createElement('span');
        span.className = 'error-msg';
        input.parentNode.appendChild(span);
    }
    if (input.value && isNaN(parseFloat(input.value))) {
        input.classList.add('error');
        span.textContent = 'Debe ingresar un numero valido.';
    } else {
        input.classList.remove('error');
        span.textContent = '';
    }
}

function validarFormulario(form) {
    var errors = form.querySelectorAll('.error');
    if (errors.length > 0) {
        mostrarToast('Corrija los errores resaltados antes de enviar.', 'error');
        return false;
    }
    return true;
}
