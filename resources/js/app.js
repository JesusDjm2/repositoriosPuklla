import './bootstrap';
import Swal from 'sweetalert2';

// Notificación al cerrar sesión: LoginController::loggedOut() la deja en la
// sesión y el layout público (layouts/hub.blade.php) la expone aquí vía
// window.__logoutMessage.
document.addEventListener('DOMContentLoaded', () => {
    if (window.__logoutMessage) {
        Swal.fire({
            icon: 'success',
            title: window.__logoutMessage,
            confirmButtonColor: '#ae0c21',
            timer: 5000,
            timerProgressBar: true,
        });
    }
});
