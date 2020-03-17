document.observe('dom:loaded', function () {
    if(Validation) {
        Validation.addAllThese([
            [
                'z4money_taxvat',
                'Digite um CPF/CNPJ válido. Use apenas dígitos.',
                function(value, element){
                    value = jQuery.trim(value);
                    value = value.replace('.','');
                    value = value.replace('.','');
                    value = value.replace('-','');
                    value = value.replace('/','');

                    if(value.length < 11){
                        return false;
                    }if(value.length == 11){
                        if(!ph_isCPFValid(value)){
                            return false;
                        }
                    }else if(value.length > 11){
                        if(!ph_isCNPJValid(value)){
                            return false;
                        }
                    }
                    return true;
                }
            ],
            [
                'z4money_holdername',
                'Digite um nome válido.',
                function(value, element){
                    value = jQuery.trim(value);

                    if(value.length < 3 || new RegExp(/\d+/).test(value)){
                        return false;
                    }
                    return true;
                }
            ],
            [
                'z4money_ccnumber',
                'Insira um número de cartão de crédito válido. Use apenas dígitos.',
                function(value, element){
                    value = jQuery.trim(value);

                    if(value.length < 10 && !new RegExp("^([a-z0-9]{5,})$").test(value)){
                        return false;
                    }
                    return true;
                }
            ],            
			[
                'z4money_cccvv',
                'Insira um número de verificação de cartão de crédito válido. Use apenas dígitos.',
                function(value, element){
                    value = jQuery.trim(value);

                    if(value.length < 3 || value.length > 4 || !new RegExp(/\d+/).test(value)){
                        return false;
                    }
                    return true;
                }
            ],
			[
                'z4money_ccexpiration',
                'Selecione o mês de vencimento do cartão.',
                function(value, element){
                    value = jQuery.trim(value);

                    if(value.length > 2 || value < 1 || value > 13 || !new RegExp(/\d+/).test(value)){
                        return false;
                    }
                    return true;
                }
            ],
			[
                'z4money_ccexpiration_yr',
                'Selecione o ano de vencimento do cartão.',
                function(value, element){
                    value = jQuery.trim(value);

                    if(value.length != 4 || value < 2019 || !new RegExp(/\d+/).test(value)){
                        return false;
                    }
                    return true;
                }
            ],
			[
                'z4money_ccinstallments',
                'Selecione a quantidade de parcelas.',
                function(value, element){
                    value = jQuery.trim(value);

                    if(value.length > 2 || value < 1 || value > 12 || !new RegExp(/\d+/).test(value)){
                        return false;
                    }
                    return true;
                }
            ]
        ])
    }
});

function ph_isCPFValid(value) {
    value = jQuery.trim(value);
    value = value.replace('.','');
    value = value.replace('.','');
    cpf = value.replace('-','');
    while(cpf.length < 11) cpf = "0" + cpf;
    var expReg = /^0+$|^1+$|^2+$|^3+$|^4+$|^5+$|^6+$|^7+$|^8+$|^9+$/;
    var a = [];
    var b = new Number;
    var c = 11;
    for (i=0; i<11; i++){
        a[i] = cpf.charAt(i);
        if (i < 9) b += (a[i] * --c);
    }
    if ((x = b % 11) < 2) { a[9] = 0 } else { a[9] = 11-x }
    b = 0;
    c = 11;
    for (y=0; y<10; y++) b += (a[y] * c--);
    if ((x = b % 11) < 2) { a[10] = 0; } else { a[10] = 11-x; }
    if ((cpf.charAt(9) != a[9]) || (cpf.charAt(10) != a[10]) || cpf.match(expReg)){
        return false;
    }
    return true;
}

function ph_isCNPJValid(value) {
    var b = [6,5,4,3,2,9,8,7,6,5,4,3,2], c = value;
    if((c = c.replace(/[^\d]/g,"").split("")).length != 14)
        return false;
    for (var i = 0, n = 0; i < 12; n += c[i] * b[++i]);
    if(c[12] != (((n %= 11) < 2) ? 0 : 11 - n))
        return false;
    for (var i = 0, n = 0; i <= 12; n += c[i] * b[i++]);
    if(c[13] != (((n %= 11) < 2) ? 0 : 11 - n))
        return false;
    return true;
}
