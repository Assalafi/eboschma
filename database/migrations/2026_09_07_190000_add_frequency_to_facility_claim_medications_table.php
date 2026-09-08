<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('facility_claim_medications', 'frequency')) {
            Schema::table('facility_claim_medications', function (Blueprint $table) {
                $table->string('frequency')->nullable()->after('dosage');
            });
        }

        // Backfill frequency, dosage, and days from prescription_items where available
        try {
            $items = DB::table('facility_claim_medications as fcm')
                ->join('prescription_items as pi', 'fcm.prescription_item_id', '=', 'pi.id')
                ->select('fcm.id', 'fcm.days', 'fcm.dosage as fcm_dosage', 'pi.frequency as pi_freq', 'pi.dosage as pi_dosage', 'pi.duration as pi_dur')
                ->get();

            foreach ($items as $item) {
                $update = [];

                // Normalize frequency
                if (!empty($item->pi_freq)) {
                    $raw = strtolower(trim($item->pi_freq));
                    if ($raw === '1' || $raw === 'od') {
                        $update['frequency'] = 'OD';
                    } elseif ($raw === '2' || $raw === 'bd') {
                        $update['frequency'] = 'BD';
                    } elseif ($raw === '3' || $raw === 'tds') {
                        $update['frequency'] = 'TDS';
                    } elseif ($raw === '4' || $raw === 'qds') {
                        $update['frequency'] = 'QDS';
                    } elseif ($raw === 'nocte') {
                        $update['frequency'] = 'Nocte';
                    } else {
                        $update['frequency'] = strtoupper($item->pi_freq);
                    }
                }

                // Dosage fallback
                if (empty($item->fcm_dosage) && !empty($item->pi_dosage)) {
                    $update['dosage'] = $item->pi_dosage;
                }

                // Days normalization from duration notation (e.g. 5/7 -> 5, 14 -> 14)
                if ((int)$item->days <= 1 && !empty($item->pi_dur)) {
                    $d = trim($item->pi_dur);
                    if (preg_match('/^(\d+)\s*\/\s*7$/', $d, $m)) {
                        $update['days'] = (int)$m[1];
                    } elseif (is_numeric($d) && (int)$d > 0) {
                        $update['days'] = (int)$d;
                    }
                }

                if (!empty($update)) {
                    DB::table('facility_claim_medications')->where('id', $item->id)->update($update);
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Backfill facility_claim_medications frequency warning: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('facility_claim_medications', 'frequency')) {
            Schema::table('facility_claim_medications', function (Blueprint $table) {
                $table->dropColumn('frequency');
            });
        }
    }
};
