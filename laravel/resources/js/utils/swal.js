import Swal from 'sweetalert2';

// Configuração padrão personalizada do SweetAlert2 para o AKY Lanches
export const AppSwal = Swal.mixin({
  customClass: {
    confirmButton: 'btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm mx-1',
    cancelButton: 'btn btn-secondary rounded-pill px-4 py-2 fw-semibold shadow-sm mx-1',
    denyButton: 'btn btn-danger rounded-pill px-4 py-2 fw-bold shadow-sm mx-1',
    popup: 'rounded-4 shadow-lg border-0 p-4',
    title: 'fw-bold text-dark fs-5',
    htmlContainer: 'text-muted small',
  },
  buttonsStyling: false,
});

export const showSuccess = (title, text = '') => {
  return AppSwal.fire({
    icon: 'success',
    title,
    text,
    confirmButtonText: 'Entendido',
  });
};

export const showError = (title, text = '') => {
  return AppSwal.fire({
    icon: 'error',
    title,
    text,
    confirmButtonText: 'Fechar',
  });
};

export const showWarning = (title, text = '') => {
  return AppSwal.fire({
    icon: 'warning',
    title,
    text,
    confirmButtonText: 'Ok',
  });
};

export const showConfirm = async (title, text = '', confirmButtonText = 'Sim, confirmar', cancelButtonText = 'Cancelar') => {
  const result = await AppSwal.fire({
    icon: 'question',
    title,
    text,
    showCancelButton: true,
    confirmButtonText,
    cancelButtonText,
    reverseButtons: true,
  });
  return result.isConfirmed;
};

export default AppSwal;

