window.positionValidationTags = function (forPivot) {
    $('.validationTags').each(function (){
        $(this).children().each(function () {
    //     $('[data-validate-for]').each(function () {
            if($(this).data( "validateFor" ) && !forPivot){
                let fieldName = $(this).data( "validateFor" );
                let parentTag = $("div[data-field-name='" + fieldName +"']");
                $(this).insertAfter( parentTag.children().last() );
            }
            if($(this).data( "validateFor" ) && forPivot){
                let inputName = $(this).data( "positionFor" );
                let inputTag = $('[name="' + inputName + '"]');
                $(this).insertAfter( inputTag.parent().children().last() );
            }
        });
    });
}

$( document ).ready(function() {
    window.positionValidationTags(false);
});
