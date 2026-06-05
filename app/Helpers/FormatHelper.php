<?php

namespace App\Helpers;

class FormatHelper
{
    /**
     * Format a number as Indonesian Rupiah.
     * Example: 1500000 => "Rp 1.500.000"
     */
    public static function formatIDR($amount): string
    {
        if ($amount === null) return 'Rp 0';
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }

    /**
     * Parse an IDR-formatted string back to float.
     * Example: "Rp 1.500.000" => 1500000.0
     */
    public static function parseIDR($value): float
    {
        // Strip everything except digits
        return (float) preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Format a date as Indonesian long format.
     * Example: "2024-01-15" => "15 Januari 2024"
     *
     * @param  string|\DateTimeInterface  $date
     */
    public static function formatDate($date): string
    {
        if ($date instanceof \DateTimeInterface) {
            $ts = $date->getTimestamp();
        } else {
            $ts = strtotime((string) $date);
        }

        if ($ts === false) {
            return '';
        }

        $day   = (int) date('j', $ts);
        $month = (int) date('n', $ts);
        $year  = (int) date('Y', $ts);

        return "{$day} " . self::monthName($month) . " {$year}";
    }

    /**
     * Return the Indonesian name for a given month number (1-12).
     */
    public static function monthName(int $month): string
    {
        $names = [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $names[$month] ?? '';
    }

    /**
     * Return the Indonesian label for a CRM pipeline stage.
     *
     * Supported stages: prospecting, qualification, proposal, negotiation,
     *                   won, lost, closed
     */
    public static function stageLabel(string $stage): string
    {
        $labels = [
            'prospecting'   => 'Prospek',
            'qualification' => 'Kualifikasi',
            'proposal'      => 'Penawaran',
            'negotiation'   => 'Negosiasi',
            'won'           => 'Berhasil',
            'lost'          => 'Gagal',
            'closed'        => 'Ditutup',
        ];

        return $labels[strtolower($stage)] ?? ucfirst($stage);
    }

    /**
     * Return the Indonesian label for an approval level.
     *
     * Level 1 => Manager
     * Level 2 => General Manager
     * Level 3 => Direktur
     */
    public static function approvalLevelLabel(int $level): string
    {
        $labels = [
            1 => 'Manager',
            2 => 'General Manager',
            3 => 'Direktur',
        ];

        return $labels[$level] ?? "Level {$level}";
    }
}
