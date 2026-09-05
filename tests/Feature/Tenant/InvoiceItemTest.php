<?php

namespace Tests\Feature\Tenant;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Service;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InvoiceItemTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'tenant');

        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'clinika_test_tenant',
            'username' => 'root',
            'password' => '',
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    private function createInvoice(): Invoice
    {
        return Invoice::create([
            'uuid' => fake()->uuid(),
            'number' => 'FT-001/2026',
            'progressive_number' => 1,
            'year' => 2026,
            'date' => '2026-08-27',
            'subtotal' => 100.00,
            'vat_amount' => 22.00,
            'stamp_duty' => 2.00,
            'discount_amount' => 0.00,
            'total' => 124.00,
            'amount' => 124.00,
            'status' => 'draft',
            'payment_method' => 'card',
            'full_name' => 'Mario Rossi',
            'vat_number' => 'RSSMRA80A01H501Z',
            'address' => 'Via Roma 1',
            'city' => 'Roma',
            'zip_code' => '00100',
            'description' => 'Prestazione medica',
        ]);
    }

    private function createService(): Service
    {
        return Service::create([
            'code' => 'VIS001',
            'name' => 'Visita specialistica',
            'default_price' => 100.00,
            'default_duration' => 30,
            'active' => true,
        ]);
    }

    private function createInvoiceItem(Invoice $invoice, ?Service $service = null): InvoiceItem
    {
        return InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'service_id' => $service?->id,
            'description' => 'Visita specialistica',
            'quantity' => 1,
            'unit_price' => 100.00,
            'vat_percentage' => 22.00,
            'total' => 122.00,
        ]);
    }

    public function test_invoice_item_is_created_in_tenant_database(): void
    {
        $invoice = $this->createInvoice();
        $service = $this->createService();

        $item = $this->createInvoiceItem($invoice, $service);

        $this->assertSame('tenant', $item->getConnectionName());

        $this->assertDatabaseHas('invoice_items', [
            'id' => $item->id,
            'invoice_id' => $invoice->id,
            'service_id' => $service->id,
            'quantity' => 1,
            'unit_price' => 100.00,
            'vat_percentage' => 22.00,
            'total' => 122.00,
        ], 'tenant');
    }

    public function test_invoice_item_belongs_to_invoice(): void
    {
        $invoice = $this->createInvoice();
        $item = $this->createInvoiceItem($invoice);

        $this->assertInstanceOf(Invoice::class, $item->invoice);
        $this->assertSame($invoice->id, $item->invoice->id);
        $this->assertSame('tenant', $item->invoice->getConnectionName());
    }

    public function test_invoice_item_belongs_to_service(): void
    {
        $invoice = $this->createInvoice();
        $service = $this->createService();

        $item = $this->createInvoiceItem($invoice, $service);

        $this->assertInstanceOf(Service::class, $item->service);
        $this->assertSame($service->id, $item->service->id);
        $this->assertSame('tenant', $item->service->getConnectionName());
    }

    public function test_invoice_has_invoice_items(): void
    {
        $invoice = $this->createInvoice();
        $service = $this->createService();

        $item = $this->createInvoiceItem($invoice, $service);

        $this->assertTrue($invoice->invoiceItems->contains($item));
        $this->assertSame($item->id, $invoice->invoiceItems->first()->id);
    }

    public function test_invoice_item_description_is_encrypted_at_rest(): void
    {
        $invoice = $this->createInvoice();

        $item = $this->createInvoiceItem($invoice);

        $rawDescription = DB::connection('tenant')
            ->table('invoice_items')
            ->where('id', $item->id)
            ->value('description');

        $this->assertNotSame('Visita specialistica', $rawDescription);
        $this->assertNotEmpty($rawDescription);
    }

    public function test_invoice_item_description_is_decrypted_when_retrieved_through_eloquent(): void
    {
        $invoice = $this->createInvoice();

        $item = $this->createInvoiceItem($invoice);
        $item->refresh();

        $this->assertSame('Visita specialistica', $item->description);
    }
}
