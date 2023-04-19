<?php
namespace Csatar\Csatar\Models;

use Csatar\Csatar\Models\Association;
use Csatar\Csatar\Models\MandatePermission;
use Csatar\Csatar\Models\MandateType;
use Db;

class PermissionExport extends \Backend\Models\ExportModel
{
    protected $fillable = [
        'association',
        'mandate_type',
    ];

    public function exportData($columns, $sessionKey = null)
    {
        if (empty($this->association) || empty($this->mandate_type)) {
            return;
        }

        $permissions = MandatePermission::with('mandateType.association')
            // one association - all mandateTypes - when $this->association !== 'all' && $this->mandate_type == 'all'
            // one association - one mandateType - when $this->association !== 'all' && $this->mandate_type !== 'all'
            ->when($this->association !== 'all', function ($permissions) {
                $permissions->whereHas('mandateType', function ($query) {
                    $query->when($this->mandate_type == 'all', function ($query) {
                        $query->where('association_id', $this->association);
                    });
                    $query->when($this->mandate_type !== 'all', function ($query) {
                        $query->where('id', $this->mandate_type);
                    });
                });
            })
            // all associations - all mandateTypes - when $this->association == 'all' && $this->mandate_type == 'all'
            // all associations - one mandateType - when $this->association == 'all' && $this->mandate_type !== 'all'
            ->when($this->association == 'all', function ($permissions) {
                $permissions->whereHas('mandateType', function ($query) {
                    $query->when($this->mandate_type == 'all', function ($query) {
                        $query->where('association_id', '>', 0);
                    });
                    $query->when($this->mandate_type !== 'all', function ($query) {
                        $query->where('id', $this->mandate_type);
                    });
                });
            })
            ->get();

        $data = [];

        foreach ($permissions as $permission) {
            $data[] = [
                'association'  => $permission->mandateType->association->name,
                'mandate_type' => $permission->mandateType->name,
                'own'          => $permission->own,
                'model'        => $permission->model,
                'field'        => $permission->field,
                'obligatory'   => $permission->obligatory,
                'create'       => $permission->create,
                'read'         => $permission->read,
                'update'       => $permission->update,
                'delete'       => $permission->delete,
            ];
        }

        return $data;
    }

    public function getAssociationOptions()
    {
        $associations        = Association::all()->lists('name', 'id');
        $associations['all'] = e(trans('csatar.csatar::lang.plugin.admin.admin.permissionsMatrix.all'));
        return $associations;
    }

    public function getMandateTypeOptions()
    {
        $mandateTypes = [];

        if ($this->association) {
            $mandateTypes = MandateType::where('association_id', $this->association)->lists('name', 'id');
        }

        if ($this->association == 'all') {
            $mandateTypes = MandateType::orderBy('name', 'asc')
                ->select(Db::raw("concat((SELECT `name_abbreviation` FROM csatar_csatar_associations
                    WHERE id = association_id), ' - ', name) as name, id"))
                ->lists('name', 'id');
        }

        $mandateTypes['all'] = e(trans('csatar.csatar::lang.plugin.admin.admin.permissionsMatrix.all'));

        return $mandateTypes;
    }

}
