<?php

use App\Jobs\SendDonationReceipt;
use App\Mail\DonationReceiptMail;
use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Models\User;
use App\Services\RazorpayService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

const TEST_SECRET = 'testsecret';
const WEBHOOK_SECRET = 'whsecret';

beforeEach(function () {
    config()->set('services.razorpay.key', 'rzp_test_key');
    config()->set('services.razorpay.secret', TEST_SECRET);
    config()->set('services.razorpay.webhook_secret', WEBHOOK_SECRET);

    // Fake order creation; keep real HMAC verification.
    app()->instance(RazorpayService::class, new class extends RazorpayService
    {
        public function createOrder(int $amountPaise, string $receipt, array $notes = []): array
        {
            // Unique per receipt (don_<id>) — mirrors Razorpay's unique order ids.
            return ['id' => 'order_'.$receipt, 'amount' => $amountPaise, 'currency' => 'INR'];
        }
    });
});

function donor(array $attrs = []): User
{
    return User::factory()->create(array_merge(['email_verified_at' => now()], $attrs));
}

function paySignature(string $orderId, string $paymentId): string
{
    return hash_hmac('sha256', $orderId.'|'.$paymentId, TEST_SECRET);
}

test('campaigns index and detail render for verified users incl. pending', function () {
    DonationCampaign::factory(2)->create(['is_active' => true]);
    $pending = donor(['status' => 'pending']);

    $this->get(route('donate.index'))->assertRedirect(route('login'));
    $this->actingAs($pending)->get(route('donate.index'))
        ->assertInertia(fn ($p) => $p->component('Donate/Index'));
});

test('create-order makes a pending donation and returns order details', function () {
    $u = donor();
    $campaign = DonationCampaign::factory()->create(['is_active' => true]);

    $this->actingAs($u)
        ->postJson(route('donate.create-order'), [
            'campaign_id' => $campaign->id,
            'amount' => 500,
            'is_anonymous' => true,
        ])
        ->assertOk()
        ->assertJson(['amount' => 500]);

    $d = Donation::first();
    expect($d->status)->toBe('pending');
    expect($d->razorpay_order_id)->toBe('order_don_'.$d->id);
    expect((int) $d->amount)->toBe(500);
    expect($d->is_anonymous)->toBeTrue();
});

test('create-order rejects out-of-range amounts and inactive campaigns', function () {
    $u = donor();
    $this->actingAs($u)->postJson(route('donate.create-order'), ['amount' => 50])
        ->assertStatus(422);
    $this->actingAs($u)->postJson(route('donate.create-order'), ['amount' => 2000000])
        ->assertStatus(422);

    $inactive = DonationCampaign::factory()->create(['is_active' => false]);
    $this->actingAs($u)->postJson(route('donate.create-order'), [
        'campaign_id' => $inactive->id, 'amount' => 500,
    ])->assertStatus(422);
});

test('create-order is rate limited at 10 per minute', function () {
    $u = donor();
    $campaign = DonationCampaign::factory()->create(['is_active' => true]);

    for ($i = 1; $i <= 10; $i++) {
        $this->actingAs($u)->postJson(route('donate.create-order'), [
            'campaign_id' => $campaign->id, 'amount' => 100,
        ])->assertOk();
    }

    $this->actingAs($u)->postJson(route('donate.create-order'), [
        'campaign_id' => $campaign->id, 'amount' => 100,
    ])->assertStatus(429);
});

test('verify with a valid signature marks success and increments the campaign once', function () {
    Bus::fake();
    $u = donor();
    $campaign = DonationCampaign::factory()->create(['raised_amount' => 1000]);
    $d = Donation::create([
        'user_id' => $u->id, 'campaign_id' => $campaign->id, 'amount' => 500,
        'currency' => 'INR', 'razorpay_order_id' => 'order_X', 'status' => 'pending',
    ]);

    $sig = paySignature('order_X', 'pay_X');
    $payload = [
        'razorpay_order_id' => 'order_X',
        'razorpay_payment_id' => 'pay_X',
        'razorpay_signature' => $sig,
        'donation_id' => $d->id,
    ];

    $this->actingAs($u)->postJson(route('donate.verify'), $payload)
        ->assertOk()->assertJson(['success' => true]);

    expect($d->fresh()->status)->toBe('success');
    expect((int) $campaign->fresh()->raised_amount)->toBe(1500);
    Bus::assertDispatched(SendDonationReceipt::class);

    // Idempotent — second call no-ops, no double increment.
    $this->actingAs($u)->postJson(route('donate.verify'), $payload)
        ->assertOk()->assertJson(['already_processed' => true]);
    expect((int) $campaign->fresh()->raised_amount)->toBe(1500);
});

test('verify with a tampered signature is rejected', function () {
    $u = donor();
    $d = Donation::create([
        'user_id' => $u->id, 'amount' => 500, 'currency' => 'INR',
        'razorpay_order_id' => 'order_Y', 'status' => 'pending',
    ]);

    $this->actingAs($u)->postJson(route('donate.verify'), [
        'razorpay_order_id' => 'order_Y',
        'razorpay_payment_id' => 'pay_Y',
        'razorpay_signature' => 'tampered',
        'donation_id' => $d->id,
    ])->assertStatus(400);

    expect($d->fresh()->status)->toBe('pending');
});

test('webhook verifies signature, is idempotent, and races safely with verify', function () {
    Bus::fake();
    $u = donor();
    $campaign = DonationCampaign::factory()->create(['raised_amount' => 0]);
    $d = Donation::create([
        'user_id' => $u->id, 'campaign_id' => $campaign->id, 'amount' => 700,
        'currency' => 'INR', 'razorpay_order_id' => 'order_W', 'status' => 'pending',
    ]);

    $body = json_encode([
        'event' => 'payment.captured',
        'payload' => ['payment' => ['entity' => ['order_id' => 'order_W', 'id' => 'pay_W']]],
    ]);
    $sig = hash_hmac('sha256', $body, WEBHOOK_SECRET);

    // Bad signature → 400
    $this->call('POST', route('webhooks.razorpay'), [], [], [], [
        'HTTP_X-Razorpay-Signature' => 'bogus', 'CONTENT_TYPE' => 'application/json',
    ], $body)->assertStatus(400);

    // Good signature → 200, marks success, increments once
    $this->call('POST', route('webhooks.razorpay'), [], [], [], [
        'HTTP_X-Razorpay-Signature' => $sig, 'CONTENT_TYPE' => 'application/json',
    ], $body)->assertOk()->assertJson(['ok' => true]);

    expect($d->fresh()->status)->toBe('success');
    expect((int) $campaign->fresh()->raised_amount)->toBe(700);

    // Duplicate webhook → no double increment
    $this->call('POST', route('webhooks.razorpay'), [], [], [], [
        'HTTP_X-Razorpay-Signature' => $sig, 'CONTENT_TYPE' => 'application/json',
    ], $body)->assertOk();
    expect((int) $campaign->fresh()->raised_amount)->toBe(700);

    // Now the verify redirect arrives late → must no-op
    $this->actingAs($u)->postJson(route('donate.verify'), [
        'razorpay_order_id' => 'order_W',
        'razorpay_payment_id' => 'pay_W',
        'razorpay_signature' => paySignature('order_W', 'pay_W'),
        'donation_id' => $d->id,
    ])->assertOk()->assertJson(['already_processed' => true]);
    expect((int) $campaign->fresh()->raised_amount)->toBe(700);
});

test('webhook payment.failed marks the donation failed without incrementing', function () {
    $u = donor();
    $campaign = DonationCampaign::factory()->create(['raised_amount' => 0]);
    $d = Donation::create([
        'user_id' => $u->id, 'campaign_id' => $campaign->id, 'amount' => 400,
        'currency' => 'INR', 'razorpay_order_id' => 'order_F', 'status' => 'pending',
    ]);

    $body = json_encode([
        'event' => 'payment.failed',
        'payload' => ['payment' => ['entity' => ['order_id' => 'order_F', 'id' => 'pay_F']]],
    ]);
    $sig = hash_hmac('sha256', $body, WEBHOOK_SECRET);

    $this->call('POST', route('webhooks.razorpay'), [], [], [], [
        'HTTP_X-Razorpay-Signature' => $sig, 'CONTENT_TYPE' => 'application/json',
    ], $body)->assertOk();

    expect($d->fresh()->status)->toBe('failed');
    expect((int) $campaign->fresh()->raised_amount)->toBe(0);
});

test('webhook route is exempt from CSRF', function () {
    // No CSRF token, no 419.
    $resp = $this->call('POST', route('webhooks.razorpay'), [], [], [], [
        'HTTP_X-Razorpay-Signature' => 'x', 'CONTENT_TYPE' => 'application/json',
    ], '{}');
    expect($resp->status())->not->toBe(419);
});

test('success page and receipt download are owner or admin only', function () {
    $owner = donor();
    $other = donor();
    $campaign = DonationCampaign::factory()->create();
    $d = Donation::create([
        'user_id' => $owner->id, 'campaign_id' => $campaign->id, 'amount' => 500,
        'currency' => 'INR', 'razorpay_order_id' => 'order_S', 'status' => 'success',
        'razorpay_payment_id' => 'pay_S',
    ]);

    $this->actingAs($other)->get(route('donate.success', $d->id))->assertForbidden();
    $this->actingAs($owner)->get(route('donate.success', $d->id))->assertOk();

    $this->actingAs($other)->get(route('donate.receipt', $d->id))->assertForbidden();
    $this->actingAs($owner)->get(route('donate.receipt', $d->id))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

test('SendDonationReceipt generates a pdf and emails the donor', function () {
    Storage::fake('public');
    Mail::fake();
    $u = donor();
    $campaign = DonationCampaign::factory()->create();
    $d = Donation::create([
        'user_id' => $u->id, 'campaign_id' => $campaign->id, 'amount' => 500,
        'currency' => 'INR', 'razorpay_order_id' => 'order_R', 'status' => 'success',
        'razorpay_payment_id' => 'pay_R',
    ]);

    (new SendDonationReceipt($d))->handle();

    expect($d->fresh()->receipt_path)->toBe("receipts/{$d->id}.pdf");
    Storage::disk('public')->assertExists("receipts/{$d->id}.pdf");
    Mail::assertSent(DonationReceiptMail::class, fn ($m) => $m->hasTo($u->email));
});

test('admin campaign CRUD is admin only', function () {
    $this->actingAs(donor())->get(route('admin.campaigns.index'))->assertForbidden();

    $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
    $this->actingAs($admin)->post(route('admin.campaigns.store'), [
        'title' => 'New Library Fund',
        'description' => 'Help us build a library.',
        'target_amount' => 500000,
        'is_active' => true,
    ])->assertRedirect(route('admin.campaigns.index'));

    expect(DonationCampaign::where('title', 'New Library Fund')->exists())->toBeTrue();
});

test('admin donations index lists and filters by status', function () {
    $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
    $campaign = DonationCampaign::factory()->create();
    Donation::factory(3)->create(['campaign_id' => $campaign->id, 'status' => 'success']);
    Donation::factory(2)->create(['campaign_id' => $campaign->id, 'status' => 'pending']);

    $this->actingAs(donor())->get(route('admin.donations.index'))->assertForbidden();

    $this->actingAs($admin)->get(route('admin.donations.index'))
        ->assertInertia(fn ($p) => $p->component('Admin/Donations/Index')->where('donations.total', 5));

    $this->actingAs($admin)->get(route('admin.donations.index', ['status' => 'success']))
        ->assertInertia(fn ($p) => $p->where('donations.total', 3));
});
