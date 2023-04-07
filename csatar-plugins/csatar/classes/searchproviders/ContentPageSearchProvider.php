<?php namespace Csatar\Csatar\Classes\SearchProviders;

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

        if ($matching->count() == 0) {
            $queryHtmlEndcoded = htmlentities($this->query);
            $matching = ContentPage::where('title', 'like', "%{$queryHtmlEndcoded}%")
                ->orWhere('content', 'like', "%{$queryHtmlEndcoded}%")
                ->get();
            $this->query = $queryHtmlEndcoded;
        }

        // Create a new Result for every match
        foreach ($matching as $match) {
            $result            = $this->newResult();
            $modelNameUserFriendly = str_slug(('\\'.$match->model_type)::getOrganizationTypeModelNameUserFriendly());

            $result->relevance = 1;
            $processedText     = SearchResultsHelper::getMatchForQuery($this->query, $match->title ,$match->content);
            $result->title     = (string) $processedText;
            $result->text      = $match->model->extended_name . ' ' . ($match->model->getParentTree() ?? '') ;
            $result->url       = $controller->pageUrl($modelNameUserFriendly, [ 'id'=> $match->model_id ] );
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
