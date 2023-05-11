// on document load
$(document).ready(function() {

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
        let alias = selectedOption.data('alias');

        filterSortPaginate(alias,1, sortColumn, sortDirection);
    });

    $('.filter-input').keyup(function(event) {
        // find child with checkbox-container class
        let checkboxContainer = $(this).parent().parent().find('.checkbox-container');
        checkboxContainer.children().each( function(){
            let textToSearch = event.target.value.toUpperCase().replace(/ /g, "");
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
    let alias = element.data('alias');

    element.val('');
    let html = '<div id="hiddenCheckbox_' + Date.now() + '">'
    html += '<input class="form-check-input" type="checkbox" value="' + keyword
        + '" data-alias="' + alias
        + '" data-column="' + column + '" data-column-label="' + filterLabel + '" id="keyword_' + Date.now()
        + '" onchange="filterSortPaginate()" checked><label class="form-check-label" for="keyword_' + Date.now()
        + '">' + keyword + '</label></div>';
    $('#hiddenCheckboxes-' + alias).append(html);
    filterSortPaginate(alias);
}

function filterSortPaginate(componentAlias, page = 1, sortColumn = '', sortDirection = '', changedColumn = null) {
    let selected = {};
    selected[componentAlias] = [];
    let activeFilters = {};

    $("input:checkbox:checked, input:radio:checked").each(function() {
        let filterColumn = $(this).data('column');
        // check if filterColumn exists in activeFilters
        if(!activeFilters[filterColumn]){
            activeFilters[filterColumn] = [];
        }
        if ($(this).data('alias') == componentAlias) {
            activeFilters[filterColumn].push($(this).val());
            selected[componentAlias].push([ $(this), '']);
        }
    });

    $('#activeFiltersNumber-' + componentAlias).text(selected[componentAlias].length);

    if(selected[componentAlias].length>0){

        $( "#activeFiltersCard-" + componentAlias ).removeClass('d-none');
        $( "#activeFiltersList-" + componentAlias  ).empty();

        selected[componentAlias].forEach(function(item){
            let columnLabel = $(item[0][0]).attr('data-column-label');
            let label = columnLabel + ': ' + $(item[0][0].parentElement).children("label").text() + item[1];
            let html = '<div class="filter-tag badge d-flex bg-gray text-subtitle text-wrap text-start w-sm-100 m-1"><span class="my-auto text-nowrap">' + label + '  </span><a class="badge badge-dark text-subtitle ms-auto my-auto pe-0"';
            html += 'onClick="removeFilter(\x27' + item[0][0].id + '\x27, \x27' + componentAlias + '\x27);">x</a></div>';
            $( "#activeFiltersList-" + componentAlias ).append( html );
        });
    } else {
        $( "#activeFiltersCard-" + componentAlias ).addClass('d-none');
    }

    activeFilters = JSON.stringify(activeFilters);
    $.request(componentAlias + '::onFilterSortPaginate', {
        data: {
            componentAlias: componentAlias,
            activeFilters: activeFilters,
            page: page,
            sortColumn: sortColumn,
            sortDirection: sortDirection,
            changedColumn: changedColumn
        }
    });

}

function removeFilter(elementId, componentAlias){
    $('#' + elementId).prop( "checked", false );
    filterSortPaginate(componentAlias);
}

function removeAllFilters(componentAlias){
    $("input:checkbox:checked, input:radio:checked").each(function() {
        $(this).prop( "checked", false );
    });
    filterSortPaginate(componentAlias);
}

function collapseOtherFilters(currentFilterId){
    $('.collapsable-filter').each(function(){
        if ($(this).attr('id') != currentFilterId) {
            $(this).collapse('hide');
        }
    });
}
