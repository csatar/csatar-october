<?php namespace Csatar\Forms\Components;

use Lang;
use Cms\Classes\ComponentBase;
use Csatar\Forms\Models\Form;
use Csatar\Forms\Traits\ManagesUploads;
use Csatar\Forms\Traits\AjaxControllerSimple;
use October\Rain\Exception\ApplicationException;

class BasicForm extends ComponentBase  {

    use AjaxControllerSimple;
    use ManagesUploads;

    /**
     * The relation model
     * @var type
     */
    public $relation = null;

    public $form_id = null;

    /**
     * Data model
     * @var Model
     */
    public $model = null;

    /**
     * Contains the rendered component
     * @var string
     */
    public $renderedComponent = null;

    /**
     * Initialise plugin and parse request
     */
    public function init() {

    }

    /**
     * Register component details
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'csatar.forms::lang.components.basicForm.name',
            'description' => 'csatar.forms::lang.components.basicForm.description',
        ];
    }

    /**
     * Register properties
     * @return array
     */
    public function defineProperties()
    {
        return [
            'form_id' => [
                'title'             => 'csatar.forms::lang.components.basicForm.properties.form_id.title',
                'description'       => 'csatar.forms::lang.components.basicForm.properties.form_id.description',
                'type'              => 'dropdown',
                'options'           => Form::lists('title', 'id'),
                'default'           => null,
                'validation'  => [
                    'required' => [
                        'message' => Lang::get('csatar.forms::lang.components.componentValidation.formNotSelected')
                    ]
                ]
            ]
        ];
    }

    /**
     * Returns a list of forms
     * @return type
     */
    public function getForms() {
        return Form::findMany($this->property('form'));
    }

    /**
     * Renders the frontend
     * @return mixed
     */
    public function onRun() {

        // Run application


        // Handle file upload requests
        if ($handler = $this->processUploads()) {
            return $handler;
        }

        // Render frontend
        $this->addCss('/modules/system/assets/ui/storm.css');
        $this->addJs('/modules/system/assets/ui/storm-min.js');
        $this->addJs('/plugins/csatar/forms/assets/vendor/dropzone/dropzone.js');
        $this->addJs('/plugins/csatar/forms/assets/js/uploader.js');
        $this->addJs('/plugins/csatar/forms/assets/js/positionValidationTags.js');

        $model_id = $this->param('model_id', 'new');
        $this->renderedComponent = $this->createForm($this->getForm(), $model_id);

    }

    private function getForm() {
        $form_id_param = $this->property('form');
        $form = Form::find($this->property('form_id'));
        if (!empty($form)) {
            $this->form_id = $form->id;
            return $form;
        } else {
            $error = e(trans('csatar.forms::lang.errors.formNotFound'));
            throw new ApplicationException($error . $this->page->title);
        }
    }

}
