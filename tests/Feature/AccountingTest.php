<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Supplier;
use App\Models\Material;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SupplierBill;
use App\Models\FranchiseInvoice;
use App\Models\Outlet;
use App\Models\SalesLog;
use App\Models\SalesLogItem;
use App\Models\Product;
use App\Models\User;
use App\Models\ExpenseVoucher;
use App\Models\JournalTransaction;
use App\Models\JournalEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $accountant;
    protected $supplier;
    protected $material;
    protected $product;
    protected $franchise;
    protected $ownStore;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create admin user
        $this->user = User::factory()->create([
            'email' => 'admin@dessertops.com',
            'password' => bcrypt('password'),
        ]);

        // Create accountant user
        $this->accountant = \App\Models\Accountant::create([
            'name' => 'Test Accountant',
            'email' => 'accountant@dessertops.com',
            'password' => bcrypt('password'),
        ]);

        // 2. Create basic supplier and material
        $this->supplier = Supplier::create([
            'name' => 'Test Supplier',
            'contact_person' => 'Supplier Contact',
        ]);

        $this->material = Material::create([
            'name' => 'Flour',
            'sku' => 'MAT-FLOUR',
            'category' => 'ingredient',
            'unit' => 'kg',
            'current_stock' => 100.00,
            'min_stock_alert' => 10.00,
        ]);

        $this->product = Product::create([
            'name' => 'Gulab Jamun Box',
            'sku' => 'PROD-GJ',
            'retail_price' => 150.00,
            'current_kitchen_stock' => 50,
        ]);

        // 3. Create own and franchise outlets
        $this->franchise = Outlet::create([
            'name' => 'Franchise Outlet',
            'type' => 'franchise',
            'commission_rate' => 15.00,
        ]);

        $this->ownStore = Outlet::create([
            'name' => 'Own Store',
            'type' => 'own',
            'commission_rate' => 0.00,
        ]);

        // 4. Seed basic Chart of Accounts
        Account::create(['code' => '1010', 'name' => 'Cash', 'type' => 'asset']);
        Account::create(['code' => '1020', 'name' => 'Bank', 'type' => 'asset']);
        Account::create(['code' => '1200', 'name' => 'Accounts Receivable', 'type' => 'asset']);
        Account::create(['code' => '1300', 'name' => 'Inventory', 'type' => 'asset']);
        Account::create(['code' => '2100', 'name' => 'Accounts Payable', 'type' => 'liability']);
        Account::create(['code' => '3000', 'name' => 'Capital', 'type' => 'equity']);
        Account::create(['code' => '3900', 'name' => 'Retained Earnings', 'type' => 'equity']);
        Account::create(['code' => '4010', 'name' => 'Own Sales', 'type' => 'revenue']);
        Account::create(['code' => '4020', 'name' => 'Franchise Sales', 'type' => 'revenue']);
        Account::create(['code' => '5010', 'name' => 'Purchases', 'type' => 'expense']);
        Account::create(['code' => '5100', 'name' => 'Rent', 'type' => 'expense']);
    }

    /** @test */
    public function an_authenticated_accountant_can_access_accounting_dashboard()
    {
        $response = $this->actingAs($this->accountant, 'accountant')
            ->get(route('accounting.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Money Overview');
    }

    /** @test */
    public function receiving_a_purchase_order_automatically_creates_supplier_bill_and_journal_entries()
    {
        // 1. Create a PO
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-0001',
            'supplier_id' => $this->supplier->id,
            'status' => 'pending',
            'total_amount' => 1000.00,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id' => $this->material->id,
            'quantity' => 10,
            'unit_price' => 100.00,
        ]);

        // 2. Receive the PO (create GRN)
        $response = $this->actingAs($this->user)
            ->post(route('purchase-orders.receive.store', $po->id), [
                'received_by' => 'Receiver User',
                'received_date' => now()->toDateString(),
                'items' => [
                    [
                        'material_id' => $this->material->id,
                        'quantity_received' => 10
                    ]
                ]
            ]);

        $response->assertRedirect();
        
        // 3. Verify Supplier Bill was created
        $bill = SupplierBill::where('purchase_order_id', $po->id)->first();
        $this->assertNotNull($bill);
        $this->assertEquals(1000.00, $bill->amount);
        $this->assertEquals('unpaid', $bill->status);

        // 4. Verify GL postings (Debit Inventory 1300, Credit AP 2100)
        $inventoryAcc = Account::where('code', '1300')->first();
        $apAcc = Account::where('code', '2100')->first();

        $this->assertEquals(1000.00, $inventoryAcc->balance);
        $this->assertEquals(1000.00, $apAcc->balance); // liability balances are Cr - Dr, so positive is credit balance
    }

    /** @test */
    public function recording_a_supplier_payment_updates_bill_status_and_ledger()
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-0002',
            'supplier_id' => $this->supplier->id,
            'status' => 'received',
            'total_amount' => 1500.00,
        ]);

        $bill = SupplierBill::create([
            'bill_number' => 'BILL-2026-9999',
            'purchase_order_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'amount' => 1500.00,
            'status' => 'unpaid',
            'due_date' => now()->addDays(15),
        ]);

        $bankAccount = Account::where('code', '1020')->first();

        // Pay the bill partially via Payment Voucher
        $response = $this->actingAs($this->accountant, 'accountant')
            ->post(route('accounting.payment-vouchers.store'), [
                'payment_type' => 'supplier',
                'voucher_number' => 'PV-2026-0001',
                'supplier_bill_id' => $bill->id,
                'amount' => 500.00,
                'date' => now()->toDateString(),
                'paid_via' => 'bank',
            ]);

        $response->assertRedirect();
        
        $bill->refresh();
        $this->assertEquals('partially_paid', $bill->status);
        $this->assertEquals(1000.00, $bill->remaining_amount);

        // Pay the rest
        $this->actingAs($this->accountant, 'accountant')
            ->post(route('accounting.payment-vouchers.store'), [
                'payment_type' => 'supplier',
                'voucher_number' => 'PV-2026-0002',
                'supplier_bill_id' => $bill->id,
                'amount' => 1000.00,
                'date' => now()->toDateString(),
                'paid_via' => 'bank',
            ]);

        $bill->refresh();
        $this->assertEquals('paid', $bill->status);
        $this->assertEquals(0.00, $bill->remaining_amount);
    }

    /** @test */
    public function submitting_franchise_sales_log_creates_invoice_and_receivable()
    {
        // 1. Setup mock sales log
        $salesLog = SalesLog::create([
            'outlet_id' => $this->franchise->id,
            'log_date' => now()->toDateString(),
        ]);

        SalesLogItem::create([
            'sales_log_id' => $salesLog->id,
            'product_id' => $this->product->id,
            'quantity_sold' => 10,
            'unit_price' => 150.00,
            'total_revenue' => 1500.00,
            'commission_amount' => 225.00, // 15%
            'net_revenue' => 1275.00,
        ]);

        // Trigger hooks by recreating the logic or calling controller.
        // Let's verify that when a sales log is created, our invoice is automatically generated.
        // Wait, the hook is in the controller's store/salesStore action. Let's test by posting to the outlet portal endpoint!
        
        // Seed outlet stock first
        $this->franchise->stocks()->create([
            'product_id' => $this->product->id,
            'quantity' => 10
        ]);

        $response = $this->actingAs($this->franchise, 'outlet')
            ->withSession(['portal_outlet_id' => $this->franchise->id])
            ->post(route('portal.sales.store'), [
                'log_date' => now()->toDateString(),
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity_sold' => 2
                    ]
                ]
            ]);

        $response->assertRedirect();

        // Verify franchise invoice created for commission net revenue share
        $invoice = FranchiseInvoice::where('outlet_id', $this->franchise->id)->first();
        $this->assertNotNull($invoice);
        
        // Quantity sold = 2 * 150 retail_price = 300 total revenue
        // Net revenue = 300 - 15% (45) = 255.00
        $this->assertEquals(255.00, $invoice->amount);

        // Verify ledger: Accounts Receivable debited 255.00
        $arAccount = Account::where('code', '1200')->first();
        $this->assertEquals(255.00, $arAccount->balance);
    }

    /** @test */
    public function manual_expense_voucher_posts_debit_expense_and_credit_cash_bank()
    {
        $expenseAcc = Account::where('code', '5100')->first();
        $bankAcc = Account::where('code', '1020')->first();

        $response = $this->actingAs($this->accountant, 'accountant')
            ->post(route('accounting.payment-vouchers.store'), [
                'payment_type' => 'expense',
                'voucher_number' => 'PV-2026-9090',
                'payee' => 'Landlord Rent Co',
                'amount' => 12000.00,
                'expense_account_id' => $expenseAcc->id,
                'paid_via' => 'bank',
                'date' => now()->toDateString(),
                'notes' => 'Factory outlet rent payment',
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('expense_vouchers', [
            'voucher_number' => 'PV-2026-9090',
            'amount' => 12000.00
        ]);

        // Rent expense should have debit balance of 12000
        $this->assertEquals(12000.00, $expenseAcc->balance);
        
        // Bank account balance should decrease by 12000 (meaning balance decreases or is negative)
        $this->assertEquals(-12000.00, $bankAcc->balance);
    }

    /** @test */
    public function can_record_fund_transfers_between_bank_and_cash_accounts()
    {
        $cashAcc = Account::where('code', '1010')->first();
        $bankAcc = Account::where('code', '1020')->first();

        // 1. First, seed some initial balance to bank using a journal entry so we don't have negative balance
        $tx = JournalTransaction::create([
            'reference' => 'JV-INIT-TEST',
            'description' => 'Initial capital',
            'date' => now()->toDateString(),
        ]);
        JournalEntry::create([
            'journal_transaction_id' => $tx->id,
            'account_id' => $bankAcc->id,
            'debit' => 50000.00,
            'credit' => 0.00,
        ]);

        $this->assertEquals(50000.00, $bankAcc->balance);
        $this->assertEquals(0.00, $cashAcc->balance);

        // 2. Perform a transfer of 10000 from Bank to Cash
        $response = $this->actingAs($this->accountant, 'accountant')
            ->post(route('accounting.transfers.store'), [
                'reference' => 'TRF-2026-9900',
                'date' => now()->toDateString(),
                'from_account' => 'bank',
                'to_account' => 'cash',
                'amount' => 10000.00,
                'description' => 'ATM withdrawal to petty cash',
            ]);

        $response->assertRedirect(route('accounting.transfers.index'));

        // 3. Verify balances updated: Cash = 10000, Bank = 40000
        $this->assertEquals(40000.00, $bankAcc->balance);
        $this->assertEquals(10000.00, $cashAcc->balance);

        // 4. Perform a transfer of 4000 from Cash back to Bank
        $response = $this->actingAs($this->accountant, 'accountant')
            ->post(route('accounting.transfers.store'), [
                'reference' => 'TRF-2026-9901',
                'date' => now()->toDateString(),
                'from_account' => 'cash',
                'to_account' => 'bank',
                'amount' => 4000.00,
                'description' => 'Deposited extra cash',
            ]);

        $response->assertRedirect(route('accounting.transfers.index'));

        // 5. Verify balances updated: Cash = 6000, Bank = 44000
        $this->assertEquals(44000.00, $bankAcc->balance);
        $this->assertEquals(6000.00, $cashAcc->balance);
    }
}
