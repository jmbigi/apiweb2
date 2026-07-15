<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Exception;

class AdminPermissonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    protected $permissions = [
        ['name' => 'create_music_score'],
        ['name' => 'edit_music_score'],
        ['name' => 'get_list_music_score'],
        ['name' => 'get_list_myMusicScores'],
        ['name' => 'get_music_score_info'],
        ['name' => 'get_music_score_file'],
        ['name' => 'get_report_music_score'],
        ['name' => 'anotate_music_score'],
        ['name' => 'edit_data_profile'],
        ['name' => 'get_mydata_profile'],
        ['name' => 'solicitate_composer_role'],
        ['name' => 'suggest_instrument'],
        ['name' => 'get_list_instruments'],
        ['name' => 'get_instrument'],
        ['name' => 'suggest_family_instrument'],
        ['name' => 'suggest_style_music'],
        ['name' => 'get_list_style_music'],
        ['name' => 'get_style_music'],
        ['name' => 'suggest_composer'],
        ['name' => 'get_list_composer'],
        ['name' => 'get_composer'],
        ['name' => 'read-profile'],
        ['name' => 'update-profile'],
        ['name' => 'delete-profile'],
    ];
     protected $roles = [
        [
            'name' => 'superadmin',
            'permissions' => [
                'create_music_score',
                'edit_music_score',
                'get_list_music_score',
                'get_musget_composert_myMusicScores',
                'get_report_music_score',
                'anotate_music_score',
                'get_music_score_file',
                'edit_data_profile',
                'get_mydata_profile',
                'solicitate_composer_role',
                'suggest_instrument',
                'get_list_instruments',
                'get_instrument',
                'suggest_family_instrument',
                'suggest_style_music',
                'get_list_style_music',
                'get_style_music',
                'suggest_composer',
                'get_list_composer',
                'get_composer',
                'read-profile',
                'update-profile',
                'delete-profile'
            ]
        ],
    ];
    public function run(): void
    {
        DB::beginTransaction();
        try{
            //upsert de permisos
            Permission::upsert($this->permissions,['name']);
            //upsert de roles
            $collectRoles = collect($this->roles);
            $onlyRoles = $collectRoles->map(function ($role){
                unset($role['permissions']);
                return $role;
            });
            Role::upsert($onlyRoles->toArray(),['name']);
            $collectRoles->map(function ($role){
                //whereIn de permisos
                $permissions = Permission::whereIn('name',$role['permissions'])->get()->pluck('id');
                //sync de permisos en role (este es belongsToMany, este no da problemas)
                $rol = Role::where('name',$role['name'])->firstOrFail();
                $rol->permissions()->sync($permissions);
            });
            //Commiteamos cambios
            DB::commit();

        }catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
    }
}
