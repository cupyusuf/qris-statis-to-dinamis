<?php

namespace App\Support;

final class QrisCsv
{
    /**
     * @return array{
     *     merchant_name: string,
     *     latest_amount: int,
     *     payment_count: int,
     *     total_amount: int,
     *     recent_transactions: array<int, array{
     *         type: string,
     *         amount: int,
     *         merchant_name: string,
     *         transaction_id: string,
     *         reference_id: string,
     *         create_time: string,
     *         update_time: string,
     *         payment_method: string,
     *     }>,
     * }
     */
    public static function summarize(string $path): array
    {
        if (! is_readable($path)) {
            return self::emptySummary();
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return self::emptySummary();
        }

        $headers = fgetcsv($handle);

        if ($headers === false || $headers === []) {
            fclose($handle);

            return self::emptySummary();
        }

        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            $normalizedRow = array_pad($row, count($headers), '');
            $entry = array_combine($headers, $normalizedRow);

            if ($entry === false) {
                continue;
            }

            $merchantName = trim((string) ($entry['Merchant/Store Name'] ?? ''));
            $transactionType = trim((string) ($entry['Transaction Type'] ?? ''));
            $amount = (int) preg_replace('/[^0-9-]/', '', (string) ($entry['Transaction Amount'] ?? '0'));

            $rows[] = [
                'type' => $transactionType,
                'amount' => max(0, $amount),
                'merchant_name' => $merchantName,
                'transaction_id' => trim((string) ($entry['Transaction ID'] ?? '')),
                'reference_id' => trim((string) ($entry['Reference ID'] ?? '')),
                'create_time' => trim((string) ($entry['Create Time'] ?? '')),
                'update_time' => trim((string) ($entry['Update Time'] ?? '')),
                'payment_method' => trim((string) ($entry['Payment Method'] ?? '')),
            ];
        }

        fclose($handle);

        $payments = array_values(array_filter(
            $rows,
            static fn(array $row): bool => strcasecmp($row['type'], 'Payment') === 0,
        ));

        usort($payments, static fn(array $left, array $right): int => strcmp($right['create_time'], $left['create_time']));

        $merchantName = trim((string) ($rows[0]['merchant_name'] ?? ''));

        if ($merchantName === '') {
            foreach ($rows as $row) {
                if ($row['merchant_name'] !== '') {
                    $merchantName = $row['merchant_name'];
                    break;
                }
            }
        }

        if ($merchantName === '') {
            $merchantName = 'Merchant';
        }

        return [
            'merchant_name' => $merchantName,
            'latest_amount' => $payments[0]['amount'] ?? 0,
            'payment_count' => count($payments),
            'total_amount' => array_sum(array_column($payments, 'amount')),
            'recent_transactions' => array_slice($payments, 0, 5),
        ];
    }

    /**
     * @return array{
     *     merchant_name: string,
     *     latest_amount: int,
     *     payment_count: int,
     *     total_amount: int,
     *     recent_transactions: array<int, array{
     *         type: string,
     *         amount: int,
     *         merchant_name: string,
     *         transaction_id: string,
     *         reference_id: string,
     *         create_time: string,
     *         update_time: string,
     *         payment_method: string,
     *     }>,
     * }
     */
    private static function emptySummary(): array
    {
        return [
            'merchant_name' => 'Merchant',
            'latest_amount' => 0,
            'payment_count' => 0,
            'total_amount' => 0,
            'recent_transactions' => [],
        ];
    }
}
