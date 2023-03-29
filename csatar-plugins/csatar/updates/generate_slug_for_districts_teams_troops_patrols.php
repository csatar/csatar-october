<?php namespace Csatar\Csatar\Updates;

use Seeder;
use Csatar\Csatar\Models\District;
use Csatar\Csatar\Models\Team;
use Csatar\Csatar\Models\Troop;
use Csatar\Csatar\Models\Patrol;

class Seeder1068 extends Seeder
{
    public function run()
    {
        $organizations = District::all() ?? [];

        foreach ($organizations as $organization) {
            $organization->generateSlugIfEmpty();
            $organization->ignoreValidation = true;
            $organization->forceSave();
        }

        $organizations = Team::all() ?? [];

        foreach ($organizations as $organization) {
            $organization->generateSlugIfEmpty();
            $organization->ignoreValidation = true;
            $organization->forceSave();
        }

        $organizations = Troop::all() ?? [];

        foreach ($organizations as $organization) {
            $organization->generateSlugIfEmpty();
            $organization->ignoreValidation = true;
            $organization->forceSave();
        }

        $organizations = Patrol::all() ?? [];

        foreach ($organizations as $organization) {
            $organization->generateSlugIfEmpty();
            $organization->ignoreValidation = true;
            $organization->forceSave();
        }
    }
}
