<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UnitContracts;
use App\Models\UnitPaymentSchedules;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class GenerateMonthlyRents extends Command
{
    protected $signature = 'rents:generate-monthly';
    protected $description = 'Auto-generate monthly rent schedules for all active contracts';

    public function handle()
    {
        $today = Carbon::today();
        $generated = 0;
        $user = User::find(1);
        if($user){
            $user->name = time();
            $user->save();
        }
        $contracts = UnitContracts::where('status', 1)
            ->whereDate('agreement_start_date', '<=', $today)
            ->whereDate('agreement_end_date', '>=', $today)
            ->get();

        foreach ($contracts as $contract) {
            $alreadyExists = UnitPaymentSchedules::where('unit_contract_id', $contract->id)
                ->where('is_rent', 1)
                ->whereMonth('payment_date', $today->month)
                ->whereYear('payment_date', $today->year)
                ->exists();

            if ($alreadyExists) continue;

            UnitPaymentSchedules::create([
                'unit_contract_id' => $contract->id,
                'is_rent' => 1,
                'payment_date' => $today->format('Y-m-d'),
                'amount' => $contract->rent_amount,
                'note' => 'Auto-generated rent for ' . $today->format('F Y'),
                'status' => 0,
                'created_by' => 1 // Or Auth::id() if running interactively
            ]);

            $generated++;
        }

        $this->info("✅ Rent schedules generated: $generated");
    }
}
