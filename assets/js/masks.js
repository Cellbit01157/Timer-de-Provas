/**
 * Máscaras e Utilitários de Formulário
 * Timer de Provas
 */

document.addEventListener('DOMContentLoaded', () => {
    // Máscara para campos de CPF
    const cpfInputs = document.querySelectorAll('input[data-mask="cpf"], input[name="cpf"]');
    cpfInputs.forEach(input => {
        input.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);

            if (value.length > 9) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/, '$1.$2.$3-$4');
            } else if (value.length > 6) {
                value = value.replace(/(\d{3})(\d{3})(\d{1,3})/, '$1.$2.$3');
            } else if (value.length > 3) {
                value = value.replace(/(\d{3})(\d{1,3})/, '$1.$2');
            }

            e.target.value = value;
        });
    });

    // Filtro numérico estrito para campos inteiros de tempo
    const numberInputs = document.querySelectorAll('input[type="number"][data-positive]');
    numberInputs.forEach(input => {
        input.addEventListener('input', (e) => {
            if (parseInt(e.target.value, 10) < 0) {
                e.target.value = 0;
            }
        });
    });
});
