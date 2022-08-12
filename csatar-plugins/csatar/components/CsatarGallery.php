<?php namespace Csatar\Csatar\Components;

use Csatar\Csatar\Models\GalleryModelPivot;
use PolloZen\SimpleGallery\Components\Gallery;
use PolloZen\SimpleGallery\Models\Gallery as GalleryModel;
use Input;

class CsatarGallery extends Gallery
{
    public $model;
    public $gallery_id;

    public function defineProperties()
    {
        return [
            'model_name' => [
                'title'             => 'csatar.csatar::lang.plugin.component.structure.properties.model_name.title',
                'description'       => 'csatar.csatar::lang.plugin.component.structure.properties.model_name.description',
                'type'              => 'string',
                'default'           => null
            ],
            'model_id' => [
                'title'             => 'csatar.csatar::lang.plugin.component.structure.properties.model_id.title',
                'description'       => 'csatar.csatar::lang.plugin.component.structure.properties.model_id.description',
                'type'              => 'string',
                'default'           => null
            ]
        ];
    }

    public function onRun(){
        $this->prepareMarkup();

        $modelName = "Csatar\Csatar\Models\\" . $this->property('model_name');
        $this->model = $modelName::find($this->property('model_id'));
        $this->gallery_id = GalleryModelPivot::where('model_type', $modelName)->where('model_id', $this->property('model_id'))->value('gallery_id');
        $this->gallery = $this->page['gallery'] = $this->getGallery();
    }

    private function prepareMarkup(){
        $this->galleryMarkup = $this->property('markup');
        if($this->property('markup')=='plugin'){
            $this->addCss('/plugins/pollozen/simplegallery/assets/css/owl.carousel.min.css');
            $this->addCss('/plugins/pollozen/simplegallery/assets/css/owl.theme.default.min.css');
            $this->addJs('/plugins/pollozen/simplegallery/assets/js/owl.awesome.carousel.min.js');
            $this->addJs('/plugins/pollozen/simplegallery/assets/js/pz.js');
        }
        if($this->property('markup')=='masonry'){
            $this->addCss('/plugins/pollozen/simplegallery/assets/css/galleries.css');
            $this->addJs('/plugins/pollozen/simplegallery/assets/js/imagesloaded.pkgd.min.js');
            $this->addJs('/plugins/pollozen/simplegallery/assets/js/isotope.pkgd.min.js');
            $this->addJs('/plugins/pollozen/simplegallery/assets/js/isotope.pkgd.min.js');
            $this->addJs('/plugins/pollozen/simplegallery/assets/js/pz.js');
        }
    }

    protected function getGallery(){
        $gallery = GalleryModel::find($this->gallery_id);
        return $gallery;
    }
}
