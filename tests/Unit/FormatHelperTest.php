<?php

namespace Tests\Unit;

use App\Helpers\FormatHelper;
use PHPUnit\Framework\TestCase;

class FormatHelperTest extends TestCase
{
    // -------------------------------------------------------------------------
    // formatIDR
    // -------------------------------------------------------------------------

    /**
     * formatIDR(1500000) should return "Rp 1.500.000"
     * (Indonesian format: dot as thousands separator, no decimals).
     */
    public function test_format_idr_returns_correct_format(): void
    {
        $this->assertSame('Rp 1.500.000', FormatHelper::formatIDR(1_500_000));
    }

    /**
     * formatIDR(200000000) should return "Rp 200.000.000".
     */
    public function test_format_idr_large_number(): void
    {
        $this->assertSame('Rp 200.000.000', FormatHelper::formatIDR(200_000_000));
    }

    // -------------------------------------------------------------------------
    // parseIDR
    // -------------------------------------------------------------------------

    /**
     * parseIDR("Rp 1.500.000") should return the float 1500000.0.
     */
    public function test_parse_idr_returns_float(): void
    {
        $result = FormatHelper::parseIDR('Rp 1.500.000');
        $this->assertSame(1_500_000.0, $result);
    }

    // -------------------------------------------------------------------------
    // monthName
    // -------------------------------------------------------------------------

    /**
     * monthName(1) should return the Indonesian name "Januari".
     */
    public function test_month_name_returns_indonesian(): void
    {
        $this->assertSame('Januari', FormatHelper::monthName(1));
    }

    /**
     * Additional spot-checks to ensure the full month map is correct.
     */
    public function test_month_name_spot_checks(): void
    {
        $this->assertSame('Februari', FormatHelper::monthName(2));
        $this->assertSame('Maret',    FormatHelper::monthName(3));
        $this->assertSame('April',    FormatHelper::monthName(4));
        $this->assertSame('Mei',      FormatHelper::monthName(5));
        $this->assertSame('Juni',     FormatHelper::monthName(6));
        $this->assertSame('Juli',     FormatHelper::monthName(7));
        $this->assertSame('Agustus',  FormatHelper::monthName(8));
        $this->assertSame('September',FormatHelper::monthName(9));
        $this->assertSame('Oktober',  FormatHelper::monthName(10));
        $this->assertSame('November', FormatHelper::monthName(11));
        $this->assertSame('Desember', FormatHelper::monthName(12));
    }
}
