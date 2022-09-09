$(document).ready(function () {
    updateValuesFromSession();
});

$(document).on('ajaxUpdate', function (event, context) {
    updateValuesFromSession();
})

$(document).on('edit::onPaginate', function (event, context) {
    updateValuesFromSession();
})

function updateValuesFromSession() {
    $.request('onGetSessionValues', {
        success: function(data) {
            Object.keys(data).forEach((key) => {
                if(typeof data[key].id !== 'undefined') {
                    $('#' + key).val(data[key].value);
                    $('#' + key).addClass('select-changed');
                }
            });
        }
    })
}

function resetSelects() {
    $('.permission-select').each(function () {
        $(this).val($(this).data('initial-value'));
    });
}

function removeHiglights() {
    $('.permission-select').each(function () {
        $(this).removeClass('select-changed');
    });
}

function addChangedClass(select) {
    if (select.val() == select.data('initial-value')) {
        select.removeClass('select-changed');
    }
    if (select.val() != select.data('initial-value')) {
        select.addClass('select-changed');
    }
    select.removeClass('bg-danger');
}

function updateAll(action, value) {
    let data = {};
    $('.' + action + '-permission').each(function(){
        let selectId = $(this).attr('id')
        let dataRequestDataFromSelect = '{' + $(this).data('request-data') + '}';
        let dataRequestDataFromSelectJson = JSON.parse(dataRequestDataFromSelect.replace(/'/g, '"'));
        if (dataRequestDataFromSelectJson.recordId != "0") {
            data[selectId] = {
                'id': dataRequestDataFromSelectJson.recordId,
                'action': action,
                'value': value,
                'initialValue': dataRequestDataFromSelectJson.initialValue
            }
            $(this).val(value);
            $(this).addClass('select-changed');
        }
    });

    $.request('onMultipleValueChange', {
        data: {data: data},
        loading: $.oc.stripeLoadIndicator
    });
}
