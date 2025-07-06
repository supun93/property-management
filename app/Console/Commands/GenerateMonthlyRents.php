<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UnitContracts;
use App\Models\UnitPaymentSchedules;
use Carbon\Carbon;

class GenerateMonthlyRents extends Command
{
    protected $signature = 'rents:generate-monthly';
    protected $description = 'Auto-generate monthly rent schedules for all active contracts';

    public function handle()
    {
        $generated = 0;
        $now = Carbon::now()->startOfMonth();

        $contracts = UnitContracts::with('unit.billingTypes.billingType')
            ->where('status', 1)
            ->get();

        foreach ($contracts as $contract) {
            if (!$contract->unit || !$contract->unit->billingTypes) {
                continue;
            }

            foreach ($contract->unit->billingTypes as $bType) {
                $exists = UnitPaymentSchedules::where('unit_contract_id', $contract->id)
                    ->where('unit_billing_type_id', $bType->id)
                    ->whereMonth('payment_date', $now->month)
                    ->whereYear('payment_date', $now->year)
                    ->exists();

                if (!$exists) {
                    UnitPaymentSchedules::create([
                        'unit_contract_id'      => $contract->id,
                        'unit_billing_type_id'  => $bType->id,
                        'payment_date'          => $now->copy(),
                        'amount'                => $bType->amount ?? 0,
                        'status'                => 0,
                        'note'                  => $bType->billingType->name ?? 'Rent',
                        'created_by'            => 1,
                    ]);

                    $generated++;
                }
            }
        }

        $this->info("✅ Rent schedules generated: $generated");
    }
}
