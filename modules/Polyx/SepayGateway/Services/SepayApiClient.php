<?php

namespace Modules\Polyx\SepayGateway\Services;

class SepayApiClient
{
    protected const QR_BASE_URL = 'https://qr.sepay.vn/img';

    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Generate QR code URL for payment.
     *
     * @param string $bankCode Bank code (ACB, VCB, BIDV, MB, etc.)
     * @param string $accountNumber Bank account number
     * @param float $amount Amount in VND
     * @param string $description Transfer description (order number)
     * @return string QR code image URL
     */
    public function generateQrUrl(
        string $bankCode,
        string $accountNumber,
        float $amount,
        string $description
    ): string {
        $params = [
            'bank' => $bankCode,
            'acc' => $accountNumber,
            'amount' => (int) $amount,
            'des' => $description,
        ];

        return self::QR_BASE_URL . '?' . http_build_query($params);
    }

    /**
     * Get list of supported banks.
     */
    public function getSupportedBanks(): array
    {
        return [
            ['code' => 'ACB', 'name' => 'Asia Commercial Bank'],
            ['code' => 'VCB', 'name' => 'Vietcombank'],
            ['code' => 'BIDV', 'name' => 'Bank for Investment and Development of Vietnam'],
            ['code' => 'MB', 'name' => 'Military Commercial Joint Stock Bank'],
            ['code' => 'TCB', 'name' => 'Techcombank'],
            ['code' => 'VPB', 'name' => 'Vietnam Prosperity Bank'],
            ['code' => 'TPB', 'name' => 'Tien Phong Bank'],
            ['code' => 'STB', 'name' => 'Sacombank'],
            ['code' => 'VIB', 'name' => 'Vietnam International Bank'],
            ['code' => 'SHB', 'name' => 'Saigon-Hanoi Commercial Bank'],
            ['code' => 'MSB', 'name' => 'Maritime Bank'],
            ['code' => 'EIB', 'name' => 'Eximbank'],
            ['code' => 'LPB', 'name' => 'LienVietPostBank'],
            ['code' => 'OCB', 'name' => 'Orient Commercial Bank'],
            ['code' => 'HDB', 'name' => 'HD Bank'],
            ['code' => 'NAB', 'name' => 'Nam A Bank'],
            ['code' => 'ABB', 'name' => 'An Binh Bank'],
            ['code' => 'SCB', 'name' => 'Saigon Commercial Bank'],
            ['code' => 'BAB', 'name' => 'Bac A Bank'],
            ['code' => 'VAB', 'name' => 'Viet A Bank'],
        ];
    }
}
