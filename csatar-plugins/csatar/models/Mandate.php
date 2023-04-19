<?php namespace Csatar\Csatar\Models;

use Csatar\Csatar\Models\MandateType;
use Csatar\Csatar\Models\Troop;
use Csatar\Csatar\Models\Patrol;
use DateTime;
use Input;
use Lang;
use Model;
use ValidationException;

/**
 * Model
 */
class Mandate extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    use \Csatar\Csatar\Traits\History;

    protected $dates = ['deleted_at'];

    protected $touches = ['scout'];

    protected $appends = ['mandate_team', 'scout_team'];

    public $ignoreValidation = false;

    function __construct(array $attributes = []) {
        parent::__construct($attributes);
        $this->setValidationAttributeNames([
            'mandate_type' => e(trans('csatar.csatar::lang.plugin.admin.mandateType.mandateType')),
            'mandate_model' => e(trans('csatar.csatar::lang.plugin.admin.mandateType.organizationTypeModelName')),
            'scout' => e(trans('csatar.csatar::lang.plugin.admin.mandateType.scout')),
            'start_date' => e(trans('csatar.csatar::lang.plugin.admin.mandateType.startDate')),
        ]);

        // on the BE, when clicking the Mandate Create button: modify the mandate_model relation type, in order to the mandate_model relation to be set when creating a new mandate
        $this->belongsTo['mandate_model'] = Input::get('Association') !== null ?
            Association::getModelName() :
            (Input::get('District') !== null ?
                District::getModelName() :
                (Input::get('Team') !== null ?
                   Team::getModelName() :
                    (Input::get('Troop') !== null ?
                       Troop::getModelName() :
                        (Input::get('Patrol') !== null ?
                            Patrol::getModelName() :
                            OrganizationBase::getModelName()))));

        // on the BE, when changing the Mandate Type on the form, which is shown after the Mandate Create button has been clicked: modify the mandate_model relation type, in order to the mandate_model relation to be set
        if ($this->belongsTo['mandate_model'] == OrganizationBase::getModelName()) {
            $mandate         = Input::get('Mandate');
            $mandate_type_id = $mandate ? $mandate['mandate_type'] : null;
            $mandate_type    = $mandate_type_id ? MandateType::find($mandate_type_id) : null;
            $this->belongsTo['mandate_model'] = $mandate_type ? $mandate_type->organization_type_model_name : OrganizationBase::getModelName();
        }

        if (isset($this->mandate_model_type) && $this->mandate_model_type != '\Csatar\Csatar\Models\Troop') {
            unset($this->belongsTo['mandate_troop']);
        }

        if (isset($this->mandate_model_type) && $this->mandate_model_type != '\Csatar\Csatar\Models\Patrol') {
            unset($this->belongsTo['mandate_patrol']);
        }
    }

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_mandates';

    /**
     * @var string The model name, from which the items should be listed from on the FE form, when clicking the add relation button.
     */
    public static $relatedModelNameForFormBuilder = '\Csatar\Csatar\Models\MandateType';

    /**
     * @var string The field from the model, in which the selected item's value should be set on the FE form, when clicking Next, after adding the relation.
     */
    public static $relatedFieldForFormBuilder = 'mandate_type';

    /**
     * @var string Are duplicate records be allowed to be added on the FE form, when clicking the add relation button.
     */
    public static $relatedModelAllowDuplicates = true;

    /**
     * @var array Validation rules
     */
    public $rules = [
        'mandate_type' => 'required',
        'mandate_model' => 'required',
        'scout' => 'required',
        'start_date' => 'required',
        'end_date' => 'nullable',
    ];

    /**
     * Add custom validation
     */
    public function beforeValidate()
    {
        if (!$this->ignoreValidation) {
            // if the validation is called from backend: check that the end date is not after the start date
            // exception to this rule when scout team is changed and he has mandates that did not start yet, but should expire because of team change
            if (isset($this->start_date) && isset($this->end_date) && (new DateTime($this->end_date) < new DateTime($this->start_date))) {
                throw new ValidationException(['end_date' => Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.endDateBeforeStartDate')]);
            }

            // check that this mandate doesn't overlap with an already existing one
            $this->validateWithExistingMandates($this->attributes);
        }
    }

    public function beforeValidateFromForm(&$data)
    {
        // if the validation is called from the form: check that the end date is not after the start date
        if (isset($data) && !empty($data['start_date']) && !empty($data['end_date']) && (new DateTime($data['end_date']) < new DateTime($data['start_date']))) {
            throw new ValidationException(['end_date' => Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.endDateBeforeStartDate')]);
        }

        // set mandate_type_id and mandate_model_id
        $data['mandate_type_id']  = isset($data['mandate_type']) ? $data['mandate_type'] : null;
        $data['mandate_model']    = Input::get('recordKeyValue');
        $data['mandate_model_id'] = isset($data['mandate_model']) ? $data['mandate_model'] : null;
        $data['scout_id']         = isset($data['scout']) ? $data['scout'] : null;

        // check that this mandate doesn't overlap with an already existing one
        $this->validateWithExistingMandates($data);
    }

    public function initFromForm($record)
    {
        // from the Organization page
        $modelName           = $record::getModelName();
        $this->mandate_model = $record;
        $this->mandate_model_type = $modelName;
        $this->mandate_model_name = $record->extendedName;
    }

    public function beforeSave()
    {
        // set further mandate model data
        $mandateType = MandateType::find($this->mandate_type_id);
        if (isset($mandateType)) {
            $this->mandate_model_type = $mandateType->organization_type_model_name;

            $organizationUnit         = ($mandateType->organization_type_model_name)::find($this->mandate_model_id);
            $this->mandate_model_name = isset($organizationUnit) ? $organizationUnit->extendedName : '';
        }
    }

    public function beforeSaveFromForm(&$data)
    {
        // set further mandate model data
        $mandateType = MandateType::find($data['mandate_type_id']);
        if (isset($mandateType)) {
            $data['mandate_model_type'] = $mandateType->organization_type_model_name;

            $organizationUnit           = ($mandateType->organization_type_model_name)::find($data['mandate_model_id']);
            $data['mandate_model_name'] = isset($organizationUnit) ? $organizationUnit->extendedName : '';
        }

        // if the end_date is an empty string, then set it to null
        $data['end_date'] = !empty($data['end_date']) ? $data['end_date'] : null;
    }

    private function validateWithExistingMandates($data)
    {
        if ($data['mandate_type_id'] == null || $data['mandate_model_id'] == null || $data['start_date'] == null) {
            return;
        }

        $id          = array_key_exists('id', $data) ? $data['id'] : null;
        $mandateType = MandateType::find($data['mandate_type_id']);
        if (empty($mandateType)) {
            return;
        }

        $organizationUnit = ($mandateType->organization_type_model_name)::find($data['mandate_model_id']);
        $startDate        = new DateTime($data['start_date']);
        $endDate          = isset($data['end_date']) ? new DateTime($data['end_date']) : null;
        $scoutId          = array_key_exists('scout_id', $data) ? ($data['scout_id']) : null;
        $mandates         = Mandate::where('mandate_type_id', $data['mandate_type_id'])->where('mandate_model_id', $data['mandate_model_id'])->get();

        foreach ($mandates as $mandate) {
            // if we are editing the mandate: the mandate shouldn't be compared to itself
            if ($id == $mandate->id) {
                continue;
            }

            // check that the date isn't (partially) overlapping with a different assignment for the same period: if the overlapping is not enabled or if it's the same user: overlap if max(start1, start2) < min(end1, end2)
            if (!$mandateType->overlap_allowed && $mandate->scout_id . '' != $scoutId) {
                $mandateStartDate = new DateTime($mandate['start_date']);
                $mandateEndDate   = isset($mandate['end_date']) ? new DateTime($mandate['end_date']) : null;

                if (($endDate !== null && $mandateEndDate !== null && max($startDate, $mandateStartDate) < min($endDate, $mandateEndDate)) ||
                    ($endDate == null && max($startDate, $mandateStartDate) < $mandateEndDate) ||
                    ($mandateEndDate == null && max($startDate, $mandateStartDate) < $endDate) ||
                    ($endDate == null && $mandateEndDate == null)) {
                        throw new ValidationException(['start_date' => Lang::get('csatar.csatar::lang.plugin.admin.mandate.overlappingMandateError')]);
                }
            }
        }
    }

    /**
     * Handle the mandate type - mandate model dependencies
     */
    public function filterFields($fields, $context = null)
    {
        $this->mandate_model_type = !$this->mandate_model_type ? $this->belongsTo['mandate_model'] : $this->mandate_model_type;
        $mandate_model_id         = null;
        $mandate_model_type       = null;

        // in case of troops and patrols, allow anyone from the team. In case of other organization units, allow only scouts from that organization unit
        if (!$this->mandate_model_id && !$this->mandate_model) {
            // we are on a create form on the FE
            $inputData = Input::get('data');
            switch ($this->mandate_model_type) {
                case Troop::getModelName():
                case Patrol::getModelName():
                    $mandate_model_id   = $inputData['team'];
                    $mandate_model_type = Team::getModelName();
                    break;

                default:
                    break;
            }
        } else {
            // we are on an edit form
            if ($this->mandate_model_type == Troop::getModelName() || $this->mandate_model_type == Patrol::getModelName()) {
                $mandate_model_id   = $this->mandate_model ? $this->mandate_model->team_id : null;
                $mandate_model_type = Team::getModelName();
            } else {
                $mandate_model_id   = $this->mandate_model_id;
                $mandate_model_type = $this->mandate_model_type;
            }
        }

        // from the Organization pages: populate the Scouts dropdown
        $scouts  = Scout::whereNull('inactivated_at')->organization($mandate_model_type, $mandate_model_id)->get();
        $options = [];
        foreach ($scouts as $item) {
            $options[$item->id] = $item->name;
        }

        asort($options);
        $fields->scout->options = $options;
    }

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'scout_id',
        'mandate_type_id',
        'mandate_model_id',
        'mandate_model_type',
        'mandate_model_name',
        'start_date',
        'end_date',
        'comment',
    ];

    /**
     * Relations
     */
    public $belongsTo = [
        'scout' => '\Csatar\Csatar\Models\Scout',
        'mandate_type' => '\Csatar\Csatar\Models\MandateType',
        'mandate_model' => '\Csatar\Csatar\Models\OrganizationBase',
        'mandate_troop' => [
            '\Csatar\Csatar\Models\Troop',
            'key' => 'mandate_model_id',
        ],
        'mandate_patrol' => [
            '\Csatar\Csatar\Models\Patrol',
            'key' => 'mandate_model_id',
        ],
    ];

    public function getMandateModelAttribute()
    {
        if ($this->mandate_model_type == '\Csatar\Csatar\Models\OrganizationBase') {
            $this->mandate_model_type         = $this->mandate_type ? $this->mandate_type->organization_type_model_name : null;
            $this->belongsTo['mandate_model'] = $this->mandate_model_type;
        }

        return $this->mandate_model_type ? ($this->mandate_model_type)::find($this->mandate_model_id) : null;
    }

    public function getIsHiddenFrontendAttribute()
    {
        return $this->mandate_type->is_hidden_frontend ?? false;
    }

    /**
     * Scope a query to only include mandates of a given type.
     */
    public function scopeMandateModelType($query, $model = null)
    {
        $currentDate = (new DateTime())->format('Y-m-d');
        return $model
            ? $query->where('mandate_model_type', $model::getModelName())
                ->where('start_date', '<=', $currentDate)
                ->where(function ($query) use ($currentDate) {
                    return $query->whereNull('end_date')->orWhere('end_date', '>=', $currentDate);
                })
            : $query->whereNull('id');
    }

    public function scopeInactiveMandatesInOrganization($query, $model)
    {
        $currentDate = (new DateTime())->format('Y-m-d');
        return $model
            ? $query->where('mandate_model_type', $model::getModelName())
                ->where(function ($query) use ($currentDate) {
                    return $query->where('start_date', '>', $currentDate)
                        ->orWhere('end_date', '<', $currentDate);
                })
                ->orderBy('end_date', 'desc')
            : $query->whereNull('id');
    }

    public function scopeInactive($query)
    {
        $currentDate = (new DateTime())->format('Y-m-d');
        return $query->where(function ($query) use ($currentDate) {
            return $query->where('start_date', '>', $currentDate)
                ->orWhere('end_date', '<', $currentDate);
        });
    }

    public function getMandateTeamAttribute(): string
    {
        if ($this->mandate_model_type == '\Csatar\Csatar\Models\Patrol') {
            return $this->mandate_patrol->team->extended_name ?? '';
        }

        if ($this->mandate_model_type == '\Csatar\Csatar\Models\Troop') {
            return $this->mandate_troop->team->extended_name ?? '';
        }

        return '';
    }

    public function getTeamForPatrolAndTroopMandates()
    {
        if ($this->mandate_model_type == '\Csatar\Csatar\Models\Patrol') {
            $team = Patrol::find($this->mandate_model_id)->team;
            return $team ? [ 'mandate_id' => $this->id, 'team_name' => $team->extendedName ] : [];
        }

        if ($this->mandate_model_type == '\Csatar\Csatar\Models\Troop') {
            $team = Troop::find($this->mandate_model_id)->team;
            return $team ? [ 'mandate_id' => $this->id, 'team_name' => $team->extendedName ] : [];
        }

        return [];
    }

    public function getScoutTeamAttribute(): string
    {
        if (isset($this->scout) && $this->scout->team->extendedName) {
            return $this->scout->team->extendedName;
        }

        return '';
    }

    public function getOrganizationOptions($scopes = null): array
    {
        if (!empty($scopes['association']->value)) {
            $options = self::associations(array_keys($scopes['association']->value))->get();
        } else {
            $options = self::all();
        }

        return $options->map(function ($item) {
                return [ 'name' => $item->mandate_model->extendedName, 'id' => $item->mandate_model->id . $item->mandate_model->getModelName()];
            })
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getTeamOptionsForPatrolAndTroopMandates($scopes = null): array
    {

        if (!empty($scopes['association']->value)) {
            $options = self::associations(array_keys($scopes['association']->value))->get();
        } else {
            $options = self::all();
        }

        return $options->filter(function ($item) {
            if (!empty($item->mandate_team)) {
                    return $item;
                }
            })
            ->map(function ($item) {
                return $item->getTeamForPatrolAndTroopMandates();
            })
            ->mapToGroups(function ($item, $key) {
                return [$item['team_name'] => $item['mandate_id']];
            })
            ->map(function ($item) {
                return $item->implode('|');
            })
            ->flip()
            ->toArray();
    }

    public function scopeAssociations($query, array $associationIds)
    {
        return $query->whereHas('mandate_type', function ($query) use ($associationIds) {
            $query->whereIn('association_id', $associationIds);
        });
    }

    public static function setAllMandatesExpiredInOrganization($organization) {
        self::where('mandate_model_type', $organization->getModelName())
            ->where('mandate_model_id', $organization->id)
            ->update(['end_date' => date('Y-m-d')]);
    }

}
