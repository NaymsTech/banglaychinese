<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_orders_table_has_the_expected_columns(): void
    {
        $columns = [
            'id',
            'legacy_source',
            'legacy_id',
            'user_id',
            'student_name',
            'student_email',
            'student_phone',
            'total_amount',
            'currency',
            'order_status',
            'admin_notes',
            'order_received_email_sent_at',
            'outcome_email_sent_at',
            'rejection_email_sent_at',
            'attention_email_sent_at',
            'payment_reminder_sent_at',
            'created_at',
            'updated_at',
        ];

        $this->assertTrue(Schema::hasColumns('orders', $columns));
    }

    public function test_order_items_table_has_the_expected_columns(): void
    {
        $columns = [
            'id',
            'order_id',
            'purchasable_type',
            'purchasable_id',
            'title',
            'unit_price',
            'quantity',
            'created_at',
            'updated_at',
        ];

        $this->assertTrue(Schema::hasColumns('order_items', $columns));
    }

    public function test_payments_table_has_the_expected_columns(): void
    {
        $columns = [
            'id',
            'order_id',
            'method',
            'trx_reference',
            'sender_number',
            'sender_details',
            'amount',
            'currency',
            'status',
            'review_note',
            'gateway',
            'gateway_ref',
            'gateway_raw_status',
            'callback_payload',
            'proof_path',
            'paid_at',
            'rejected_at',
            'refunded_at',
            'created_at',
            'updated_at',
        ];

        $this->assertTrue(Schema::hasColumns('payments', $columns));
    }

    public function test_legacy_traceability_columns_are_uniquely_indexed_on_orders(): void
    {
        $unique = collect(Schema::getIndexes('orders'))
            ->first(fn (array $index): bool => $index['unique'] === true);

        $this->assertNotNull($unique);
        $this->assertContains('legacy_source', $unique['columns']);
        $this->assertContains('legacy_id', $unique['columns']);
    }

    public function test_order_items_indexes_the_purchasable_pair(): void
    {
        $columns = collect(Schema::getIndexes('order_items'))
            ->pluck('columns');

        $this->assertTrue($columns->contains(fn (array $index): bool => in_array('purchasable_type', $index, true) && in_array('purchasable_id', $index, true)));
    }
}
