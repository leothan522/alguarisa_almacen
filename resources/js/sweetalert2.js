import Swal from 'sweetalert2';

window.Swal = Swal;

window.SweetAlert = Swal.mixin({
    text: 'Operation completed successfully.',
    icon: 'success',
    position: 'top-end',
    toast: true,
    showConfirmButton: false,
    theme: 'auto',
    topLayer: true,
    timer: 3000
});
