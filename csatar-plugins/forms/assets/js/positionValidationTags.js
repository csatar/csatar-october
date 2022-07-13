$( document ).ready(function() {

    $('#validationTags').children().each(function () {
        if($(this).data( "validateFor" )){
            let fieldName = $(this).data( "validateFor" );
            let parentTag = $("div[data-field-name='" + fieldName +"']");
            $(this).insertAfter( parentTag.children().last() );
        }
    });

});
