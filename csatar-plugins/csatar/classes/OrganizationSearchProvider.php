<?php namespace Csatar\Csatar\Classes;

use Csatar\Csatar\Models\OrganizationBase;
use OFFLINE\SiteSearch\Classes\Providers\ResultsProvider;

class OrganizationSearchProvider extends ResultsProvider
{
    public function search()
    {
        // The controller is used to generate page URLs.
        $controller = \Cms\Classes\Controller::getController() ?? new \Cms\Classes\Controller();

        $organizationBaseChildClasses = OrganizationBase::getAllChildClasses();

        foreach ($organizationBaseChildClasses as $childClass) {
            // Get your matching models
            $matching = null;
            $searchableColumns = $childClass::getSearchableColumns();
            $matching = $childClass::when(in_array('family_name', $searchableColumns), function ($query) { //dd('family_name', $this->query, $query->toSql());
                                return $query->where('family_name', 'like', "%{$this->query}%");
                            })->when(in_array('given_name', $searchableColumns), function ($query) { //dd('given_name', $this->query, $query->toSql());
                                return $query->orWhere('given_name', 'like', "%{$this->query}%");
                            })->when(in_array('name', $searchableColumns), function ($query) use($searchableColumns) { //dd('name', $this->query, $query);
                                return $query->orWhere('name', 'like', "%{$this->query}%");
                            })->get();

            // Create a new Result for every match
            foreach ($matching as $match) {
                $result            = $this->newResult();
                $model = str_slug($childClass::getOrganizationTypeModelNameUserFriendly());

                $result->relevance = 1;
                $result->title      = $match->extendedName != '' ?  $match->extendedName : $match->name;
                $result->url       = $controller->pageUrl($model, [ 'id'=> $match->id ] );
                if( $childClass == '\\Csatar\Csatar\Models\Scout' ) {
                    $result->url       = $controller->pageUrl('tag', [ 'ecset_code'=> $match->ecset_code ] );
                    $result->text     = $childClass::getOrganizationTypeModelNameUserFriendly();
                }
                $result->thumb     = $match->image;

                // Add the results to the results collection
                $this->addResult($result);
            }
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
