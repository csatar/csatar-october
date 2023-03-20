// on document load
$(document).ready(function() {

    filterSortPaginate();

    $('.freeText').on("keydown", function (event) {
        if (event.keyCode === 13 ) {//|| e.keyCode === 188
            addKeywordCheckbox($(this));
        }
    });

    $('.searchButton').on('click', function(event){
        addKeywordCheckbox($('#' + $(this).data('input-id')));
    });

    $('#sort').on('change', function(event){
        let selectedOption = $(this).find('option:selected');
        let sortColumn = selectedOption.data('column');
        let sortDirection = selectedOption.data('direction');

        filterSortPaginate(1, sortColumn, sortDirection);
    });

    $('.filter-input').keyup(function(event) {
        // find child with checkbox-container class
        let checkboxContainer = $(this).parent().parent().find('.checkbox-container');
        checkboxContainer.children().each( function(){
            let textToSearch = event.target.value.toUpperCase().replace(/ /g, "");
            console.log(textToSearch);
            let searchIn = ($(this).children("label").text()).replace(/ /g, "");
            if (searchIn.toUpperCase().indexOf(textToSearch) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        })
    });

});

function addKeywordCheckbox(element){
    let keyword = element.val();
    if (keyword == '') {
        return;
    }
    let filterLabel = element.attr('placeholder');
    let column = element.data('column');
    element.val('');
    let html = '<div id="hiddenCheckbox_' + Date.now() + '">'
    html += '<input class="form-check-input" type="checkbox" value="' + keyword + '" data-column="' + column + '" data-column-label="' + filterLabel + '" id="keyword_' + Date.now()
        + '" onchange="filterSortPaginate()" checked><label class="form-check-label" for="keyword_' + Date.now()
        + '">' + keyword + '</label></div>';
    $('#hiddenCheckboxes').append(html);
    filterSortPaginate();
}

function filterSortPaginate(page = 1, sortColumn = '', sortDirection = '') {
    let selected = [];
    let activeFilters = {};

    $("input:checkbox:checked, input:radio:checked").each(function() {
        let filterColumn = $(this).data('column');
        // check if filterColumn exists in activeFilters
        if(!activeFilters[filterColumn]){
            activeFilters[filterColumn] = [];
        }
        activeFilters[filterColumn].push($(this).val());
        selected.push([ $(this), '']);
    });

    $('#activeFiltersNumber').text(selected.length);

    if(selected.length>0){

        $( "#activeFiltersCard" ).removeClass('d-none');
        $( "#activeFiltersList" ).empty();

        selected.forEach(function(item){
            let columnLabel = $(item[0][0]).attr('data-column-label');
            let label = columnLabel + ': ' + $(item[0][0].parentElement).children("label").text() + item[1];
            let html = '<span class="filter-tag badge bg-primary m-1 text-wrap">' + label + ' <a class="badge badge-dark"';
            html += 'onClick="removeFilter(\x27' + item[0][0].id + '\x27);">x</a></span>';
            $( "#activeFiltersList" ).append( html );
        });
    } else {
        $( "#activeFiltersCard" ).addClass('d-none');
    }

    // if (Object.keys(activeFilters).length !== 0) {
        activeFilters = JSON.stringify(activeFilters);
        $.request('onFilterSortPaginate', {
            data: {
                activeFilters: activeFilters,
                page: page,
                sortColumn: sortColumn,
                sortDirection: sortDirection
            }
        });
    // }

}

function removeFilter(elementId){
    $('#' + elementId).prop( "checked", false );
    filterSortPaginate();
}

function removeAllFilters(){
    $("input:checkbox:checked, input:radio:checked").each(function() {
        $(this).prop( "checked", false );
    });
    filterSortPaginate();
}
