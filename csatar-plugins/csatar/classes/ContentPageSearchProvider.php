<?php namespace Csatar\Csatar\Classes;

use Csatar\Csatar\Models\ContentPage;
use OFFLINE\SiteSearch\Classes\Providers\ResultsProvider;

class ContentPageSearchProvider extends ResultsProvider
{
    public function search()
    {
        // The controller is used to generate page URLs.
        $controller = \Cms\Classes\Controller::getController() ?? new \Cms\Classes\Controller();

        // Get your matching models
        $matching = ContentPage::where('title', 'like', "%{$this->query}%")
            ->orWhere('content', 'like', "%{$this->query}%")
            ->get();

        if($matching->count() == 0) {
            $queryHtmlEndcoded = htmlentities($this->query);
            $matching = ContentPage::where('title', 'like', "%{$queryHtmlEndcoded}%")
                ->orWhere('content', 'like', "%{$queryHtmlEndcoded}%")
                ->get();
            $this->query = $queryHtmlEndcoded;
        }

        // Create a new Result for every match
        foreach ($matching as $match) {
            $result            = $this->newResult();
            $model = strtolower(('\\'.$match->model_type)::getOrganizationTypeModelNameUserFriendly());

            $result->relevance = 1;
            $result->title     = $match->title;
            $result->text      = $match->content;
            $result->url       = $controller->pageUrl($model, [ 'id'=> $match->model_id ] );
            $result->thumb     = $match->image;

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
