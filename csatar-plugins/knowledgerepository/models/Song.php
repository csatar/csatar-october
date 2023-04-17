<?php namespace Csatar\KnowledgeRepository\Models;

use Auth;
use Csatar\Csatar\Models\PermissionBasedAccess;
use Lang;
use Model;

/**
 * Model
 */
class Song extends PermissionBasedAccess
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    use \October\Rain\Database\Traits\Nullable;

    use \Csatar\Csatar\Traits\History;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public $fillable = [
        'association_id',
        'title',
        'author',
        'text',
        'link',
        'note',
        'uploader_csatar_code',
        'approver_csatar_code',
        'approved_at',
        'version',
        'song_type_id',
        'folk_song_type_id',
        'region_id',
        'rhythm_id',
    ];

    public $additionalFieldsForPermissionMatrix = [
        'created_at',
    ];

    public $nullable = [
        'title',
        'author',
        'text',
        'link',
        'note',
        'uploader_csatar_code',
        'approver_csatar_code',
        'approved_at',
        'version',
        'song_type_id',
        'folk_song_type_id',
        'region_id',
        'rhythm_id',
    ];

    protected $appends = ['extended_name'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_songs';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'songtype' => [
            '\Csatar\KnowledgeRepository\Models\SongType',
            'key' => 'song_type_id',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.menu.knowledgeRepositoryParameters.songType'
        ],
        'folksongtype' => [
            '\Csatar\KnowledgeRepository\Models\FolkSongType',
            'key' => 'folk_song_type_id',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.menu.knowledgeRepositoryParameters.folkSongType'
        ],
        'region' => [
            '\Csatar\KnowledgeRepository\Models\Region',
            'key' => 'region_id',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.menu.knowledgeRepositoryParameters.region'
        ],
        'rhythm' => [
            '\Csatar\KnowledgeRepository\Models\FolkSongRhythm',
            'key' => 'rhythm_id',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.menu.knowledgeRepositoryParameters.folkSongRhythm'
        ],
        'uploaderscout' => [
            '\Csatar\Csatar\Models\Scout',
            'key' => 'uploader_csatar_code',
            'otherKey' => 'ecset_code',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.general.proposerCsatarCode'
        ],
        'approverscout' => [
            '\Csatar\Csatar\Models\Scout',
            'key' => 'approver_csatar_code',
            'otherKey' => 'ecset_code',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.general.approverCsatarCode'
        ],
        'association' => [
            '\Csatar\Csatar\Models\Association',
            'formBuilder' => [
                'requiredBeforeRender' => true,
            ],
        ]
    ];

    public $belongsToMany = [
        'agegroups' => [
            '\Csatar\Csatar\Models\AgeGroup',
            'table' => 'csatar_knowledgerepository_age_group_song',
            'label' => 'csatar.csatar::lang.plugin.admin.ageGroups.ageGroups',
            'scope' => [self::class, 'filterAgeGroupByAssociation']
        ],
        'trialsystems' => [
            '\Csatar\KnowledgeRepository\Models\TrialSystem',
            'table' => 'csatar_knowledgerepository_song_trial_system',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.trialSystem.trialSystems',
        ],
    ];

    public $attachMany = [
        'attachements' => ['System\Models\File'],
    ];

    public static function getOrganizationTypeModelNameUserFriendly()
    {
        return Lang::get('csatar.knowledgerepository::lang.plugin.admin.menu.knowledgeRepositoryParameters.song');
    }

    public function beforeCreate()
    {
        if (empty($this->uploader_csatar_code)) {
            $scout = Auth::user()->scout;

            $this->uploader_csatar_code = $scout->ecset_code;
        }
    }

    public static function filterAgeGroupByAssociation($query, $related)
    {
        if (!isset($related->association_id)) {
            return $query->where('id', 0);
        }
        return $query->where('association_id', $related->association_id);
    }

    public static function filterTrialSystemByAssociation($query, $related)
    {
        if (!isset($related->association_id)) {
            return $query->where('id', 0);
        }
        return $query->where('association_id', $related->association_id);
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function getUploaderScout() {
        return $this->uploader_csatar_code ? $this->uploaderscout : null;
    }

    public function getApproverScout() {
        return $this->approver_csatar_code ? $this->approverscout : null;
    }

    public function getAssociationId()
    {
        return $this->association_id;
    }

    public function getAssociation()
    {
        return $this->association ?? null;
    }

    /**
     * Override the getExtendedNameAttribute function
     */
    public function getExtendedNameAttribute()
    {
        return isset($this->attributes['name']) ? $this->attributes['name'] . ' ' . Lang::get('csatar.csatar::lang.plugin.admin.district.nameSuffix') : null;
    }
}
