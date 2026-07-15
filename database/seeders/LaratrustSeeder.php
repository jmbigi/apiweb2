<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LaratrustSeeder extends Seeder
{

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
        ['name' => 'delete-profile'],
        ['name' => 'read-profile'],
        ['name' => 'update-profile'],
    ];

    protected $roles = [
        [
            'name' => 'editorial',
            'permissions' => [
                'create_music_score',
                'edit_music_score',
                'get_music_score_info',
                'get_list_myMusicScores',
                'get_report_music_score',
                'get_music_score_file',
                'edit_data_profile',
                'get_mydata_profile',
                'suggest_instrument',
                'get_list_instruments',
                'get_instrument',
                'suggest_family_instrument',
                'suggest_style_music',
                'get_list_style_music',
                'get_style_music',
                'suggest_composer',
                'get_list_composer',
                'get_composer'
            ]
        ],
        [
            'name' => 'composer',
            'permissions' => [
                'create_music_score',
                'edit_music_score',
                'get_music_score_info',
                'get_list_myMusicScores',
                'get_report_music_score',
                'get_music_score_file',
                'edit_data_profile',
                'get_mydata_profile',
                'suggest_instrument',
                'get_list_instruments',
                'get_instrument',
                'suggest_family_instrument',
                'suggest_style_music',
                'get_list_style_music',
                'get_style_music',
                'suggest_composer',
                'get_list_composer',
                'get_composer'
            ]
        ],
        [
            'name' => 'musician',
            'permissions' => [
                'anotate_music_score',
                'get_list_music_score',
                'get_music_score_info',
                'get_music_score_file',
                'edit_data_profile',
                'solicitate_composer_role',
                'get_mydata_profile',
                'suggest_instrument',
                'get_list_instruments',
                'get_instrument',
                'suggest_family_instrument',
                'suggest_style_music',
                'get_list_style_music',
                'get_style_music',
                'suggest_composer',
                'get_list_composer',
                'get_composer'
            ]
        ],
    ];

    /**
     * Run the database seeds.
     */
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
            //
            $role['name'] = 'superadmin';
           // $rol = \App\Models\Role::where('name', $role['name'])->firstOrFail();
            $rol = Role::firstOrCreate(['name'=>'superadmin']);
            // Obtener los IDs de los permisos para el superadmin
            $superadminPermissions = Permission::whereIn('name', $this->permissions)->get()->pluck('id');
            // Sync de permisos en el rol 'superadmin'
            $rol->permissions()->sync($superadminPermissions);
            //
            //Commiteamos cambios
            DB::commit();

        }catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
        
    }
}
