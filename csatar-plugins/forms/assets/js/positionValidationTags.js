$( document ).ready(function() {

    $('#validationTags').children().each(function () {
        if($(this).data( "validateFor" )){
            let inputName = 'data[' + $(this).data( "validateFor" ) + ']';
            let inputTag = $('[name="' + inputName + '"]')
            $(this).insertAfter( inputTag.parent().children().last() );
        }
    });

});
