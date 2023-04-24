<?php 
namespace Csatar\Csatar\Classes\SearchProviders;

use OFFLINE\SiteSearch\Classes\Providers\ResultsProvider;
use PolloZen\SimpleGallery\Models\Gallery;
use Lang;

class GallerySearchProvider extends ResultsProvider
{
    public function search()
    {
        // The controller is used to generate page URLs.
        $controller = \Cms\Classes\Controller::getController() ?? new \Cms\Classes\Controller();

        // Get your matching models
        $matching = Gallery::where('name', 'like', "%{$this->query}%")
            ->orWhere('description', 'like', "%{$this->query}%")
            ->get();

        if ($matching->count() == 0) {
            $queryHtmlEndcoded = htmlentities($this->query);
            $matching          = Gallery::where('name', 'like', "%{$queryHtmlEndcoded}%")
                ->orWhere('description', 'like', "%{$queryHtmlEndcoded}%")
                ->get();
            $this->query       = $queryHtmlEndcoded;
        }

        // Create a new Result for every match
        foreach ($matching as $match) {
            $result            = $this->newResult();
            $parentModel       = isset($match->galleryPivot[0]) ? $match->galleryPivot[0]->model : null;
            $result->relevance = 1;
            $result->title     = SearchResultsHelper::getMatchForQuery($this->query, $match->name, $match->description);
            if ($parentModel) {
                $result->url  = $controller->pageUrl(str_slug($parentModel::getOrganizationTypeModelNameUserFriendly()), [ 'id' => $parentModel->id ] );
                $result->text = Lang::get('csatar.csatar::lang.plugin.admin.gallery.gallery') . ' - ' . $parentModel->extended_name . ' ' . ($parentModel->getParentTree() ?? '') ;
            }

            $result->thumb = $match->image;

            // Add the results to the results collection
            $this->addResult($result);
        }

        return $this;
    }

    public function displayName()
    {
        return e(trans('csatar.csatar::lang.plugin.admin.general.searchResult'));
    }

    public function identifier()
    {
        return e(trans('csatar.csatar::lang.plugin.admin.general.contentPage'));
    }
}
