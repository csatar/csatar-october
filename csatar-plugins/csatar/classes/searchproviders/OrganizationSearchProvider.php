<?php namespace Csatar\Csatar\Classes\SearchProviders;

use Carbon\Carbon;
use Csatar\Csatar\Models\OrganizationBase;
use Db;
use Lang;
use OFFLINE\SiteSearch\Classes\Providers\ResultsProvider;

class OrganizationSearchProvider extends ResultsProvider
{
    private const RESULT_HIERARCHY = [
        '\Csatar\Csatar\Models\Scout' => 1,
        '\Csatar\Csatar\Models\Patrol' => 2,
        '\Csatar\Csatar\Models\Troop' => 3,
        '\Csatar\Csatar\Models\Team' => 4,
        '\Csatar\Csatar\Models\District' => 5,
        '\Csatar\Csatar\Models\Association' => 6,
    ];
    public function search()
    {
        // The controller is used to generate page URLs.
        $controller = \Cms\Classes\Controller::getController() ?? new \Cms\Classes\Controller();

        $organizationBaseChildClasses = OrganizationBase::getAllChildClasses();

        usort($organizationBaseChildClasses, function($a, $b) {
            return self::RESULT_HIERARCHY[$a] <=> self::RESULT_HIERARCHY[$b];
        });

        foreach ($organizationBaseChildClasses as $childClass) {
            // Get your matching models
            $matching = null;
            $searchableColumns = $childClass::getSearchableColumns();
            $matching = $childClass::where(function ($query) use ($searchableColumns) {
                                return $query->when(in_array('family_name', $searchableColumns), function ($query) { //dd('family_name', $this->query, $query->toSql());
                                    return $query->where('family_name', 'like', "%{$this->query}%");
                                })->when(in_array('given_name', $searchableColumns), function ($query) { //dd('given_name', $this->query, $query->toSql());
                                    return $query->orWhere('given_name', 'like', "%{$this->query}%");
                                })->when(in_array('family_name', $searchableColumns) && in_array('given_name', $searchableColumns), function ($query) {
                                    return $query->orWhere(Db::raw("CONCAT(`family_name`, ' ', `given_name`)"), 'like', "%{$this->query}%");
                                });
                            })->when($childClass == '\\Csatar\Csatar\Models\Scout', function ($query){
                                return $query->where(function ($query){
                                    return $query->where('inactivated_at', '>', Carbon::now()->subYears(5))
                                        ->orWhereNull('inactivated_at');
                                });
                            })->when(in_array('name', $searchableColumns), function ($query) use($searchableColumns) { //dd('name', $this->query, $query);
                                return $query->orWhere('name', 'like', "%{$this->query}%");
                            })->get();

            // Create a new Result for every match
            foreach ($matching as $match) {
                $result            = $this->newResult();
                $model = str_slug($childClass::getOrganizationTypeModelNameUserFriendly());

                $result->relevance = 100 - (self::RESULT_HIERARCHY[$childClass] ?? 1);
                $result->title     = $match->extendedName != '' ? $match->extendedName : $match->name;
                $result->url       = $controller->pageUrl($model, [ 'id' => $match->id ] );
                if ( $childClass == '\\Csatar\Csatar\Models\Scout' ) {
                    $result->url      = $controller->pageUrl('tag', [ 'ecset_code' => $match->ecset_code ] );
                    $result->text     = $match->inactivated_at === null ?
                        Lang::get('csatar.csatar::lang.plugin.admin.scout.activeMember') :
                        Lang::get('csatar.csatar::lang.plugin.admin.scout.inactiveMember');
                    $result->text     .= ' ' . $match->getParentTree();
                } else {
                    $result->text     = $match->getParentTree();
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
