<?php namespace Csatar\Csatar\Components;

use Auth;
use Csatar\Csatar\Models\GalleryModelPivot;
use PolloZen\SimpleGallery\Components\Gallery;
use PolloZen\SimpleGallery\Models\Gallery as GalleryModel;
use System\Models\File;
use Input;
use Illuminate\Support\Facades\Log;
use Redirect;
use ValidationException;
use Lang;
use Resizer;

class CsatarGallery extends Gallery
{
    public $model;
    public $gallery_id;
    public $permission_to_edit;

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

    public function onRun()
    {
        $this->prepareMarkup();

        $this->permission_to_edit = $this->getPermissionToEdit();
        $modelName = "Csatar\Csatar\Models\\" . $this->property('model_name');
        $this->model = $modelName::find($this->property('model_id'));

        $galleryIDs = GalleryModelPivot::select('gallery_id')->where('model_type', $modelName)->where('model_id', $this->property('model_id'))->where('parent_id', null)->get();
        $galleries = [];
        foreach ($galleryIDs as $id) {
            $gallery = GalleryModel::find($id);
            $galleries[] = $gallery['0'];
        }
        $this->galleries = $this->page['galleries'] = $galleries;
    }

    private function prepareMarkup()
    {
        $this->galleryMarkup = $this->property('markup');
        if($this->property('markup')=='plugin'){
            $this->addCss('/plugins/pollozen/simplegallery/assets/css/owl.carousel.min.css');
            $this->addCss('/plugins/pollozen/simplegallery/assets/css/owl.theme.default.min.css');
            $this->addJs('/plugins/pollozen/simplegallery/assets/js/owl.awesome.carousel.min.js');
            $this->addJs('/plugins/pollozen/simplegallery/assets/js/pz.js');
        }
        if($this->property('markup')=='masonry')
        {
            $this->addCss('/plugins/pollozen/simplegallery/assets/css/galleries.css');
            $this->addJs('/plugins/pollozen/simplegallery/assets/js/imagesloaded.pkgd.min.js');
            $this->addJs('/plugins/pollozen/simplegallery/assets/js/isotope.pkgd.min.js');
            $this->addJs('/plugins/pollozen/simplegallery/assets/js/isotope.pkgd.min.js');
            $this->addJs('/plugins/pollozen/simplegallery/assets/js/pz.js');
        }
    }

    protected function getGallery()
    {
        $gallery = GalleryModel::find($this->gallery_id);
        return $gallery;
    }

    public function onOpenCreateForm()
    {
        $renderPartial = "#galleries";

        if (post('parent_id')) {
            $this->parent_id = $this->page['parent_id'] = post('parent_id');
        }

        return [
            $renderPartial => $this->renderPartial('@create')
        ];
    }

    public function onCreateGallery()
    {
        $gallery = new GalleryModel();
        $gallery->name = post('name');
        $gallery->description = post('description');
        $gallery->save();

        if (empty(Input::file('images'))) {
            throw new ValidationException(['images' => 'Képet feltölteni kötelező!']);
        }

        if (sizeof(Input::file('images')) > 30) {
            throw new ValidationException(['images' => 'Maximum 30 képet lehet feltölteni a galériához!']);
        }

        foreach (Input::file('images') as $file) {
            $newFile = new File();
            $newFile->data = $file;
            $newFile->save();

            list($width, $height) = getimagesize($newFile->getLocalPath());

            if ($width > 1920) {
                $resizer = new Resizer();
                $resizer::open($newFile->getLocalPath())
                    ->resize(1920, null, ['mode' => 'auto'])
                    ->save($newFile->getLocalPath());
            }

            $gallery->images()->add($newFile);
        }

        $gallery->save();

        $pivot = new GalleryModelPivot();
        $pivot->model_type = "Csatar\Csatar\Models\\" . $this->property('model_name');
        $pivot->model_id = $this->property('model_id');
        $pivot->gallery_id = $gallery->id;

        $pivot->parent_id = post('parent_id') ?: null;

        $pivot->save();


        return $this->onOpenGallery($gallery->id);
    }

    public function onSaveGallery()
    {
        $gallery = GalleryModel::find(post('gallery_id'));
        $gallery->name = post('name');
        $gallery->description = post('description');

        if (empty(Input::file('images')) && $gallery->images()->count() === 0) {
            throw new ValidationException(['images' => 'Képet feltölteni kötelező!']);
        }

        $imagesSize = empty(Input::file('images')) ? 0 : sizeof(Input::file('images'));

        if (($imagesSize + $gallery->images()->count()) > 30 ) {
            throw new ValidationException(['images' => 'Maximum 30 képet lehet feltölteni a galériához!']);
        }

        if (Input::file('images') != []) {
            foreach (Input::file('images') as $file) {
                $newFile = new File();
                $newFile->data = $file;
                $newFile->save();

                list($width, $height) = getimagesize($newFile->getLocalPath());

                if ($width > 1920) {
                    $resizer = new Resizer();
                    $resizer::open($newFile->getLocalPath())
                        ->resize(1920, null, ['mode' => 'auto'])
                        ->save($newFile->getLocalPath());
                }

                $gallery->images()->add($newFile);
            }
        }

        $gallery->save();
        $this->gallery = $this->page['gallery'] = $gallery;

        $this->gallery = $this->page['gallery'] = GalleryModel::find($gallery->id);
        $this->childGalleries = $this->page['childGalleries'] = $this->getChildGalleries($gallery->id);

        $this->permission_to_edit = $this->getPermissionToEdit();

        return [
            '#galleries' => $this->renderPartial('@galleryImages')
        ];
    }

    public function onOpenGallery($gallery_id = null)
    {
        $this->gallery = $this->page['gallery'] = GalleryModel::find($gallery_id ?: post('gallery_id'));
        $this->childGalleries = $this->page['childGalleries'] = $this->getChildGalleries($gallery_id ?: post('gallery_id'));

        $this->permission_to_edit = $this->getPermissionToEdit();

        return [
            '#galleries' => $this->renderPartial('@galleryImages')
        ];
    }

    public function onOpenEditForm($gallery_id = null)
    {
        $this->gallery = $this->page['gallery'] = GalleryModel::find($gallery_id ?: post('gallery_id'));
        return [
            '#galleries' => $this->renderPartial('@edit')
        ];
    }

    public function onDeleteGallery()
    {
        $gallery = GalleryModel::find(post('gallery_id'));
        $pivot = GalleryModelPivot::where('model_type', "Csatar\Csatar\Models\\" . $this->property('model_name'))->where('model_id', $this->property('model_id'))->where('gallery_id', $gallery->id)->first();
        $pivot->delete();
        $gallery->delete();

        $this->onRun();
        return [
            '#galleries' => $this->renderPartial('@default')
        ];
    }

    public function onRefreshPage()
    {
        if (post('parent_id') != null) {
            return $this->onOpenGallery(post('parent_id'));
        }

        if (post('gallery_id') != null) {
            return $this->onOpenGallery(post('gallery_id'));
        }

        $this->onRun();
        return [
            '#galleries' => $this->renderPartial('@default')
        ];
    }

    public function onReturnBack()
    {
        $pivot = GalleryModelPivot::where('model_type', "Csatar\Csatar\Models\\" . $this->property('model_name'))->where('model_id', $this->property('model_id'))->where('gallery_id', post('gallery_id'))->first();

        if ($pivot->parent_id != null) {
            return $this->onOpenGallery($pivot->parent_id);
        }

        $this->onRun();
        return [
            '#galleries' => $this->renderPartial('@default')
        ];
    }

    public function getChildGalleries($parent_id)
    {
        $modelName = "Csatar\Csatar\Models\\" . $this->property('model_name');
        $childIDs = GalleryModelPivot::select('gallery_id')->where('model_type', $modelName)->where('model_id', $this->property('model_id'))->where('parent_id', $parent_id)->get();
        $childGalleries = [];
        foreach ($childIDs as $id) {
            $childGallery = GalleryModel::find($id);
            $childGalleries[] = $childGallery['0'];
        }

        return $childGalleries;
    }

    public function getPermissionToEdit()
    {
        return Auth::user() ? true : false;
    }

    public function renderGalleryComponent()
    {
        $componentProps = [
            'formSlug' => 'galleria',
            'recordKeyParam' => 'id',
            'recordActionParam' => '',
            'readOnly' => false,
            'createRecordKeyword' => 'uj',
            'actionUpdateKeyword' => 'modositas',
            'actionDeleteKeyword' => 'torol'
        ];
        return $this->controller->renderComponent('basicForm', $componentProps);
    }

    public function onRemoveImage()
    {
        $gallery = GalleryModel::find(post('gallery_id'));
        $file_id = post('file_id');
        $file = File ::find($file_id);

        $gallery->images()->remove($file);
    }

    public function onSaveImage()
    {
        $gallery = GalleryModel::find(post('gallery_id'));
        $file_id = post('file_id');
        $file = File ::find($file_id);
        $file->title = post('title-' . $file_id);
        $file->description = post('description-' . $file_id);
        $file->save();
    }

}
