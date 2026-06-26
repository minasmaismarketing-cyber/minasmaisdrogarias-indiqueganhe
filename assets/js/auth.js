document.addEventListener('DOMContentLoaded', () => {
    const cpfInputs = document.querySelectorAll('#cpf');
    const phoneInputs = document.querySelectorAll('#telefone, #whatsapp');

    cpfInputs.forEach((input) => maskCpf(input));
    phoneInputs.forEach((input) => maskPhone(input));
});

function maskCpf(input) {
    input.addEventListener('input', () => {
        let value = input.value.replace(/\D/g, '').slice(0, 11);
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        input.value = value;
    });
}

function maskPhone(input) {
    input.addEventListener('input', () => {
        let value = input.value.replace(/\D/g, '').slice(0, 11);
        if (value.length <= 10) {
            value = value.replace(/(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
        } else {
            value = value.replace(/(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
        }
        input.value = value;
    });
}
