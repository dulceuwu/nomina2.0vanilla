document.addEventListener('DOMContentLoaded', function() {
    var navToggle = document.getElementById('nav-toggle');
    var navMenu = document.getElementById('nav-menu');
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('mostrar');
        });
    }
    var mensajeFlash = document.getElementById('mensaje-flash');
    if (mensajeFlash) {
        setTimeout(function() {
            mensajeFlash.style.opacity = '0';
            mensajeFlash.style.transition = 'opacity 0.5s';
            setTimeout(function() {
                mensajeFlash.style.display = 'none';
            }, 500);
        }, 4000);
    }
});

function mostrarToast(mensaje, tipo) {
    tipo = tipo || 'info';
    var toast = document.createElement('div');
    toast.className = 'toast toast-' + tipo;
    toast.textContent = mensaje;
    document.body.appendChild(toast);
    setTimeout(function() {
        toast.classList.add('mostrar');
    }, 10);
    setTimeout(function() {
        toast.classList.remove('mostrar');
        setTimeout(function() {
            toast.remove();
        }, 300);
    }, 3000);
}

function mostrarSpinner() {
    var overlay = document.getElementById('spinner-overlay');
    if (overlay) overlay.classList.add('mostrar');
}

function ocultarSpinner() {
    var overlay = document.getElementById('spinner-overlay');
    if (overlay) overlay.classList.remove('mostrar');
}

function confirmarModal(mensaje, callback) {
    var overlay = document.getElementById('modal-confirm');
    var texto = document.getElementById('modal-texto');
    var btnSi = document.getElementById('modal-btn-si');
    var btnNo = document.getElementById('modal-btn-no');
    if (!overlay || !texto || !btnSi || !btnNo) return;
    texto.textContent = mensaje;
    overlay.classList.add('mostrar');
    btnSi.onclick = function() {
        overlay.classList.remove('mostrar');
        if (callback) callback(true);
    };
    btnNo.onclick = function() {
        overlay.classList.remove('mostrar');
        if (callback) callback(false);
    };
}

document.addEventListener('change', function(e) {
    if (e.target.id === 'seleccionar-todos') {
        var checkboxes = document.querySelectorAll('.empleado-checkbox');
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = e.target.checked;
        }
    }
});
