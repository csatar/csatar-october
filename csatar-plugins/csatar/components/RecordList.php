<?php namespace Csatar\Csatar\Components;

use Auth;
use Carbon\Carbon;
use Cms\Classes\ComponentBase;
use Lang;
use Storage;
use Response;
use Redirect;
use RainLab\Builder\Components\RecordList as RainRecordList;

class RecordList extends RainRecordList {

    use \System\Traits\ConfigMaker;

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
            ],
            'pageNumber' => [
                'title'       => 'rainlab.builder::lang.components.list_page_number',
                'description' => 'rainlab.builder::lang.components.list_page_number_description',
                'type'        => 'string',
                'default'     => '{{ :page }}',
                'group'       => 'rainlab.builder::lang.components.list_pagination'
            ],
            'sortColumn' => [
                'title'       => 'csatar.csatar::lang.plugin.component.recordList.defaultSorting.listSortColumn',
                'type'        => 'autocomplete',
                'depends'     => ['modelClass'],
                'group'       => 'csatar.csatar::lang.plugin.component.recordList.defaultSorting.defaultSorting',
                'showExternalParam' => false
            ],
            'sortDirection' => [
                'title'       => 'csatar.csatar::lang.plugin.component.recordList.defaultSorting.listSortDirection',
                'type'        => 'dropdown',
                'showExternalParam' => false,
                'group'       => 'csatar.csatar::lang.plugin.component.recordList.defaultSorting.defaultSorting',
                'options'     => [
                    'asc'     => 'csatar.csatar::lang.plugin.component.recordList.defaultSorting.listSortDirectionAsc',
                    'desc'    => 'csatar.csatar::lang.plugin.component.recordList.defaultSorting.listSortDirectionDesc',
                ]
            ]
        ];

        return array_merge($properties, $parentProperties);
    }

    public function onRun()
    {
        $this->prepareVars();

        $this->records = $this->page['records'] = $this->listRecords();
    }

    protected function prepareVars()
    {
        $this->noRecordsMessage = $this->page['noRecordsMessage'] = Lang::get($this->property('noRecordsMessage'));
        $this->pageParam = $this->page['pageParam'] = $this->paramName('pageNumber');
        $this->columnsConfig = $this->page['columnsConfig'] = $this->makeConfig($this->getColumnsConfigFile());
        $this->tableHeaderConfig = $this->page['tableHeaderConfig'] = $this->getTableHeaderConfig();
        $this->tableRowConfig = $this->page['tableRowConfig'] = $this->getTableRowConfig();

        $this->detailsKeyColumn = $this->page['detailsKeyColumn'] = $this->property('detailsKeyColumn');
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

    public function getColumnsConfigFile()
    {
        $modelClass = $this->property('modelClass');
        $columnsConfigFile = $this->property('columnsConfigFile');
        return '$/' . str_replace('\\', '/', strtolower($modelClass)) . '/' . $columnsConfigFile;
    }

    public function getTableHeaderConfig() {
        $headerConfig = [];
        foreach ($this->columnsConfig->columns as $column => $config) {
            if (!isset($config['recordList'])) {
                continue;
            }
            $headerConfig[$column]['label'] = Lang::get($config['label']);
        }

        return $headerConfig;
    }

    public function getTableRowConfig() {
        $rowConfig = [];
        foreach ($this->columnsConfig->columns as $column => $config) {
            if (!isset($config['recordList'])) {
                continue;
            }
            $rowConfig[$column]['attribute'] = $column;
            $rowConfig[$column]['type'] = $config['type'];
        }

        return $rowConfig;
    }
}