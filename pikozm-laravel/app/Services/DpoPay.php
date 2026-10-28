<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

/**
 * Client for the DPO Pay (3G Direct Pay) XML API v6.
 * Ported from payments/includes/dpo.php.
 */
class DpoPay
{
    public function __construct(private readonly array $config)
    {
    }

    /**
     * Create a payment token (createToken).
     *
     * @param  array{amount: float|string, company_ref: string, service_type: string, service_description: string, currency?: string}  $params
     * @return array{success: bool, token?: string, trans_ref?: string, message?: string}
     */
    public function createToken(array $params): array
    {
        $amount = number_format((float) $params['amount'], 2, '.', '');
        $currency = $this->xml($params['currency'] ?? $this->config['currency']);
        $companyRef = $this->xml($params['company_ref']);
        $serviceType = $this->xml($params['service_type']);
        $serviceDescription = $this->xml($params['service_description']);
        $serviceDate = now()->format('Y/m/d H:i');
        $token = $this->xml((string) $this->config['company_token']);
        $redirectUrl = $this->xml($this->config['redirect_url'] ?: route('payments.success'));
        $backUrl = $this->xml($this->config['back_url'] ?: route('payments.cancel'));

        $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<API3G>
    <CompanyToken>{$token}</CompanyToken>
    <Request>createToken</Request>
    <Transaction>
        <PaymentAmount>{$amount}</PaymentAmount>
        <PaymentCurrency>{$currency}</PaymentCurrency>
        <CompanyRef>{$companyRef}</CompanyRef>
        <RedirectURL>{$redirectUrl}</RedirectURL>
        <BackURL>{$backUrl}</BackURL>
        <CompanyRefUnique>0</CompanyRefUnique>
        <PTL>5</PTL>
    </Transaction>
    <Services>
        <Service>
            <ServiceType>{$serviceType}</ServiceType>
            <ServiceDescription>{$serviceDescription}</ServiceDescription>
            <ServiceDate>{$serviceDate}</ServiceDate>
        </Service>
    </Services>
</API3G>
XML;

        $data = $this->send($xml);

        if (! $data instanceof SimpleXMLElement) {
            return ['success' => false, 'message' => $data];
        }

        if ((string) $data->Result === '000') {
            return [
                'success' => true,
                'token' => (string) $data->TransToken,
                'trans_ref' => (string) $data->TransRef,
            ];
        }

        return [
            'success' => false,
            'message' => (string) $data->ResultExplanation ?: 'Unable to create payment token.',
        ];
    }

    /**
     * Verify whether a transaction token has been paid (verifyToken).
     *
     * @return array{success: bool, result_code: string, message: string}
     */
    public function verifyToken(string $transactionToken): array
    {
        $token = $this->xml((string) $this->config['company_token']);
        $safeToken = $this->xml($transactionToken);

        $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<API3G>
    <CompanyToken>{$token}</CompanyToken>
    <Request>verifyToken</Request>
    <TransactionToken>{$safeToken}</TransactionToken>
</API3G>
XML;

        $data = $this->send($xml);

        if (! $data instanceof SimpleXMLElement) {
            return ['success' => false, 'result_code' => '', 'message' => $data];
        }

        $result = (string) $data->Result;

        return [
            'success' => $result === '000',
            'result_code' => $result,
            'message' => (string) $data->ResultExplanation,
        ];
    }

    /** URL the customer is redirected to in order to pay. */
    public function paymentUrl(string $transactionToken): string
    {
        return $this->config['payment_url'].urlencode($transactionToken);
    }

    /**
     * POST the XML and parse the response.
     *
     * @return SimpleXMLElement|string Parsed XML, or an error message.
     */
    private function send(string $xml): SimpleXMLElement|string
    {
        try {
            $response = Http::timeout($this->config['timeout'] ?? 30)
                ->withBody($xml, 'application/xml')
                ->post($this->config['api_endpoint']);
        } catch (ConnectionException $e) {
            Log::warning('DPO Pay connection failed', ['error' => $e->getMessage()]);

            return 'Connection to DPO Pay failed: '.$e->getMessage();
        }

        $previous = libxml_use_internal_errors(true);
        $data = simplexml_load_string($response->body());
        libxml_use_internal_errors($previous);

        if ($data === false) {
            Log::warning('DPO Pay returned invalid XML', ['status' => $response->status()]);

            return 'Invalid response received from DPO Pay.';
        }

        return $data;
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
