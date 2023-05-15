<?php
namespace Csatar\Csatar\Components;

use Auth;
use Carbon\Carbon;
use Cms\Classes\ComponentBase;
use Csatar\Csatar\Classes\Constants;
use Csatar\Csatar\Models\Association;
use Input;
use Lang;
use Storage;
use Response;
use Redirect;
use RainLab\Builder\Components\RecordList as RainRecordList;

class RecordList extends RainRecordList {

    use \System\Traits\ConfigMaker;

    /**
     * The model class name
     * @var string
     */
    public $modelClassName;

    /**
     * The configuration array of the model columns
     * @var collection
     */
    public $columnsConfig;

    /**
     * The configuration array for the table header
     * @var array
     */
    public array $tableHeaderConfig;

    /**
     * The configuration array for the table row
     * @var array
     */
    public array $tableRowConfig;

    /**
     * Sort column
     * @var string
     */
    public string $sortColumn;

    /**
     * Sort direction
     * @var string
     */
    public string $sortDirection;

    /**
     * Filters config
     * @var array
     */
    public array $filtersConfig;

    /**
     * Active filters applied by user
     * @var mixed
     */
    public array $activeFilters;

    /**
     *  Records for filter options
     * @var mixed
     */
    public $recordsForFilterOptions;

    public $defaultAssociation = 'Romániai Magyar Cserkészszövetség';

    public function componentDetails()
    {
        return [
            'name' => Lang::get('csatar.csatar::lang.plugin.component.recordList.name'),
            'description' => Lang::get('csatar.csatar::lang.plugin.component.recordList.description')
        ];
    }

    public function defineProperties()
    {
        $parentProperties = parent::defineProperties();

        if (isset($parentProperties['displayColumn'])) {
            unset($parentProperties['displayColumn']);
        }

        $properties = [
            'modelClass' => [
                'title'       => 'rainlab.builder::lang.components.list_model',
                'type'        => 'dropdown',
                'showExternalParam' => false
            ],
            'scope' => [
                'title'       => 'rainlab.builder::lang.components.list_scope',
                'description' => 'rainlab.builder::lang.components.list_scope_description',
                'type'        => 'dropdown',
                'depends'     => ['modelClass'],
                'showExternalParam' => false
            ],
            'scopeValue' => [
                'title'       => 'rainlab.builder::lang.components.list_scope_value',
                'description' => 'rainlab.builder::lang.components.list_scope_value_description',
                'type'        => 'string',
                'default'     => '{{ :scope }}',
            ],
            'columnsConfigFile' => [
                'title'       => 'csatar.csatar::lang.plugin.component.recordList.columnsConfigFile.columnsConfigFile',
                'description' => 'csatar.csatar::lang.plugin.component.recordList.columnsConfigFile.columnsConfigFileDescription',
                'type'        => 'string',
                'depends'     => ['modelClass'],
                'validation'  => [
                    'required' => [
                        'message' => Lang::get('csatar.csatar::lang.plugin.component.recordList.columnsConfigFile.columnsConfigFileMissing')
                    ]
                ]
            ],
            'noRecordsMessage' => [
                'title'        => 'rainlab.builder::lang.components.list_no_records',
                'description'  => 'rainlab.builder::lang.components.list_no_records_description',
                'type'         => 'string',
                'default'      => Lang::get('rainlab.builder::lang.components.list_no_records_default'),
                'showExternalParam' => false,
            ],
            'detailsPage' => [
                'title'       => 'rainlab.builder::lang.components.list_details_page',
                'description' => 'rainlab.builder::lang.components.list_details_page_description',
                'type'        => 'dropdown',
                'showExternalParam' => false,
                'group'       => 'rainlab.builder::lang.components.list_details_page_link'
            ],
            'detailsKeyColumn' => [
                'title'       => 'rainlab.builder::lang.components.list_details_key_column',
                'description' => 'rainlab.builder::lang.components.list_details_key_column_description',
                'type'        => 'autocomplete',
                'depends'     => ['modelClass'],
                'showExternalParam' => false,
                'group'       => 'rainlab.builder::lang.components.list_details_page_link'
            ],
            'detailsUrlParameter' => [
                'title'       => 'rainlab.builder::lang.components.list_details_url_parameter',
                'description' => 'rainlab.builder::lang.components.list_details_url_parameter_description',
                'type'        => 'string',
                'default'     => 'id',
                'showExternalParam' => false,
                'group'       => 'rainlab.builder::lang.components.list_details_page_link'
            ],
            'recordsPerPage' => [
                'title'             => 'rainlab.builder::lang.components.list_records_per_page',
                'description'       => 'rainlab.builder::lang.components.list_records_per_page_description',
                'type'              => 'string',
                'validationPattern' => '^[0-9]*$',
                'validationMessage' => 'rainlab.builder::lang.components.list_records_per_page_validation',
                'group'             => 'rainlab.builder::lang.components.list_pagination'
            ]
        ];

        if (isset($parentProperties['sortColumn'])) {
            unset($parentProperties['sortColumn']);
        }

        if (isset($parentProperties['sortDirection'])) {
            unset($parentProperties['sortDirection']);
        }

        return array_merge($properties, $parentProperties);
    }

    public function onRun()
    {
        $this->prepareVars();

        $this->records = $this->page['records'] = $this->listRecords();

        $this->filtersConfig = $this->page['filtersConfig'] = $this->getFiltersConfig();

        $this->addCss('/plugins/csatar/csatar/assets/recordlist/recordList.css?v=1.0.1');
        $this->addJs('/plugins/csatar/csatar/assets/recordlist/recordList.js?v=1.1.1');
    }

    protected function prepareVars()
    {
        $this->modelClassName   = $this->validateModelClassName();
        $this->noRecordsMessage = $this->page['noRecordsMessage'] = Lang::get($this->property('noRecordsMessage'));

        $this->columnsConfig     = $this->page['columnsConfig'] = $this->makeConfig($this->getColumnsConfigFile());
        $this->tableHeaderConfig = $this->page['tableHeaderConfig'] = $this->getTableHeaderConfig();
        $this->tableRowConfig    = $this->page['tableRowConfig'] = $this->getTableRowConfig();
        $this->sortConfig        = $this->page['sortConfig'] = $this->getSortConfig();

        $this->detailsKeyColumn    = $this->page['detailsKeyColumn'] = $this->property('detailsKeyColumn');
        $this->detailsUrlParameter = $this->page['detailsUrlParameter'] = $this->property('detailsUrlParameter');

        $detailsPage = $this->property('detailsPage');
        if ($detailsPage == '-') {
            $detailsPage = null;
        }

        $this->detailsPage = $this->page['detailsPage'] = $detailsPage;

        if (strlen($this->detailsPage)) {
            if (!strlen($this->detailsKeyColumn)) {
                throw new SystemException('The details key column should be set to generate links to the details page.');
            }

            if (!strlen($this->detailsUrlParameter)) {
                throw new SystemException('The details page URL parameter name should be set to generate links to the details page.');
            }
        }
    }

    protected function listRecords()
    {
        $model      = new $this->modelClassName();
        $scope      = $this->getScopeName($model);
        $scopeValue = $this->property('scopeValue');

        if ($scope !== null) {
            $model = $model->$scope($scopeValue);
        }

        $this->recordsForFilterOptions = $this->getRecordsForFilterOptions($model);

        if (!empty($this->activeFilters)) {
            $model = $this->applyFilters($model);
        }

        $model   = $this->sort($model);
        $records = $this->paginate($model);

        return $records;
    }

    protected function sort($model)
    {
        $sortColumn = $this->sortColumn ?? '';
        if (!strlen($sortColumn)) {
            return $model;
        }

        $sortDirection = $this->sortDirection;

        if ($sortDirection !== 'desc') {
            $sortDirection = 'asc';
        }

        return $model->orderBy($sortColumn, $sortDirection);
    }

    protected function paginate($model)
    {
        $recordsPerPage = trim($this->property('recordsPerPage'));
        if (!strlen($recordsPerPage)) {
            // Pagination is disabled - return all records
            return $model->get();
        }

        if (!preg_match('/^[0-9]+$/', $recordsPerPage)) {
            throw new SystemException('Invalid records per page value.');
        }

        $pageNumber = $this->pageNumber;
        if (!strlen($pageNumber) || !preg_match('/^[0-9]+$/', $pageNumber)) {
            $pageNumber = 1;
        }

        return $model->paginate($recordsPerPage, $pageNumber);
    }

    public function getColumnsConfigFile()
    {
        $modelClass        = $this->property('modelClass');
        $columnsConfigFile = $this->property('columnsConfigFile');
        return '$/' . str_replace('\\', '/', strtolower($modelClass)) . '/' . $columnsConfigFile;
    }

    public function getTableHeaderConfig() {
        $headerConfig = [];
        foreach ($this->columnsConfig->columns as $column => $config) {
            if (!isset($config['recordList'])) {
                continue;
            }

            if (isset($config['label'])) {
                $headerConfig[$column]['label'] = Lang::get($config['label']);
            } else {
                $headerConfig[$column]['label'] = ucfirst($column);
            }

            if (isset($config['recordList']['sortable']) && is_array($config['recordList']['sortable'])) {
                $headerConfig[$column]['sortable']        = true;
                $headerConfig[$column]['sortableDefault'] = $config['recordList']['sortable']['default'] ?? false;
                $this->sortColumn    = $config['recordList']['sortable']['default'] ? $column : null;
                $this->sortDirection = $config['recordList']['sortable']['default'] ?? 'asc';
            } else {
                $headerConfig[$column]['sortable'] = $config['recordList']['sortable'] ?? false;
            }
        }

        return $headerConfig;
    }

    public function getSortConfig() {
        $sortConfig = [];
        foreach ($this->columnsConfig->columns as $column => $config) {
            if (!isset($config['recordList']['sortable'])) {
                continue;
            }

            if (isset($config['label'])) {
                $sortConfig[$column]['label'] = Lang::get($config['label']);
            } else {
                $sortConfig[$column]['label'] = ucfirst($column);
            }

            if (isset($config['recordList']['sortable']) && is_array($config['recordList']['sortable'])) {
                $sortConfig[$column]['default'] = $config['recordList']['sortable']['default'] ?? false;
            } else {
                $headerConfig[$column]['sortable'] = $config['recordList']['sortable'] ?? false;
            }
        }

        return $sortConfig;
    }

    public function getFiltersConfig(bool $withoutOptions = false) {
        $filterConfig = [];
        foreach ($this->columnsConfig->columns as $column => $config) {
            if (!isset($config['recordList']['filterable'])) {
                continue;
            }

            if (isset($config['label'])) {
                $filterConfig[$column]['label'] = Lang::get($config['label']);
            } else {
                $filterConfig[$column]['label'] = ucfirst($column);
            }

            $filterConfig[$column]['type'] = $config['type']; // maybe this should be ignored and only the filterConfig type should be used
            if (!$withoutOptions) {
                $dependsOnValue = null;
                if (isset($config['recordList']['filterConfig']['dependsOn'])) {
                    foreach ($filterConfig[$config['recordList']['filterConfig']['dependsOn']]['options'] as $option) {
                        if ($option['checked'] == true) {
                            $dependsOnValue = $option['id'];
                            break;
                        }
                    }
                }

                $filterConfig[$column]['options'] = $this->getFilterOptions($column, $config, $dependsOnValue);
            }

            $filterConfig[$column]['filterConfig'] = $config['recordList']['filterConfig'] ?? null;
        }

        return $filterConfig;
    }

    public function getFilterOptions($column, $config, $dependsOn = null) {

        if (isset($config['recordList']['filterConfig']['type']) &&
            $config['recordList']['filterConfig']['type'] == 'freeText'
        ) {
            return [];
        }

        if (isset($config['recordList']['filterConfig']['options'])) {
            return $this->processPreDefinedOptions($config['recordList']['filterConfig']['options']);
        }

        if (isset($config['recordList']['filterConfig']['type']) &&
            $config['recordList']['filterConfig']['type'] == 'relation'
        ) {
            return $this->getFilterOptionsForRelation($column, $config, $dependsOn);
        }

        return $this->recordsForFilterOptions
            ->reject(function ($item) use ($column) {
                return empty($item[$column]);
            })
            ->map(function ($item) use ($column) {
            return [
                'id' => $item['id'],
                'label' => $item[$column]
            ];
        });
    }

    protected function processPreDefinedOptions($options) {
        if (!is_array($options)) {
            return null;
        }

        $processedOptions = [];
        foreach ($options as $key => $label) {
            if (empty($label)) {
                continue;
            }

            $processedOptions[] = [
                'id' => $key,
                'label' => $label
            ];
        }

        return $processedOptions;
    }

    protected function getRecordsForFilterOptions($model) {
        return $model->get();
    }

    protected function getFilterOptionsForRelation($column, $config, $dependsOn = null) {
        $options      = [];
        $model        = new $this->modelClassName();
        $relationName = $config['recordList']['filterConfig']['relationName'] ?? $column;
        $relationType = $this->rowConfig[$column]['relationName'] ?? $this->getRelationType($relationName);

        $defaultId = null;
        if (isset($config['recordList']['filterConfig']['defaultFrom'])) {
            $defaultFrom = $config['recordList']['filterConfig']['defaultFrom'];
            $defaultId   = $this->$defaultFrom();
        }

        $activeFilters       = json_decode(Input::get('activeFilters'), true);
        $columnActiveFilters = isset($activeFilters[$column]) ? $activeFilters[$column] : null;

        if (isset($model->$relationType[$relationName])) {
            $relationModelClassName = is_array($model->$relationType[$relationName]) ? $model->$relationType[$relationName][0] : $model->$relationType[$relationName];
            $relationModelClassName = $this->validateModelClassName($relationModelClassName);

            $query = !empty($dependsOn) ? $relationModelClassName::where($config['recordList']['filterConfig']['dependsOn'], $dependsOn)->get() : $relationModelClassName::all();

            return $query->map(function ($item) use ($config, $defaultId, $columnActiveFilters) {
                $keyFrom = $config['recordList']['filterConfig']['keyFrom'] ?? 'id';

                if (isset($config['recordList']['filterConfig']['extendedLabel'])) {
                    $label = $item->getExtendedNameAttribute();
                } else {
                    $labelFrom = $config['recordList']['filterConfig']['labelFrom'] ?? 'name';
                    $label     = $item->$labelFrom;
                }

                return [
                    'id' => $item->$keyFrom,
                    'label' => $label,
                    'checked' => $this->isOptionSelected($item->$keyFrom, $defaultId, $columnActiveFilters),
                ];
            });
        }

        return $options;
    }

    protected function applyFilters($query) {
        $filtersConfig = $this->getFiltersConfig(true);

        foreach ($this->activeFilters as $column => $values) {
            if (empty($values)) {
                continue;
            }

            if (isset($filtersConfig[$column]['filterConfig']['type'])) {
                if ($filtersConfig[$column]['filterConfig']['type'] == 'freeText') {
                    $query = $query->where(function ($query) use ($column, $values) {
                        foreach ($values as $value) {
                            $query = $query->orWhere($column, 'like', '%' . $value . '%');
                        }
                    });
                    continue;
                }

                if ($filtersConfig[$column]['filterConfig']['type'] == 'relation') {
                    $key          = $filtersConfig[$column]['filterConfig']['keyFrom'] ?? 'id';
                    $addKey1      = $filtersConfig[$column]['filterConfig']['additionalKeyFrom1'] ?? 'id';
                    $addKey2      = $filtersConfig[$column]['filterConfig']['additionalKeyFrom2'] ?? 'id';
                    $addKey3      = $filtersConfig[$column]['filterConfig']['additionalKeyFrom3'] ?? 'id';
                    $relationName = $filtersConfig[$column]['filterConfig']['relationName'] ?? $column;
                    $query        = $query->whereHas($relationName, function ($query) use ($values, $key, $addKey1, $addKey2, $addKey3){
                        $query->whereIn($key, $values)->orWhereIn($addKey1, $values)->orWhereIn($addKey2, $values)->orWhereIn($addKey3, $values);
                    });
                    continue;
                }
            }

            $query = $query->whereIn('id', $values);
        }

        return $query;
    }

    public function getTableRowConfig() {
        // this method should work without recordList['filterConfig'] set, because not all columns should be filterable
        $rowConfig = [];
        foreach ($this->columnsConfig->columns as $column => $config) {
            if (!isset($config['recordList'])) {
                continue;
            }

            $rowConfig[$column]['attribute'] = $column;
            $rowConfig[$column]['type']      = $config['type'];

            if (isset($config['label'])) {
                $rowConfig[$column]['label'] = Lang::get($config['label']);
            } else {
                $rowConfig[$column]['label'] = ucfirst($column);
            }

            if (isset($config['recordList']['tooltipFrom'])) {
                $rowConfig[$column]['tooltipFrom'] = $config['recordList']['tooltipFrom'];
            }

            if (isset($config['relation'])) {
                $rowConfig[$column]['relationName'] = $config['relation'];
                $rowConfig[$column]['relationType'] = $this->getRelationType($config['relation']);
                $rowConfig[$column]['attribute']    = $config['relation'];
                $rowConfig[$column]['valueFrom']    = $config['valueFrom'] ?? 'name';
            }
        }

        return $rowConfig;
    }

    public function getRelationType(string $relationName) {
        $model = new $this->modelClassName();
        $availableRelationTypes = Constants::AVAILABLE_RELATION_TYPES;
        // check relation type based on availableRelationTypes
        foreach ($availableRelationTypes as $relationType) {
            if (isset($model->$relationType[$relationName])) {
                return $relationType;
            }
        }

        return null;
    }

    public function onFilterSortPaginate() {

        $filters        = json_decode(Input::get('activeFilters'), true);
        $componentAlias = Input::get('componentAlias');
        $pageNumber     = Input::get('page');
        $sortColumn     = Input::get('sortColumn');
        $sortDirection  = Input::get('sortDirection');
        $this->prepareVars();

        if (!empty($filters)) {
            $this->activeFilters = $this->page['activeFilters'] = $filters;
        }

        if (!empty($pageNumber)) {
            $this->pageNumber = $pageNumber;
        }

        if (!empty($sortColumn) && !empty($sortDirection)) {
            $this->sortColumn    = $sortColumn;
            $this->sortDirection = $sortDirection;
        }

        $partialArray = [];

        $this->filtersConfig = $this->page['filtersConfig'] = $this->getFiltersConfig();
        foreach ($this->filtersConfig as $column => $config) {
            if (isset($config['filterConfig']['dependsOn']) && Input::get('changedColumn') == $config['filterConfig']['dependsOn']) {
                $partialArray['#filter-' . $column . '-' . $componentAlias] = $this->renderPartial('@filter', ['column' => $column, 'config' => $config]);
            }
        }

        $this->records = $this->page['records'] = $this->listRecords();
        $partialArray['#tableRows-' . $componentAlias] = $this->renderPartial('@tableRows');
        return $partialArray;
    }

    /**
     * @return string
     */
    public function validateModelClassName($modelClassName = null): string
    {
        if ($modelClassName === null) {
            $modelClassName = $this->property('modelClass');
        }

        if (!strlen($modelClassName) || !class_exists($modelClassName)) {
            throw new SystemException('Invalid model class name');
        }

        return $modelClassName;
    }

    public function getAssociationIdByUser()
    {
        return !Auth::user() ? Association::where('name', $this->defaultAssociation)->select('id')->first()->getAssociationId() : Auth::user()->scout->getAssociation()->id;
    }

    public function isOptionSelected($value, $defaultId = null, $activeIds = null)
    {
        if ($activeIds != null && in_array($value, array_values($activeIds))) {
            return true;
        }

        if (!Input::get('activeFilters') && $value == $defaultId) {
            return true;
        }

        return false;
    }

}
