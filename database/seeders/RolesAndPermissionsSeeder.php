<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Delete existing roles and permissions
        Role::query()->delete();
        Permission::query()->delete();

        // Create permissions
        $permissions = [
            // Team permissions (plural form)
            'view teams',
            'view any teams',
            'create teams',
            'edit teams',
            'delete teams',

            // Team permissions (singular form)
            'view team',
            'view any team',
            'create team',
            'edit team',
            'delete team',

            // Team invitations permissions
            'view team-invitations',
            'view any team-invitations',
            'create team-invitations',
            'update team-invitations',
            'delete team-invitations',
            'restore team-invitations',
            'force delete team-invitations',

            // Team invitation permissions (singular)
            'view team-invitation',
            'view any team-invitation',
            'create team-invitation',
            'update team-invitation',
            'delete team-invitation',
            'restore team-invitation',
            'force delete team-invitation',

            // User permissions (plural form)
            'view users',
            'view any users',
            'create users',
            'edit users',
            'delete users',

            // User permissions (singular form)
            'view user',
            'view any user',
            'create user',
            'update user',
            'edit user',
            'delete user',

            // Skill permissions (plural form)
            'view skills',
            'view any skills',
            'create skills',
            'edit skills',
            'delete skills',

            // Skill permissions (singular form)
            'view skill',
            'view any skill',
            'create skill',
            'edit skill',
            'delete skill',

            // Practice permissions (plural form)
            'view practices',
            'view any practices',
            'create practices',
            'edit practices',
            'delete practices',

            // Practice permissions (singular form)
            'view practice',
            'view any practice',
            'create practice',
            'edit practice',
            'delete practice',

            // Feedback permissions
            'view feedback',
            'view any feedback',
            'create feedback',
            'edit feedback',
            'delete feedback',
            'view own feedback',
            'update feedback',

            // Coach-Student relationship permissions (plural)
            'view any coach-students',
            'view coach-students',
            'create coach-students',
            'update coach-students',
            'delete coach-students',
            'restore coach-students',
            'force delete coach-students',

            // Coach-Student relationship permissions (singular)
            'view any coach-student',
            'view coach-student',
            'create coach-student',
            'update coach-student',
            'delete coach-student',
            'restore coach-student',
            'force delete coach-student',

            'assign coaches',
            'view coach assignments',

            // Permissions for "Permission" resource itself (plural)
            'view any permissions',
            'view permissions',
            'create permissions',
            'update permissions',
            'delete permissions',
            'restore permissions',
            'force delete permissions',

            // Permissions for "Permission" resource itself (singular)
            'view any permission',
            'view permission',
            'create permission',
            'update permission',
            'delete permission',
            'restore permission',
            'force delete permission',

            // Permissions for "Role" resource (plural)
            'view any roles',
            'view roles',
            'create roles',
            'update roles',
            'delete roles',
            'restore roles',
            'force delete roles',

            // Permissions for "Role" resource (singular)
            'view any role',
            'view role',
            'create role',
            'update role',
            'delete role',
            'restore role',
            'force delete role',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $studentRole = Role::create(['name' => 'student']);
        $subjectMentorRole = Role::create(['name' => 'subject_mentor']);
        $personalCoachRole = Role::create(['name' => 'personal_coach']);

        // Assign all permissions to admin role
        $adminRole->givePermissionTo(Permission::all());

        // Assign appropriate permissions to student role
        $studentPermissions = [
            // Team viewing permissions
            'view teams',
            'view team',

            // Feedback permissions
            'view feedback',
            'view own feedback',

            // View skills and practices
            'view skills',
            'view skill',
            'view practices',
            'view practice',
        ];
        $studentRole->givePermissionTo($studentPermissions);

        // Assign appropriate permissions to subject mentor role
        $mentorPermissions = [
            // Team permissions
            'view teams',
            'view team',

            // Feedback management
            'view feedback',
            'view any feedback',
            'create feedback',
            'update feedback',

            // Skill and practice viewing
            'view skills',
            'view skill',
            'view practices',
            'view practice',

            // View students in teams
            'view users',
            'view user',
        ];
        $subjectMentorRole->givePermissionTo($mentorPermissions);

        // Assign appropriate permissions to personal coach role
        $coachPermissions = [
            // Team permissions
            'view teams',
            'view team',

            // Feedback management
            'view feedback',
            'view any feedback',
            'create feedback',
            'update feedback',

            // Skill and practice viewing
            'view skills',
            'view skill',
            'view practices',
            'view practice',

            // View students and coaching relationships
            'view users',
            'view user',
            'view coach-students',
            'view coach-student',
            'view coach assignments',
        ];
        $personalCoachRole->givePermissionTo($coachPermissions);
    }
}
