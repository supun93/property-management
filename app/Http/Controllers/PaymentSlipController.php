<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PaymentSlip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentSlipController extends Controller
{
    public function pending()
    {
        $slips = PaymentSlip::where('status', 0)->with('tenant', 'schedule')->get();
        return view('slips.pending', compact('slips'));
    }

    public function updateStatus(Request $request, $id)
    {
        $slip = PaymentSlip::findOrFail($id);
        $action = $request->action;

        if ($action == 'approve') {
            $slip->update([
                'status' => 1,
                'approved_by' => Auth::user()->id,
                'approved_at' => now(),
            ]);

            $slip->schedule->update([
                'status' => 1,
                'approval_status' => 1,
            ]);

            return back()->with('success', 'Slip approved.');
        }

        if ($action == 'reject') {
            $slip->update(['status' => 2]);
            $slip->schedule->update(['approval_status' => 2]);
            return back()->with('error', 'Slip rejected.');
        }

        return back()->with('error', 'Invalid action.');
    }

    public function upload(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'file' => 'required|mimes:jpeg,png,pdf|max:2048',
            'remarks' => 'nullable|string',
        ]);

        $filePath = $request->file('file')->store('slips', 'public');

        $slip = PaymentSlip::create([
            'invoice_id' => $request->invoice_id,
            'tenant_id' => Auth::user()->tenent->id,
            'file_path' => $filePath,
            'remarks' => $request->remarks,
            'status' => 0,
        ]);

        return redirect()->back()->with('success', 'Payment slip uploaded successfully.');
    }

     public function delete($id)
    {
        $record = PaymentSlip::findOrFail($id);
        $record->delete();

        return response()->json("success");
    }
}
