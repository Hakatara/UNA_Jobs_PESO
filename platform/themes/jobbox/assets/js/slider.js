$(document).ready(function (){
    let rangeSlider = $('#slider-range');
    let maxSalaryRange = parseInt(rangeSlider.data('maxSalaryRange'))
    if (rangeSlider.length > 0) {
        let moneyFormat = wNumb({
            decimals: window.currencies.number_after_dot,
            thousand: window.currencies.thousands_separator,
            prefix: window.currencies.symbol
        });

        const boxMoney = $('.box-input-money')

        noUiSlider.create(rangeSlider[0], {
            start: [boxMoney.find('input[name="offered_salary_from"]').val(), boxMoney.find('input[name="offered_salary_to"]').val()],
            step: 1,
            tooltips: true,
            connect: true,
            range: {
                min: 0,
                max: maxSalaryRange
            },
            format: moneyFormat
        });

        // Set visual min and max values and also update value hidden form inputs
        rangeSlider[0].noUiSlider.on('update', function (values) {
            $('input[name="offered_salary_from"]').val(moneyFormat.from(values[0]));
            $('input[name="offered_salary_to"]').val(moneyFormat.from(values[1]));
        });
    }
});
