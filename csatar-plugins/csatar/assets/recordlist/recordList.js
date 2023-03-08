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

    $('.sortButton').on('click', function(event){
        let previousSortDirection = $(this).data('sort-direction');
        let element = $(this);
        setSortButtonAttributes(element, previousSortDirection)
        let column = $(this).data('column');
        let sortDirection = $(this).data('sort-direction');

        if ($(this).data('sort-direction') == 'noSort') {
            let sortDefault = $('.sortDefault');
            setSortButtonAttributes(sortDefault, 'noSort', sortDefault.data('default-sort-direction'))
            column = $('.sortDefault').data('column');
            sortDirection = $('.sortDefault').data('sort-direction');
        }

        filterSortPaginate(1, column, sortDirection);
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

    if(selected.length>0){
        $( "#activeFiltersCard" ).removeClass('d-none');
        $( "#activeFiltersList" ).empty();

        selected.forEach(function(item){
            let columnLabel = $(item[0][0]).attr('data-column-label');
            let label = columnLabel + ': ' + $(item[0][0].parentElement).children("label").text() + item[1];
            let html = '<span class="filter-tag badge bg-primary mr-1">' + label + ' <a class="badge badge-dark"';
            html += 'onClick="removeFilter(\x27' + item[0][0].id + '\x27);">X</a></span>';
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

function setSortButtonAttributes(element, previousSortDirection, sortDefault = ''){
    switch (previousSortDirection) {
        case 'asc':
            console.log('asc');
            element.removeClass('asc');
            element.addClass('desc');
            element.attr('data-sort-direction', 'desc');
            element.data('sort-direction', 'desc');
            break;
        case 'desc':
            console.log('desc');
            element.removeClass('desc');
            element.attr('data-sort-direction', 'noSort');
            element.data('sort-direction', 'noSort');
            break;
        case 'noSort':
        default:
            console.log('noSort');
            element.addClass(sortDefault ? sortDefault : 'asc');
            element.attr('data-sort-direction', sortDefault ? sortDefault : 'asc');
            element.data('sort-direction', sortDefault ? sortDefault : 'asc');
    }
    $('.sortButton').not(element).each(function(){
        $(this).removeClass('desc');
        $(this).removeClass('asc');
        $(this).data('sort-direction', 'noSort');
    });
}