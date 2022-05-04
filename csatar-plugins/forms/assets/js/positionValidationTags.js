$( document ).ready(function() {

    $('#validationTags').children().each(function () {
        if($(this).data( "validateFor" )){
            let inputName = 'data[' + $(this).data( "validateFor" ) + ']';
            $(this).insertAfter( 'input[name="' + inputName + '"]' );
        }
    });

});
