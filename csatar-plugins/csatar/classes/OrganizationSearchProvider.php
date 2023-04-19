<?php 
namespace Csatar\Csatar\Classes;

use Csatar\Csatar\Models\OrganizationBase;
use Carbon\Carbon;
use Db;
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
            $matching          = null;
            $searchableColumns = $childClass::getSearchableColumns();
            $matching          = $childClass::when($childClass == '\\Csatar\Csatar\Models\Scout', function ($query) use($searchableColumns) {
                            $query->where(function ($query) use($searchableColumns){
                                return $query->when(in_array('family_name', $searchableColumns) && in_array('given_name', $searchableColumns), function ($query) {
                                    return $query->orWhere(Db::raw("CONCAT(`family_name`, ' ', `given_name`)"), 'like', "%{$this->query}%");
                                });
                            });
                            $query->where(function ($query){
                                return $query->where('inactivated_at', '>', Carbon::now()->subYears(5))
                                    ->orWhereNull('inactivated_at');
                            });
                            return $query->orderByRaw("CONCAT(family_name, ' ', given_name) ASC");
                        })->when(in_array('name', $searchableColumns), function ($query) use($searchableColumns) {
                            return $query->orWhere('name', 'like', "%{$this->query}%");
                        })->when(($childClass != '\\Csatar\Csatar\Models\Scout' && $childClass != '\\Csatar\Csatar\Models\Team'), function ($query){
                            return $query->orderBy('name', 'ASC');
                        })->when($childClass == '\\Csatar\Csatar\Models\Team', function ($query){
                            return $query->orderByRaw('CAST(team_number AS UNSIGNED) ASC');
                        })->get();

            // Create a new Result for every match
            foreach ($matching as $key => $match) {
                $result = $this->newResult();
                $model  = str_slug($childClass::getOrganizationTypeModelNameUserFriendly());

                $result->relevance = $this->calculateRelevance($childClass, $key);
                $result->title     = $match->extendedName != '' ?  $match->extendedName : $match->name;
                $result->url       = $controller->pageUrl($model, [ 'id'=> $match->id ] );
                if ($childClass == '\\Csatar\Csatar\Models\Scout') {
                    $result->url  = $controller->pageUrl('tag', [ 'ecset_code'=> $match->ecset_code ] );
                    $result->text = $childClass::getOrganizationTypeModelNameUserFriendly();
                }

                $result->thumb = $match->image;

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

    public function calculateRelevance($childClass, $key): int
    {
        return 1000 + (count(self::RESULT_HIERARCHY) * 1000) - (self::RESULT_HIERARCHY[$childClass] * 1000) - $key;
    }
}
