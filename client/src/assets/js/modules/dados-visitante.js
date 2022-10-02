function mascaraCPF(cpf) {
    cpf=cpf.replace(/\D/g,"")
    cpf=cpf.replace(/(\d{3})(\d)/,"$1.$2")
    cpf=cpf.replace(/(\d{3})(\d)/,"$1.$2")
    cpf=cpf.replace(/(\d{3})(\d{1,2})$/,"$1-$2")
    return cpf
}

function validaCPF(cpf, onCpfValido) {
    if(cpfValido(cpf.value)) {
        cpf.classList.remove('is-invalid');
        onCpfValido();
        return true;
    } else {
        cpf.classList.add('is-invalid');
        return false;
    }
}

function cpfValido(cpf) {
    cpf = cpf.replaceAll('\.', '');
    cpf = cpf.replaceAll('\-', '');
    if(cpf==="00000000000" || cpf < 11){
        return false;
    }

    let soma, resto;

    soma = 0;
    for (let i = 1; i <= 9; i++) {
        soma += parseInt(cpf.substring(i-1, i)) * (11 - i);
    }
    resto = (soma * 10) % 11;

    if((resto === 10) || (resto===11)) {
        resto = 0;
    }

    if(resto !== parseInt(cpf.substring(9,10))) {
        return false;
    }

    soma = 0;
    for (let i = 1; i <= 10; i++) {
        soma += parseInt(cpf.substring(i-1, i)) * (12 - i);
    }
    resto = (soma * 10) % 11;

    if((resto === 10) || (resto === 11)) {
        resto = 0;
    }

    return resto === parseInt(cpf.substring(10, 11));
}

export {mascaraCPF, validaCPF, cpfValido}
