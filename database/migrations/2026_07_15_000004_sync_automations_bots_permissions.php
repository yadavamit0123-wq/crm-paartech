<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Live tenants often skip re-seed — automations/bots pages were behind
 * permission middleware but Admin role kabhi sync nahi hua → 403.
 * Ye migration permissions ensure karke Admin/Manager/Marketing pe attach karti hai.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('roles') || ! Schema::hasTable('role_permission')) {
            return;
        }

        $needed = [
            ['name' => 'Manage Automations', 'slug' => 'automations.manage', 'group' => 'communication'],
            ['name' => 'Manage Bots', 'slug' => 'bots.manage', 'group' => 'communication'],
        ];

        $permIds = [];
        foreach ($needed as $perm) {
            $existing = DB::table('permissions')->where('slug', $perm['slug'])->first();
            if ($existing) {
                $permIds[] = $existing->id;
            } else {
                $permIds[] = DB::table('permissions')->insertGetId(array_merge($perm, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // Admin / Manager / Marketing system roles — plus koi role jiske paas already broadcasts.manage ya settings.manage ho
        $roleIds = DB::table('roles')
            ->whereIn('slug', ['admin', 'manager', 'marketing'])
            ->pluck('id')
            ->all();

        $settingsPermId = DB::table('permissions')->where('slug', 'settings.manage')->value('id');
        $broadcastsPermId = DB::table('permissions')->where('slug', 'broadcasts.manage')->value('id');

        if ($settingsPermId || $broadcastsPermId) {
            $extra = DB::table('role_permission')
                ->whereIn('permission_id', array_filter([$settingsPermId, $broadcastsPermId]))
                ->pluck('role_id')
                ->unique()
                ->all();
            $roleIds = array_values(array_unique(array_merge($roleIds, $extra)));
        }

        foreach ($roleIds as $roleId) {
            foreach ($permIds as $permId) {
                $exists = DB::table('role_permission')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $permId)
                    ->exists();
                if (! $exists) {
                    DB::table('role_permission')->insert([
                        'role_id' => $roleId,
                        'permission_id' => $permId,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Keep permissions — only remove pivot rows we might have added is ambiguous; leave as-is.
    }
};
