<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalTransaction;
use App\Models\JournalEntry;
use App\Models\SupplierBill;
use App\Models\SupplierPayment;
use App\Models\FranchiseInvoice;
use App\Models\FranchiseReceipt;
use App\Models\ExpenseVoucher;
use App\Models\SalesLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AccountingController extends Controller
{
    /**
     * Friendly name map for expense account codes.
     */
    private array $expenseNameMap = [
        '5010' => 'Raw Materials',
        '5020' => 'Production Costs',
        '5100' => 'Rent',
        '5200' => 'Utilities',
        '5300' => 'Salaries & Wages',
        '5400' => 'Transport & Delivery',
        '5500' => 'Maintenance & Repairs',
        '5600' => 'Office Supplies',
        '5900' => 'General & Admin',
    ];

    /**
     * Build a plain-English description for a journal transaction based on its reference prefix.
     */
    private function describeTransaction(JournalTransaction $tx): string
    {
        $ref = $tx->reference ?? '';
        $amount = $tx->journalEntries->max(function ($e) {
            return max((float) $e->debit, (float) $e->credit);
        });
        $formattedAmount = number_format($amount, 2);

        if (str_starts_with($ref, 'PO-')) {
            return 'Supplier bill recorded';
        }
        if (str_starts_with($ref, 'SPAY-')) {
            return "Paid supplier ₹{$formattedAmount}";
        }
        if (str_starts_with($ref, 'FR-')) {
            return "Received from franchise ₹{$formattedAmount}";
        }
        if (str_starts_with($ref, 'EXP-') || str_starts_with($ref, 'PV-')) {
            return 'Expense: ' . ($tx->description ?? '');
        }
        if (str_starts_with($ref, 'RV-')) {
            return "Income received ₹{$formattedAmount}";
        }
        if (str_starts_with($ref, 'SL-')) {
            return 'Own outlet sales recorded';
        }

        return $tx->description ?? $ref;
    }

    // ═══════════════════════════════════════════
    //  DASHBOARD
    // ═══════════════════════════════════════════

    /**
     * Dashboard – Money Overview
     */
    public function dashboard()
    {
        // Live Balances
        $cashAccount = Account::where('code', '1010')->first();
        $bankAccount = Account::where('code', '1020')->first();

        $cashBalance = $cashAccount ? $cashAccount->balance : 0.00;
        $bankBalance = $bankAccount ? $bankAccount->balance : 0.00;

        // Outstanding AP & AR
        $outstandingAP = SupplierBill::whereIn('status', ['unpaid', 'partially_paid'])
            ->get()->sum(fn($bill) => $bill->remaining_amount);

        $outstandingAR = FranchiseInvoice::whereIn('status', ['unpaid', 'partially_paid'])
            ->get()->sum(fn($inv) => $inv->remaining_amount);

        $unpaidBillsCount = SupplierBill::whereIn('status', ['unpaid', 'partially_paid'])->count();
        $unpaidInvoicesCount = FranchiseInvoice::whereIn('status', ['unpaid', 'partially_paid'])->count();

        // MTD calculations
        $firstDayOfMonth = now()->startOfMonth();
        $lastDayOfMonth = now()->endOfMonth();

        $revenueAccountIds = Account::whereIn('code', ['4010', '4020'])->pluck('id');
        $mtdRevenue = JournalEntry::whereIn('account_id', $revenueAccountIds)
            ->whereHas('journalTransaction', fn($q) => $q->whereBetween('date', [$firstDayOfMonth, $lastDayOfMonth]))
            ->sum('credit');

        $expenseAccountIds = Account::where('type', 'expense')->pluck('id');
        $mtdExpenses = JournalEntry::whereIn('account_id', $expenseAccountIds)
            ->whereHas('journalTransaction', fn($q) => $q->whereBetween('date', [$firstDayOfMonth, $lastDayOfMonth]))
            ->sum('debit');

        $mtdProfit = $mtdRevenue - $mtdExpenses;

        // Recent Activity – last 15 transactions as plain-English feed
        $cashAccountId = $cashAccount?->id;
        $bankAccountId = $bankAccount?->id;

        $recentTransactions = JournalTransaction::with(['journalEntries.account'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        $recentActivity = $recentTransactions->map(function ($tx) use ($cashAccountId, $bankAccountId) {
            $description = $this->describeTransaction($tx);

            $cashAmount = 0;
            $bankAmount = 0;

            foreach ($tx->journalEntries as $entry) {
                if ($entry->account_id == $cashAccountId) {
                    $cashAmount += (float) $entry->debit - (float) $entry->credit;
                }
                if ($entry->account_id == $bankAccountId) {
                    $bankAmount += (float) $entry->debit - (float) $entry->credit;
                }
            }

            return [
                'date' => $tx->date,
                'description' => $description,
                'cash_amount' => $cashAmount,
                'bank_amount' => $bankAmount,
                'reference' => $tx->reference,
            ];
        });

        return view('accounting.dashboard', compact(
            'cashBalance',
            'bankBalance',
            'outstandingAP',
            'outstandingAR',
            'unpaidBillsCount',
            'unpaidInvoicesCount',
            'mtdRevenue',
            'mtdExpenses',
            'mtdProfit',
            'recentActivity'
        ));
    }

    // ═══════════════════════════════════════════
    //  PAYMENT VOUCHERS (Money Going Out)
    // ═══════════════════════════════════════════

    /**
     * List all payment vouchers (expenses + supplier payments) merged.
     */
    public function paymentVouchersIndex(Request $request)
    {
        // Expense vouchers
        $expenses = ExpenseVoucher::with(['expenseAccount', 'paymentAccount', 'user'])->get();
        $expenseMapped = $expenses->toBase()->map(function ($ev) {
            $categoryName = $ev->expenseAccount->name ?? '';
            $categoryName = preg_replace('/\s*Expense$/i', '', $categoryName);
            $categoryName = $this->expenseNameMap[$ev->expenseAccount->code ?? ''] ?? $categoryName;

            return (object) [
                'voucher_number' => $ev->voucher_number,
                'date' => $ev->date,
                'payee' => $ev->payee,
                'type' => 'expense',
                'category' => $categoryName,
                'amount' => (float) $ev->amount,
                'paid_via' => ($ev->paymentAccount->code ?? '') == '1010' ? 'Cash' : 'Bank',
                'source_type' => 'expense',
                'source_id' => $ev->id,
            ];
        });

        // Supplier payments
        $supplierPayments = SupplierPayment::with(['supplierBill.supplier'])->get();
        $supplierMapped = $supplierPayments->toBase()->map(function ($sp) {
            // Determine paid_via from journal entries
            $paidVia = 'Bank';
            $journalTx = JournalTransaction::where('reference', $sp->payment_number)->first();
            if ($journalTx) {
                $cashAccountId = Account::where('code', '1010')->value('id');
                $hasCashCredit = JournalEntry::where('journal_transaction_id', $journalTx->id)
                    ->where('account_id', $cashAccountId)
                    ->where('credit', '>', 0)
                    ->exists();
                if ($hasCashCredit) {
                    $paidVia = 'Cash';
                }
            }

            return (object) [
                'voucher_number' => $sp->payment_number,
                'date' => $sp->payment_date,
                'payee' => $sp->supplierBill->supplier->name ?? 'Unknown Supplier',
                'type' => 'supplier',
                'category' => 'Supplier Payment',
                'amount' => (float) $sp->amount,
                'paid_via' => $paidVia,
                'source_type' => 'supplier_payment',
                'source_id' => $sp->id,
            ];
        });

        // Merge and sort
        $vouchers = $expenseMapped->merge($supplierMapped)
            ->sortByDesc('date')
            ->values();

        // Optional filter
        if ($request->has('type') && $request->type !== 'all') {
            $vouchers = $vouchers->where('type', $request->type)->values();
        }

        return view('accounting.payment_vouchers.index', compact('vouchers'));
    }

    /**
     * Payment voucher creation form.
     */
    public function paymentVoucherCreate()
    {
        // Expense categories with friendly names
        $categories = Account::where('type', 'expense')->orderBy('code', 'asc')->get()->map(function ($acc) {
            return [
                'id' => $acc->id,
                'code' => $acc->code,
                'label' => $this->expenseNameMap[$acc->code] ?? $acc->name,
            ];
        });

        // Unpaid supplier bills
        $unpaidBills = SupplierBill::with('supplier')
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->get();

        // Auto-generate voucher number (check both ExpenseVoucher and SupplierPayment)
        $year = now()->year;
        $latestExpenseVoucher = ExpenseVoucher::where('voucher_number', 'like', "PV-{$year}-%")
            ->orderBy('id', 'desc')->first();
        $latestSupplierPayment = SupplierPayment::where('payment_number', 'like', "PV-{$year}-%")
            ->orderBy('id', 'desc')->first();

        $maxSerial = 0;
        if ($latestExpenseVoucher) {
            $parts = explode('-', $latestExpenseVoucher->voucher_number);
            $maxSerial = max($maxSerial, (int) end($parts));
        }
        if ($latestSupplierPayment) {
            $parts = explode('-', $latestSupplierPayment->payment_number);
            $maxSerial = max($maxSerial, (int) end($parts));
        }
        $voucherNumber = 'PV-' . $year . '-' . str_pad($maxSerial + 1, 4, '0', STR_PAD_LEFT);

        return view('accounting.payment_vouchers.create', compact('categories', 'unpaidBills', 'voucherNumber'));
    }

    /**
     * Store payment voucher (expense or supplier payment).
     */
    public function paymentVoucherStore(Request $request)
    {
        $paymentType = $request->payment_type;

        if ($paymentType === 'expense') {
            return $this->storeExpenseVoucher($request);
        }

        if ($paymentType === 'supplier') {
            return $this->storeSupplierPayment($request);
        }

        return back()->with('error', 'Invalid payment type.');
    }

    /**
     * Store an expense payment voucher.
     */
    private function storeExpenseVoucher(Request $request)
    {
        $request->validate([
            'voucher_number' => 'required|string',
            'payee' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_account_id' => 'required|exists:accounts,id',
            'paid_via' => 'required|in:cash,bank',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $paymentAccount = $request->paid_via === 'cash'
                ? Account::where('code', '1010')->first()
                : Account::where('code', '1020')->first();

            $voucher = ExpenseVoucher::create([
                'voucher_number' => $request->voucher_number,
                'payee' => $request->payee,
                'amount' => $request->amount,
                'expense_account_id' => $request->expense_account_id,
                'payment_account_id' => $paymentAccount->id,
                'date' => $request->date,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            // Post journal: Debit expense, Credit cash/bank
            $tx = JournalTransaction::create([
                'reference' => $voucher->voucher_number,
                'description' => "Expense Voucher: {$request->notes} (Payee: {$request->payee})",
                'date' => $request->date,
                'created_by' => auth()->id(),
            ]);
            JournalEntry::create([
                'journal_transaction_id' => $tx->id,
                'account_id' => $request->expense_account_id,
                'debit' => $request->amount,
                'credit' => 0.00,
            ]);
            JournalEntry::create([
                'journal_transaction_id' => $tx->id,
                'account_id' => $paymentAccount->id,
                'debit' => 0.00,
                'credit' => $request->amount,
            ]);

            DB::commit();

            return redirect()->route('accounting.payment-vouchers.show', ['type' => 'expense', 'id' => $voucher->id])
                ->with('success', "Payment Voucher {$voucher->voucher_number} recorded successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error recording expense: ' . $e->getMessage());
        }
    }

    /**
     * Store a supplier payment voucher.
     */
    private function storeSupplierPayment(Request $request)
    {
        $bill = SupplierBill::findOrFail($request->supplier_bill_id);

        $request->validate([
            'voucher_number' => 'required|string',
            'supplier_bill_id' => 'required|exists:supplier_bills,id',
            'amount' => 'required|numeric|min:0.01|max:' . $bill->remaining_amount,
            'paid_via' => 'required|in:cash,bank',
            'date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $paymentAccount = $request->paid_via === 'cash'
                ? Account::where('code', '1010')->first()
                : Account::where('code', '1020')->first();

            $payment = SupplierPayment::create([
                'payment_number' => $request->voucher_number,
                'supplier_bill_id' => $bill->id,
                'amount' => $request->amount,
                'payment_date' => $request->date,
                'payment_method' => $request->paid_via,
                'reference' => $request->reference,
                'notes' => $request->notes,
            ]);

            // Update bill status
            $newPaid = $bill->paid_amount;
            if ($newPaid >= $bill->amount) {
                $bill->update(['status' => 'paid']);
            } else {
                $bill->update(['status' => 'partially_paid']);
            }

            // Post journal: Debit AP (2100), Credit cash/bank
            $apAccount = Account::where('code', '2100')->first();
            $tx = JournalTransaction::create([
                'reference' => $payment->payment_number,
                'description' => "Payment of {$request->amount} to supplier {$bill->supplier->name} for Bill {$bill->bill_number}",
                'date' => $request->date,
                'created_by' => auth()->id(),
            ]);
            JournalEntry::create([
                'journal_transaction_id' => $tx->id,
                'account_id' => $apAccount->id,
                'debit' => $request->amount,
                'credit' => 0.00,
            ]);
            JournalEntry::create([
                'journal_transaction_id' => $tx->id,
                'account_id' => $paymentAccount->id,
                'debit' => 0.00,
                'credit' => $request->amount,
            ]);

            DB::commit();

            return redirect()->route('accounting.payment-vouchers.show', ['type' => 'supplier', 'id' => $payment->id])
                ->with('success', "Supplier payment {$payment->payment_number} recorded successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error recording supplier payment: ' . $e->getMessage());
        }
    }

    /**
     * Show a single payment voucher.
     */
    public function paymentVoucherShow(Request $request)
    {
        $type = $request->query('type');
        $id = $request->query('id');

        if ($type === 'expense') {
            $source = ExpenseVoucher::with(['expenseAccount', 'paymentAccount', 'user'])->findOrFail($id);
            $categoryName = $this->expenseNameMap[$source->expenseAccount->code ?? ''] ?? $source->expenseAccount->name;

            $voucher = (object) [
                'voucher_number' => $source->voucher_number,
                'date' => $source->date,
                'payee' => $source->payee,
                'category' => $categoryName,
                'amount' => (float) $source->amount,
                'paid_via' => ($source->paymentAccount->code ?? '') == '1010' ? 'Cash' : 'Bank',
                'notes' => $source->notes,
                'created_at' => $source->created_at,
                'type' => 'expense',
            ];
        } elseif ($type === 'supplier') {
            $source = SupplierPayment::with(['supplierBill.supplier'])->findOrFail($id);

            $voucher = (object) [
                'voucher_number' => $source->payment_number,
                'date' => $source->payment_date,
                'payee' => $source->supplierBill->supplier->name ?? 'Unknown',
                'category' => 'Supplier Payment',
                'amount' => (float) $source->amount,
                'paid_via' => $source->payment_method === 'cash' ? 'Cash' : 'Bank',
                'notes' => $source->notes,
                'created_at' => $source->created_at,
                'type' => 'supplier',
                'bill_number' => $source->supplierBill->bill_number ?? '',
                'reference' => $source->reference,
            ];
        } else {
            abort(404);
        }

        return view('accounting.payment_vouchers.show', compact('voucher'));
    }

    // ═══════════════════════════════════════════
    //  RECEIPT VOUCHERS (Money Coming In)
    // ═══════════════════════════════════════════

    /**
     * List all receipt vouchers (franchise receipts + own income) merged.
     */
    public function receiptVouchersIndex(Request $request)
    {
        // Franchise receipts
        $franchiseReceipts = FranchiseReceipt::with(['franchiseInvoice.outlet'])->get();
        $franchiseMapped = $franchiseReceipts->toBase()->map(function ($fr) {
            $paidVia = $fr->payment_method === 'cash' ? 'Cash' : 'Bank';

            return (object) [
                'voucher_number' => $fr->receipt_number,
                'date' => $fr->receipt_date,
                'received_from' => $fr->franchiseInvoice->outlet->name ?? 'Unknown Franchise',
                'type' => 'franchise',
                'amount' => (float) $fr->amount,
                'received_via' => $paidVia,
                'source_type' => 'franchise',
                'source_id' => $fr->id,
            ];
        });

        // Own income receipts (JournalTransactions with RV- reference)
        $ownIncomeTransactions = JournalTransaction::with(['journalEntries.account'])
            ->where('reference', 'like', 'RV-%')
            ->get();

        $ownIncomeMapped = $ownIncomeTransactions->toBase()->map(function ($tx) {
            $amount = $tx->journalEntries->max(fn($e) => max((float) $e->debit, (float) $e->credit));
            $cashAccountId = Account::where('code', '1010')->value('id');
            $hasCashDebit = $tx->journalEntries->contains(fn($e) => $e->account_id == $cashAccountId && (float) $e->debit > 0);

            return (object) [
                'voucher_number' => $tx->reference,
                'date' => $tx->date,
                'received_from' => str_replace('Own income: ', '', $tx->description ?? ''),
                'type' => 'own_income',
                'amount' => $amount,
                'received_via' => $hasCashDebit ? 'Cash' : 'Bank',
                'source_type' => 'own_income',
                'source_id' => $tx->id,
            ];
        });

        // Merge and sort
        $vouchers = $franchiseMapped->merge($ownIncomeMapped)
            ->sortByDesc('date')
            ->values();

        return view('accounting.receipt_vouchers.index', compact('vouchers'));
    }

    /**
     * Receipt voucher creation form.
     */
    public function receiptVoucherCreate()
    {
        // Unpaid franchise invoices
        $unpaidInvoices = FranchiseInvoice::with('outlet')
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->get();

        // Auto-generate voucher number (check FranchiseReceipt and JournalTransaction)
        $year = now()->year;
        $latestReceipt = FranchiseReceipt::where('receipt_number', 'like', "RV-{$year}-%")
            ->orderBy('id', 'desc')->first();
        $latestOwnIncome = JournalTransaction::where('reference', 'like', "RV-{$year}-%")
            ->orderBy('id', 'desc')->first();

        $maxSerial = 0;
        if ($latestReceipt) {
            $parts = explode('-', $latestReceipt->receipt_number);
            $maxSerial = max($maxSerial, (int) end($parts));
        }
        if ($latestOwnIncome) {
            $parts = explode('-', $latestOwnIncome->reference);
            $maxSerial = max($maxSerial, (int) end($parts));
        }
        $voucherNumber = 'RV-' . $year . '-' . str_pad($maxSerial + 1, 4, '0', STR_PAD_LEFT);

        return view('accounting.receipt_vouchers.create', compact('unpaidInvoices', 'voucherNumber'));
    }

    /**
     * Store receipt voucher (own income or franchise).
     */
    public function receiptVoucherStore(Request $request)
    {
        $receiptType = $request->receipt_type;

        if ($receiptType === 'own_income') {
            return $this->storeOwnIncomeReceipt($request);
        }

        if ($receiptType === 'franchise') {
            return $this->storeFranchiseReceipt($request);
        }

        return back()->with('error', 'Invalid receipt type.');
    }

    /**
     * Store own-income receipt voucher.
     */
    private function storeOwnIncomeReceipt(Request $request)
    {
        $request->validate([
            'voucher_number' => 'required|string',
            'received_from' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'received_via' => 'required|in:cash,bank',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $paymentAccount = $request->received_via === 'cash'
                ? Account::where('code', '1010')->first()
                : Account::where('code', '1020')->first();

            $revenueAccount = Account::where('code', '4010')->first();

            $tx = JournalTransaction::create([
                'reference' => $request->voucher_number,
                'description' => "Own income: {$request->received_from}",
                'date' => $request->date,
                'created_by' => auth()->id(),
            ]);

            // Debit cash/bank
            JournalEntry::create([
                'journal_transaction_id' => $tx->id,
                'account_id' => $paymentAccount->id,
                'debit' => $request->amount,
                'credit' => 0.00,
            ]);

            // Credit Own Sales Revenue (4010)
            JournalEntry::create([
                'journal_transaction_id' => $tx->id,
                'account_id' => $revenueAccount->id,
                'debit' => 0.00,
                'credit' => $request->amount,
            ]);

            DB::commit();

            return redirect()->route('accounting.receipt-vouchers.index')
                ->with('success', "Receipt Voucher {$request->voucher_number} recorded successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error recording receipt: ' . $e->getMessage());
        }
    }

    /**
     * Store franchise receipt voucher.
     */
    private function storeFranchiseReceipt(Request $request)
    {
        $invoice = FranchiseInvoice::findOrFail($request->franchise_invoice_id);

        $request->validate([
            'voucher_number' => 'required|string',
            'franchise_invoice_id' => 'required|exists:franchise_invoices,id',
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->remaining_amount,
            'received_via' => 'required|in:cash,bank',
            'date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $paymentAccount = $request->received_via === 'cash'
                ? Account::where('code', '1010')->first()
                : Account::where('code', '1020')->first();

            FranchiseReceipt::create([
                'receipt_number' => $request->voucher_number,
                'franchise_invoice_id' => $invoice->id,
                'amount' => $request->amount,
                'receipt_date' => $request->date,
                'payment_method' => $request->received_via,
                'reference' => $request->reference,
                'notes' => $request->notes,
            ]);

            // Update invoice status
            $newPaid = $invoice->paid_amount;
            if ($newPaid >= $invoice->amount) {
                $invoice->update(['status' => 'paid']);
            } else {
                $invoice->update(['status' => 'partially_paid']);
            }

            // Post journal: Debit cash/bank, Credit AR (1200)
            $arAccount = Account::where('code', '1200')->first();
            $tx = JournalTransaction::create([
                'reference' => $request->voucher_number,
                'description' => "Franchise receipt from {$invoice->outlet->name} for Invoice {$invoice->invoice_number}",
                'date' => $request->date,
                'created_by' => auth()->id(),
            ]);
            JournalEntry::create([
                'journal_transaction_id' => $tx->id,
                'account_id' => $paymentAccount->id,
                'debit' => $request->amount,
                'credit' => 0.00,
            ]);
            JournalEntry::create([
                'journal_transaction_id' => $tx->id,
                'account_id' => $arAccount->id,
                'debit' => 0.00,
                'credit' => $request->amount,
            ]);

            DB::commit();

            return redirect()->route('accounting.receipt-vouchers.index')
                ->with('success', "Receipt Voucher {$request->voucher_number} recorded successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error recording franchise receipt: ' . $e->getMessage());
        }
    }

    /**
     * Show a single receipt voucher.
     */
    public function receiptVoucherShow(Request $request)
    {
        $type = $request->query('type');
        $id = $request->query('id');

        if ($type === 'franchise') {
            $source = FranchiseReceipt::with(['franchiseInvoice.outlet'])->findOrFail($id);

            $voucher = (object) [
                'voucher_number' => $source->receipt_number,
                'date' => $source->receipt_date,
                'received_from' => $source->franchiseInvoice->outlet->name ?? 'Unknown',
                'category' => 'Franchise Collection',
                'amount' => (float) $source->amount,
                'received_via' => $source->payment_method === 'cash' ? 'Cash' : 'Bank',
                'notes' => $source->notes,
                'created_at' => $source->created_at,
                'type' => 'franchise',
                'invoice_number' => $source->franchiseInvoice->invoice_number ?? '',
                'reference' => $source->reference,
            ];
        } elseif ($type === 'own_income') {
            $source = JournalTransaction::with(['journalEntries.account'])->findOrFail($id);
            $amount = $source->journalEntries->max(fn($e) => max((float) $e->debit, (float) $e->credit));
            $cashAccountId = Account::where('code', '1010')->value('id');
            $hasCashDebit = $source->journalEntries->contains(fn($e) => $e->account_id == $cashAccountId && (float) $e->debit > 0);

            $voucher = (object) [
                'voucher_number' => $source->reference,
                'date' => $source->date,
                'received_from' => str_replace('Own income: ', '', $source->description ?? ''),
                'category' => 'Own Income',
                'amount' => $amount,
                'received_via' => $hasCashDebit ? 'Cash' : 'Bank',
                'notes' => $source->description,
                'created_at' => $source->created_at,
                'type' => 'own_income',
            ];
        } else {
            abort(404);
        }

        return view('accounting.receipt_vouchers.show', compact('voucher'));
    }

    // ═══════════════════════════════════════════
    //  PENDING DUES (Bills & Invoices)
    // ═══════════════════════════════════════════

    /**
     * Supplier Bills Index
     */
    public function billsIndex(Request $request)
    {
        $query = SupplierBill::with(['supplier', 'purchaseOrder']);
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        $bills = $query->orderBy('created_at', 'desc')->get();

        return view('accounting.bills.index', compact('bills'));
    }

    /**
     * Supplier Bill Detail (simplified – no payment form)
     */
    public function billShow(SupplierBill $bill)
    {
        $bill->load(['supplier', 'purchaseOrder', 'payments']);

        return view('accounting.bills.show', compact('bill'));
    }

    /**
     * Franchise Invoices Index
     */
    public function franchiseInvoicesIndex(Request $request)
    {
        $query = FranchiseInvoice::with(['outlet', 'salesLog']);
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        $invoices = $query->orderBy('created_at', 'desc')->get();

        return view('accounting.franchise_invoices.index', compact('invoices'));
    }

    /**
     * Franchise Invoice Detail (simplified – no collection form)
     */
    public function franchiseInvoiceShow(FranchiseInvoice $invoice)
    {
        $invoice->load(['outlet', 'salesLog.items.product', 'receipts']);

        return view('accounting.franchise_invoices.show', compact('invoice'));
    }

    // ═══════════════════════════════════════════
    //  TRANSACTION HISTORY
    // ═══════════════════════════════════════════

    /**
     * Full transaction history with cash/bank flow.
     */
    public function transactionHistory(Request $request)
    {
        $startDate = $request->get('start_date', '2020-01-01'); // Show all transactions by default
        $endDate = $request->get('end_date', now()->toDateString());

        $cashAccountId = Account::where('code', '1010')->value('id');
        $bankAccountId = Account::where('code', '1020')->value('id');

        $journalTransactions = JournalTransaction::with(['journalEntries.account'])
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $transactions = $journalTransactions->map(function ($tx) use ($cashAccountId, $bankAccountId) {
            $cashIn = 0;
            $cashOut = 0;
            $bankIn = 0;
            $bankOut = 0;

            foreach ($tx->journalEntries as $entry) {
                if ($entry->account_id == $cashAccountId) {
                    $cashIn += (float) $entry->debit;
                    $cashOut += (float) $entry->credit;
                }
                if ($entry->account_id == $bankAccountId) {
                    $bankIn += (float) $entry->debit;
                    $bankOut += (float) $entry->credit;
                }
            }

            return [
                'date' => $tx->date,
                'reference' => $tx->reference,
                'description' => $this->describeTransaction($tx),
                'cash_in' => $cashIn,
                'cash_out' => $cashOut,
                'bank_in' => $bankIn,
                'bank_out' => $bankOut,
                'cash_amount' => $cashIn - $cashOut,
                'bank_amount' => $bankIn - $bankOut,
            ];
        });

        // Calculate running balance
        $runningBalance = 0;
        $transactions = $transactions->map(function ($tx) use (&$runningBalance) {
            $runningBalance += $tx['cash_amount'] + $tx['bank_amount'];
            $tx['running_balance'] = $runningBalance;
            return $tx;
        });

        // Current totals
        $cashBalance = Account::where('code', '1010')->first()?->balance ?? 0;
        $bankBalance = Account::where('code', '1020')->first()?->balance ?? 0;

        return view('accounting.transaction_history', compact(
            'transactions',
            'cashBalance',
            'bankBalance',
            'startDate',
            'endDate'
        ));
    }

    // ═══════════════════════════════════════════
    //  REPORTS (Simplified P&L)
    // ═══════════════════════════════════════════

    /**
     * Simplified Income & Expense Report
     */
    public function reports(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        // Income section
        $ownSalesAccount = Account::where('code', '4010')->first();
        $franchiseSalesAccount = Account::where('code', '4020')->first();

        $ownSales = 0;
        if ($ownSalesAccount) {
            $credits = JournalEntry::where('account_id', $ownSalesAccount->id)
                ->whereHas('journalTransaction', fn($q) => $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate))
                ->sum('credit');
            $debits = JournalEntry::where('account_id', $ownSalesAccount->id)
                ->whereHas('journalTransaction', fn($q) => $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate))
                ->sum('debit');
            $ownSales = $credits - $debits;
        }

        $franchiseSales = 0;
        if ($franchiseSalesAccount) {
            $credits = JournalEntry::where('account_id', $franchiseSalesAccount->id)
                ->whereHas('journalTransaction', fn($q) => $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate))
                ->sum('credit');
            $debits = JournalEntry::where('account_id', $franchiseSalesAccount->id)
                ->whereHas('journalTransaction', fn($q) => $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate))
                ->sum('debit');
            $franchiseSales = $credits - $debits;
        }

        $incomes = [
            ['name' => 'Own Outlet Sales', 'amount' => $ownSales],
            ['name' => 'Franchise Sales', 'amount' => $franchiseSales],
        ];
        $totalIncome = $ownSales + $franchiseSales;

        // Expenses section
        $expenseAccounts = Account::where('type', 'expense')->orderBy('code', 'asc')->get();
        $expenses = [];
        $totalExpenses = 0;

        foreach ($expenseAccounts as $acc) {
            $debits = JournalEntry::where('account_id', $acc->id)
                ->whereHas('journalTransaction', fn($q) => $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate))
                ->sum('debit');
            $credits = JournalEntry::where('account_id', $acc->id)
                ->whereHas('journalTransaction', fn($q) => $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate))
                ->sum('credit');
            $amt = $debits - $credits;

            if ($amt != 0) {
                $expenses[] = [
                    'name' => $this->expenseNameMap[$acc->code] ?? $acc->name,
                    'amount' => $amt,
                ];
                $totalExpenses += $amt;
            }
        }

        $netProfit = $totalIncome - $totalExpenses;

        return view('accounting.reports', compact(
            'incomes',
            'expenses',
            'totalIncome',
            'totalExpenses',
            'netProfit',
            'startDate',
            'endDate'
        ));
    }

    /**
     * List all transfers (Bank to Cash / Cash to Bank).
     */
    public function transfersIndex()
    {
        $transfers = JournalTransaction::with(['journalEntries.account'])
            ->where('reference', 'like', 'TRF-%')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($tx) {
                $debitEntry = $tx->journalEntries->firstWhere('debit', '>', 0);
                $creditEntry = $tx->journalEntries->firstWhere('credit', '>', 0);
                
                return (object) [
                    'id' => $tx->id,
                    'reference' => $tx->reference,
                    'date' => $tx->date,
                    'description' => $tx->description,
                    'amount' => $debitEntry ? (float)$debitEntry->debit : 0.00,
                    'from_account' => $creditEntry->account->name ?? 'Unknown',
                    'to_account' => $debitEntry->account->name ?? 'Unknown',
                ];
            });

        return view('accounting.transfers.index', compact('transfers'));
    }

    /**
     * Show transfer creation form.
     */
    public function transferCreate()
    {
        $cashBalance = Account::where('code', '1010')->first()?->balance ?? 0;
        $bankBalance = Account::where('code', '1020')->first()?->balance ?? 0;

        // Auto-generate reference TRF-YYYY-XXXX
        $year = now()->year;
        $latest = JournalTransaction::where('reference', 'like', "TRF-{$year}-%")->orderBy('id', 'desc')->first();
        if ($latest) {
            $seq = (int) substr($latest->reference, 9) + 1;
        } else {
            $seq = 1;
        }
        $reference = "TRF-{$year}-" . str_pad($seq, 4, '0', STR_PAD_LEFT);

        return view('accounting.transfers.create', compact('cashBalance', 'bankBalance', 'reference'));
    }

    /**
     * Store new transfer.
     */
    public function transferStore(Request $request)
    {
        $request->validate([
            'reference' => 'required|string|unique:journal_transactions,reference',
            'date' => 'required|date',
            'from_account' => 'required|in:cash,bank',
            'to_account' => 'required|in:cash,bank',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        if ($request->from_account === $request->to_account) {
            return back()->withInput()->with('error', 'Source and destination accounts must be different.');
        }

        $fromCode = $request->from_account === 'cash' ? '1010' : '1020';
        $toCode = $request->to_account === 'cash' ? '1010' : '1020';

        $fromAccount = Account::where('code', $fromCode)->firstOrFail();
        $toAccount = Account::where('code', $toCode)->firstOrFail();

        // Check if source account has sufficient balance
        if ($fromAccount->balance < $request->amount) {
            return back()->withInput()->with('error', "Insufficient funds in {$fromAccount->name}. Current balance: ₹" . number_format($fromAccount->balance, 2));
        }

        try {
            DB::beginTransaction();

            // 1. Create Journal Transaction
            $tx = JournalTransaction::create([
                'reference' => $request->reference,
                'description' => $request->description ?: "Fund transfer from {$fromAccount->name} to {$toAccount->name}",
                'date' => $request->date,
            ]);

            // 2. Debit the destination (increases balance)
            JournalEntry::create([
                'journal_transaction_id' => $tx->id,
                'account_id' => $toAccount->id,
                'debit' => $request->amount,
                'credit' => 0.00,
            ]);

            // 3. Credit the source (decreases balance)
            JournalEntry::create([
                'journal_transaction_id' => $tx->id,
                'account_id' => $fromAccount->id,
                'debit' => 0.00,
                'credit' => $request->amount,
            ]);

            DB::commit();

            return redirect()->route('accounting.transfers.index')
                ->with('success', 'Fund transfer recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error recording transfer: ' . $e->getMessage());
        }
    }
}
