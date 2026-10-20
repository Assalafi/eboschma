<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserActivity
{
    /**
     * Operational tables/columns that reference facility user (web guard) records.
     *
     * System tables (notifications, sessions, presence) and pivot tables are
     * intentionally excluded — they are not business activity.
     */
    protected static array $userReferences = [
        ['encounter_actions', 'user_id'],
        ['vital_signs', 'taken_by'],
        ['service_results', 'reported_by'],
        ['service_results', 'radiologist_id'],
        ['service_results', 'radiographer_id'],
        ['prescriptions', 'prescribed_by'],
        ['investigations', 'ordered_by'],
        ['investigations', 'requested_by'],
        ['facility_claims', 'submitted_by'],
        ['facility_claim_documents', 'uploaded_by'],
        ['service_orders', 'ordered_by'],
        ['authorizations', 'approved_by'],
        ['drug_stock_requests', 'requested_by'],
        ['drug_administrations', 'administered_by'],
        ['admissions', 'admitted_by'],
        ['nurse_ward', 'user_id'],
        ['doctor_ward', 'user_id'],
        ['activity_logs', 'user_id'],
    ];

    /**
     * Operational tables/columns that reference staff (staff guard) records.
     */
    protected static array $staffReferences = [
        ['beneficiaries', 'created_by'],
        ['beneficiaries', 'updated_by'],
        ['beneficiaries', 'submitted_by'],
        ['children', 'created_by'],
        ['children', 'updated_by'],
        ['children', 'submitted_by'],
        ['spouses', 'created_by'],
        ['spouses', 'updated_by'],
        ['spouses', 'submitted_by'],
        ['claims', 'created_by'],
        ['claims', 'updated_by'],
        ['claims', 'approved_by'],
        ['claims', 'rejected_by'],
        ['claims', 'paid_by'],
        ['claim_histories', 'user_id'],
        ['claim_notes', 'user_id'],
        ['admissions', 'admitted_by'],
        ['authorizations', 'approved_by'],
        ['contribution_uploads', 'uploaded_by'],
        ['doctor_ward', 'user_id'],
        ['nurse_ward', 'user_id'],
        ['drug_administrations', 'administered_by'],
        ['drug_stocks', 'approved_by'],
        ['drug_stocks', 'dispensed_by'],
        ['drug_stocks', 'stocked_by'],
        ['drug_stock_requests', 'approved_by'],
        ['drug_stock_requests', 'dispensed_by'],
        ['drug_stock_requests', 'requested_by'],
        ['drug_store_stocks', 'stocked_by'],
        ['encounter_actions', 'user_id'],
        ['facility_claims', 'approved_by'],
        ['facility_claims', 'e5_updated_by'],
        ['facility_claims', 'paid_by'],
        ['facility_claims', 'reviewed_by'],
        ['facility_claims', 'ro_updated_by'],
        ['facility_claims', 'submitted_by'],
        ['facility_claim_documents', 'uploaded_by'],
        ['facility_wallets', 'created_by'],
        ['investigations', 'ordered_by'],
        ['investigations', 'requested_by'],
        ['prescriptions', 'prescribed_by'],
        ['service_orders', 'ordered_by'],
        ['service_referrals', 'approved_by'],
        ['service_referrals', 'rejected_by'],
        ['service_results', 'reported_by'],
        ['tickets', 'assigned_to'],
        ['tickets', 'created_by'],
        ['ticket_replies', 'user_id'],
        ['vital_signs', 'taken_by'],
        ['wallet_transactions', 'performed_by'],
        ['activity_logs', 'user_id'],
    ];

    public static function userHasActivity(string $userId): bool
    {
        return self::hasAny(self::$userReferences, $userId);
    }

    public static function staffHasActivity(string $staffId): bool
    {
        return self::hasAny(self::$staffReferences, $staffId);
    }

    protected static function hasAny(array $references, string $id): bool
    {
        if ($id === '') {
            return false;
        }

        foreach ($references as [$table, $column]) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
                continue;
            }

            if (DB::table($table)->where($column, $id)->exists()) {
                return true;
            }
        }

        return false;
    }
}
