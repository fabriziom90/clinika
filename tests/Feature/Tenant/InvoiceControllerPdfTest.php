<?php

namespace Tests\Feature\Tenant;

use App\Models\Clinic;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InvoiceControllerPdfTest extends TestCase
{
    use WithoutMiddleware;

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

        Permission::on('tenant')->firstOrCreate([
            'name' => 'invoices.view',
            'guard_name' => 'web',
        ]);

    }

    public function test_show_generates_and_returns_invoice_pdf(): void
    {
        $clinic = $this->createClinic();

        app()->instance('currentClinic', $clinic);

        $user = $this->createUser();
        $user->givePermissionTo('invoices.view');

        $invoice = $this->createInvoice();

        $response = $this
            ->actingAs($user, 'web')
            ->get($this->url($clinic, "/admin/invoices/{$invoice->uuid}"));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_show_returns_existing_invoice_pdf(): void
    {
        $clinic = $this->createClinic();
        app()->instance('currentClinic', $clinic);

        $user = $this->createUser();
        $user->givePermissionTo('invoices.view');

        $invoice = $this->createInvoice();

        $path = "invoices/{$clinic->uuid}/{$invoice->year}/{$invoice->uuid}.pdf";

        Storage::disk('local')->put($path, '%PDF-existing%');

        $invoice->update([
            'pdf_path' => $path,
        ]);

        $response = $this
            ->actingAs($user, 'web')
            ->get($this->url($clinic, "/admin/invoices/{$invoice->uuid}"));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');

        $invoice->refresh();

        $this->assertSame($path, $invoice->pdf_path);
        $this->assertTrue(Storage::disk('local')->exists($path));
    }

    private function url(Clinic $clinic, string $path): string
    {
        return "http://{$clinic->slug}.clinika.test{$path}";
    }

    private function createClinic(): Clinic
    {
        $clinic = new Clinic;
        $clinic->setConnection('central');

        $clinic->name = 'Clinica Test '.uniqid();
        $clinic->slug = 'test-'.uniqid();
        $clinic->uuid = (string) \Illuminate\Support\Str::uuid();
        $clinic->active = true;

        $clinic->database = 'clinika_test_tenant';
        $clinic->db_host = '127.0.0.1';
        $clinic->db_port = 3306;
        $clinic->db_username = 'root';
        $clinic->db_password = '';

        $clinic->save();

        return $clinic;
    }

    private function createUser(): User
    {
        $user = new User;
        $user->setConnection('tenant');

        $user->name = 'Test';
        $user->surname = 'User';
        $user->email = 'invoice-pdf-'.uniqid().'@test.it';
        $user->email_hash = hash(
            'sha256',
            strtolower($user->email)
        );
        $user->password = bcrypt('password');

        $user->save();

        return $user;
    }

    private function createInvoice(): Invoice
    {
        $patient = new Patient;
        $patient->setConnection('tenant');
        $patient->name = 'Mario';
        $patient->surname = 'Rossi';
        $patient->email = 'mario.rossi.'.uniqid().'@test.it';
        $patient->personal_code = 'RSSMRA80A01H501X';
        $patient->birthday = '1980-01-01';
        $patient->city = 'Roma';
        $patient->zip_code = '00100';
        $patient->address = 'Via Test 1';
        $patient->phone = '3331234567';
        $patient->genre = 'M';
        $patient->save();

        $service = new Service;
        $service->setConnection('tenant');
        $service->code = 'VIS-TEST';
        $service->name = 'Visita specialistica';
        $service->default_price = 100;
        $service->default_duration = 30;
        $service->active = true;
        $service->save();

        $invoice = new Invoice;
        $invoice->setConnection('tenant');
        $invoice->uuid = (string) Str::uuid();
        $invoice->patient_id = $patient->id;
        $invoice->number = '1/2026';
        $invoice->progressive_number = 1;
        $invoice->year = 2026;
        $invoice->date = now();
        $invoice->subtotal = 100;
        $invoice->vat_amount = 0;
        $invoice->stamp_duty = 0;
        $invoice->discount_amount = 0;
        $invoice->total = 100;
        $invoice->amount = 100;
        $invoice->status = 'draft';
        $invoice->full_name = 'Mario Rossi';
        $invoice->vat_number = 'RSSMRA80A01H501X';
        $invoice->address = 'Via Test 1';
        $invoice->city = 'Roma';
        $invoice->zip_code = '00100';
        $invoice->description = 'Prestazione sanitaria';
        $invoice->save();

        $item = new InvoiceItem;
        $item->setConnection('tenant');
        $item->invoice_id = $invoice->id;
        $item->service_id = $service->id;
        $item->description = 'Visita specialistica';
        $item->quantity = 1;
        $item->unit_price = 100;
        $item->vat_percentage = 0;
        $item->total = 100;
        $item->save();

        return $invoice;
    }
}
