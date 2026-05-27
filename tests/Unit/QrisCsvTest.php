<?php

namespace Tests\Unit;

use App\Support\QrisCsv;
use Tests\TestCase;

class QrisCsvTest extends TestCase
{
    public function test_it_reads_the_public_qris_csv_summary(): void
    {
        $summary = QrisCsv::summarize(public_path('qris.csv'));

        $this->assertSame('Yusuf Tech', $summary['merchant_name']);
        $this->assertSame(3, $summary['payment_count']);
        $this->assertSame(303000, $summary['total_amount']);
        $this->assertSame(3000, $summary['latest_amount']);
        $this->assertNotEmpty($summary['recent_transactions']);
    }
}