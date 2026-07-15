<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Live tenants often skip re-seed — Inbox/Products pages were behind
 * permission middleware but roles kabhi sync nahi hue → 403.
 * Nav still showed the tabs (no permission gate).
 * Ye migration permissions ensure karke intended roles pe attach karti hai.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('roles') || ! Schema::hasTable('role_permission')) {
            return;
        }

        $inboxId = $this->ensurePermission([
            'name' => 'View Inbox',
            'slug' => 'inbox.view',
            'group' => 'communication',
        ]);

        $productsId = $this->ensurePermission([
            'name' => 'Manage Products',
            'slug' => 'products.manage',
            'group' => 'sales',
        ]);

        // Seeder intent: admin/manager/senior/junior get inbox; admin/manager/senior get products.
        $inboxRoleIds = DB::table('roles')
            ->whereIn('slug', ['admin', 'manager', 'senior', 'junior'])
            ->pluck('id')
            ->all();

        $productsRoleIds = DB::table('roles')
            ->whereIn('slug', ['admin', 'manager', 'senior'])
            ->pluck('id')
            ->all();

        // Also attach to custom/owner roles that already have related CRM perms
        // (roles created before these slugs existed; settings.manage ≈ admin-like).
        $relatedForInbox = DB::table('permissions')
            ->whereIn('slug', ['settings.manage', 'tasks.view'])
            ->pluck('id')
            ->all();

        if ($relatedForInbox !== []) {
            $extra = DB::table('role_permission')
                ->whereIn('permission_id', $relatedForInbox)
                ->pluck('role_id')
                ->unique()
                ->all();
            $inboxRoleIds = array_values(array_unique(array_merge($inboxRoleIds, $extra)));
        }

        $relatedForProducts = DB::table('permissions')
            ->whereIn('slug', ['settings.manage', 'orders.view', 'orders.create'])
            ->pluck('id')
            ->all();

        if ($relatedForProducts !== []) {
            $extra = DB::table('role_permission')
                ->whereIn('permission_id', $relatedForProducts)
                ->pluck('role_id')
                ->unique()
                ->all();
            $productsRoleIds = array_values(array_unique(array_merge($productsRoleIds, $extra)));
        }

        $this->attachPermission($inboxRoleIds, $inboxId);
        $this->attachPermission($productsRoleIds, $productsId);
    }

    public function down(): void
    {
        // Keep permissions — pivot removal is ambiguous for live tenants.
    }

    protected function ensurePermission(array $perm): int
    {
        $existing = DB::table('permissions')->where('slug', $perm['slug'])->first();
        if ($existing) {
            return (int) $existing->id;
        }

        return (int) DB::table('permissions')->insertGetId(array_merge($perm, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    /**
     * @param  list<int|string>  $roleIds
     */
    protected function attachPermission(array $roleIds, int $permId): void
    {
        foreach ($roleIds as $roleId) {
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
};
