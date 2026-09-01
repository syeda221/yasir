<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Ensure `checkbook` is in modules table
        if (Schema::hasTable('modules')) {
            DB::table('modules')->updateOrInsert(
                ['name' => 'checkbook'],
                ['name' => 'checkbook', 'updated_at' => now(), 'created_at' => now()]
            );
        }

        // 2. Ensure all modules permissions exist
        $modules = [
            'home',
            'profile',
            'products',
            'product.bookings',
            'discount.products',
            'categories',
            'subcategories',
            'brands',
            'units',
            'warehouse',
            'warehouse.stock',
            'stock.transfer',
            'stock.adjust',
            'stocks',
            'purchases',
            'purchase.returns',
            'vendors',
            'vendor.bilties',
            'inward.gatepass',
            'sales',
            'sales.returns',
            'customers',
            'customer_types',
            'customer.ledger',
            'bookings',
            'checkbook',

            'chart.of.accounts',
            'expense.voucher',
            'receipts.voucher',
            'journal.voucher',
            'payment.voucher',
            'income.voucher',
            'item.stock.report',
            'purchase.report',
            'sale.report',
            'reporting',
            'recovery.report',
            'payable.report',
            'parties.balance.report',
            'aging.report',
            'balance.sheet.report',
            'profit.loss.report',
            'inventory.onhand',
            'vendor.ledger',
            'users',
            'roles',
            'permissions',
            'branches',
            'zones',
            'sales.officers',
            'narrations',
            'executive.report',
            'package.types',
            // HR Modules
            'hr.departments',
            'hr.employees',
            'hr.attendance',
            'hr.payroll',
            'hr.leaves',
            'hr.designations',
            'hr.shifts',
            'hr.holidays',
            'hr.salary.structure',
            'hr.loans',
            'hr.biometric.devices',
            'web_products',
            'coupons',
            'web_orders',
            'settings',
            'web_users'
        ];

        $actions = ['view', 'create', 'edit', 'delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $permissionName = strtolower("{$module}.{$action}");
                Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
            }
        }

        // Custom website-settings permissions
        foreach ([
            'website-settings.view',
            'website-settings.create',
            'website-settings.edit',
            'website-settings.delete',
            'website-settings.update',
            'website-settings.upload_manage'
        ] as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // Purchase POS permission
        Permission::firstOrCreate(['name' => 'purchase_pos.create', 'guard_name' => 'web']);

        // Sync with Super Admin roles
        foreach (['Super Admin', 'superAdmin', 'Admin', 'admin'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo(Permission::all());
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep permissions intact
    }
};
