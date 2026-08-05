<?php

namespace App\Transformers;

/**
 * Transform checkout order records into API-friendly payloads.
 */
class CheckoutOrderResource
{
    /**
     * @param array<string, mixed> $order
     * @param array<string, mixed> $context
     */
    public static function make(array $order, array $context): array
    {
        $method = $order['metode_pembayaran'] ?? 'Direct Bank Transfer';

        $payment = [
            'method'         => $method,
            'deadline'       => $order['batas_waktu_pembayaran'] ?? null,
            'bank_transfer'  => null,
            'qris'           => null,
        ];

        if ($method === 'QRIS') {
            $payment['qris'] = self::buildQrisPayload($context['qris'] ?? []);
        } else {
            $payment['bank_transfer'] = self::buildBankTransferPayload($context['bank'] ?? []);
        }

        return [
            'status'       => $context['status'] ?? 'success',
            'order_id'     => $order['order_id'] ?? null,
            'total_amount' => (float) ($order['total_harga'] ?? 0),
            'payment'      => $payment,
        ];
    }

    /**
     * @param array<string, mixed> $bank
     * @return array<string, mixed>
     */
    private static function buildBankTransferPayload(array $bank): array
    {
        $accounts = array_map(
            static function (array $account): array {
                return [
                    'bank_name'      => $account['bank_name'] ?? null,
                    'account_number' => $account['account_number'] ?? null,
                    'account_holder' => $account['account_holder'] ?? null,
                    'branch'         => $account['branch'] ?? null,
                ];
            },
            $bank['accounts'] ?? []
        );

        return [
            'instructions'     => $bank['instructions'] ?? null,
            'upload_proof_url' => $bank['upload_proof_url'] ?? null,
            'accounts'         => $accounts,
        ];
    }

    /**
     * @param array<string, mixed> $qris
     * @return array<string, mixed>
     */
    private static function buildQrisPayload(array $qris): array
    {
        return [
            'image_url'        => $qris['image_url'] ?? null,
            'instructions'     => $qris['instructions'] ?? null,
            'upload_proof_url' => $qris['upload_proof_url'] ?? null,
        ];
    }
}
