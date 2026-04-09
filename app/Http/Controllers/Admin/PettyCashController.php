<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PettyCashTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PettyCashController extends Controller
{
    public function index(Request $request)
    {
        $query = PettyCashTransaction::with('recorder')->latest('transaction_at')->latest('id');

        if ($request->filled('from')) {
            $query->where('transaction_at', '>=', Carbon::parse($request->from));
        }
        if ($request->filled('to')) {
            $query->where('transaction_at', '<=', Carbon::parse($request->to));
        }

        $transactions = $query->paginate(25)->withQueryString();

        $totalCredit = (float) PettyCashTransaction::where('type', 'credit')->sum('amount');
        $totalDebit = (float) PettyCashTransaction::where('type', 'debit')->sum('amount');
        $balance = $totalCredit - $totalDebit;

        return view('admin.petty-cash.index', compact('transactions', 'totalCredit', 'totalDebit', 'balance'));
    }

    public function create()
    {
        return view('admin.petty-cash.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_at' => 'required|date',
            'type' => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'description' => 'nullable|string|max:2000',
            'reference' => 'nullable|string|max:191',
        ]);

        $validated['recorded_by'] = auth()->id();
        PettyCashTransaction::create($validated);

        return redirect()->route('admin.petty-cash.index')->with('success', 'Petty cash entry saved.');
    }

    public function edit(int $id)
    {
        $transaction = PettyCashTransaction::findOrFail($id);

        return view('admin.petty-cash.edit', compact('transaction'));
    }

    public function update(Request $request, int $id)
    {
        $transaction = PettyCashTransaction::findOrFail($id);

        $validated = $request->validate([
            'transaction_at' => 'required|date',
            'type' => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'description' => 'nullable|string|max:2000',
            'reference' => 'nullable|string|max:191',
        ]);

        $transaction->update($validated);

        return redirect()->route('admin.petty-cash.index')->with('success', 'Entry updated.');
    }

    public function destroy(int $id)
    {
        $transaction = PettyCashTransaction::findOrFail($id);
        $transaction->delete();

        return redirect()->route('admin.petty-cash.index')->with('success', 'Entry deleted.');
    }

    public function report(Request $request)
    {
        return view('admin.petty-cash.report', $this->buildReportPayload($request));
    }

    public function exportReportCsv(Request $request)
    {
        $data = $this->buildReportPayload($request);

        $filename = 'petty-cash-report_'.$data['from']->format('Y-m-d_His').'_to_'.$data['to']->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($out, ['Petty cash report']);
            fputcsv($out, ['From', $data['from']->format('Y-m-d H:i:s')]);
            fputcsv($out, ['To', $data['to']->format('Y-m-d H:i:s')]);
            fputcsv($out, []);
            fputcsv($out, ['Opening balance', number_format($data['openingBalance'], 2, '.', '')]);
            fputcsv($out, ['Received in period', number_format($data['periodCredit'], 2, '.', '')]);
            fputcsv($out, ['Paid out in period', number_format($data['periodDebit'], 2, '.', '')]);
            fputcsv($out, ['Closing balance', number_format($data['closingBalance'], 2, '.', '')]);
            fputcsv($out, []);
            fputcsv($out, ['Transaction at', 'Type', 'Amount (INR)', 'Signed amount (INR)', 'Running balance (INR)', 'Description', 'Reference', 'Recorded by']);

            foreach ($data['rows'] as $row) {
                $t = $row->transaction;
                fputcsv($out, [
                    $t->transaction_at->format('Y-m-d H:i:s'),
                    $t->type === 'credit' ? 'Received' : 'Paid out',
                    number_format((float) $t->amount, 2, '.', ''),
                    number_format($t->signedAmount(), 2, '.', ''),
                    number_format($row->running_balance, 2, '.', ''),
                    $t->description ?? '',
                    $t->reference ?? '',
                    $t->recorder?->name ?? '',
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array{from: Carbon, to: Carbon, fromInput: string, toInput: string, openingBalance: float, periodCredit: float, periodDebit: float, closingBalance: float, rows: Collection, count: int}
     */
    private function buildReportPayload(Request $request): array
    {
        $defaultFrom = now()->startOfMonth()->format('Y-m-d\TH:i');
        $defaultTo = now()->format('Y-m-d\TH:i');

        $fromInput = $request->input('from', $defaultFrom);
        $toInput = $request->input('to', $defaultTo);

        $from = Carbon::parse($fromInput)->startOfSecond();
        $to = Carbon::parse($toInput)->endOfSecond();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfSecond(), $from->copy()->endOfSecond()];
        }

        $openingCredit = (float) PettyCashTransaction::where('transaction_at', '<', $from)
            ->where('type', 'credit')
            ->sum('amount');
        $openingDebit = (float) PettyCashTransaction::where('transaction_at', '<', $from)
            ->where('type', 'debit')
            ->sum('amount');
        $openingBalance = $openingCredit - $openingDebit;

        $transactions = PettyCashTransaction::with('recorder')
            ->where('transaction_at', '>=', $from)
            ->where('transaction_at', '<=', $to)
            ->orderBy('transaction_at')
            ->orderBy('id')
            ->get();

        $periodCredit = (float) $transactions->where('type', 'credit')->sum('amount');
        $periodDebit = (float) $transactions->where('type', 'debit')->sum('amount');
        $closingBalance = $openingBalance + $periodCredit - $periodDebit;

        $running = $openingBalance;
        $rows = $transactions->map(function (PettyCashTransaction $t) use (&$running) {
            $running += $t->signedAmount();

            return (object) [
                'transaction' => $t,
                'running_balance' => $running,
            ];
        });

        return [
            'from' => $from,
            'to' => $to,
            'fromInput' => $fromInput,
            'toInput' => $toInput,
            'openingBalance' => $openingBalance,
            'periodCredit' => $periodCredit,
            'periodDebit' => $periodDebit,
            'closingBalance' => $closingBalance,
            'rows' => $rows,
            'count' => $transactions->count(),
        ];
    }
}
