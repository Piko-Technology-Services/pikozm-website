<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
     * @param  array{amount: float|string, company_ref: string, service_type: string, service_description: string, currency?: string, redirect_url?: string, back_url?: string, customer?: array{first_name?: string, last_name?: string, email?: string, phone?: string}}  $params
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
        $redirectUrl = $this->xml($params['redirect_url'] ?? ($this->config['redirect_url'] ?: route('payments.success')));
        $backUrl = $this->xml($params['back_url'] ?? ($this->config['back_url'] ?: route('payments.cancel')));
        $customer = $this->customerXml($params['customer'] ?? []);

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
        <PTL>5</PTL>{$customer}
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

    /**
     * Mobile money options DPO offers for a transaction (GetMobilePaymentOptions).
     *
     * @return array<int, array{name: string, country: string, prefix: string, instructions: string}>
     */
    public function mobilePaymentOptions(string $transactionToken): array
    {
        $token = $this->xml((string) $this->config['company_token']);
        $safeToken = $this->xml($transactionToken);

        $data = $this->send(<<<XML
<?xml version="1.0" encoding="utf-8"?>
<API3G>
    <CompanyToken>{$token}</CompanyToken>
    <Request>GetMobilePaymentOptions</Request>
    <TransactionToken>{$safeToken}</TransactionToken>
</API3G>
XML);

        if (! $data instanceof SimpleXMLElement || ! isset($data->paymentoptions->mobileoption)) {
            return [];
        }

        $options = [];
        foreach ($data->paymentoptions->mobileoption as $option) {
            $options[] = [
                'name' => trim((string) $option->paymentname),
                'country' => trim((string) $option->country),
                'prefix' => trim((string) ($option->celluarprefix ?? $option->cellularprefix)),
                'instructions' => trim(strip_tags((string) $option->instructions)),
            ];
        }

        return $options;
    }

    /**
     * Send a mobile money payment prompt to the customer's phone (ChargeTokenMobile).
     * Afterwards, poll verifyToken until the payment is approved or fails.
     *
     * @return array{success: bool, code: string, message: string, instructions: string}
     */
    public function chargeMobile(string $transactionToken, string $phoneNumber, string $mno, string $country): array
    {
        $token = $this->xml((string) $this->config['company_token']);
        $safeToken = $this->xml($transactionToken);
        $phone = $this->xml($phoneNumber);
        $mno = $this->xml($mno);
        $country = $this->xml($country);

        $data = $this->send(<<<XML
<?xml version="1.0" encoding="utf-8"?>
<API3G>
    <CompanyToken>{$token}</CompanyToken>
    <Request>ChargeTokenMobile</Request>
    <TransactionToken>{$safeToken}</TransactionToken>
    <PhoneNumber>{$phone}</PhoneNumber>
    <MNO>{$mno}</MNO>
    <MNOcountry>{$country}</MNOcountry>
</API3G>
XML);

        if (! $data instanceof SimpleXMLElement) {
            return ['success' => false, 'code' => '', 'message' => $data, 'instructions' => ''];
        }

        // DPO answers with StatusCode/Code (130 = prompt sent) or Result (000).
        $code = (string) ($data->StatusCode ?? '') ?: (string) ($data->Code ?? '') ?: (string) ($data->Result ?? '');
        $message = (string) ($data->ResultExplanation ?? '') ?: (string) ($data->Explanation ?? '');
        $instructions = trim(strip_tags(str_ireplace(['<br>', '<br/>', '<br />'], "\n", (string) ($data->instructions ?? ''))));

        return [
            'success' => in_array($code, ['130', '000'], true),
            'code' => $code,
            'message' => $message,
            'instructions' => $instructions,
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
            $response = $this->request()->withBody($xml, 'application/xml')->post($this->config['api_endpoint']);
        } catch (ConnectionException $e) {
            Log::warning('DPO Pay connection failed', ['error' => $e->getMessage()]);

            return 'Connection to DPO Pay failed: '.$e->getMessage();
        }

        $previous = libxml_use_internal_errors(true);
        $data = $response->successful() ? simplexml_load_string($response->body()) : false;
        libxml_use_internal_errors($previous);

        if ($data === false) {
            // Log enough to tell a firewall block from a DPO error page.
            Log::warning('DPO Pay returned an unexpected response', [
                'status' => $response->status(),
                'server' => $response->header('Server'),
                'cf_ray' => $response->header('CF-Ray') ?: null,
                'body' => Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($response->body()))), 400),
            ]);

            return match (true) {
                $response->status() === 403 => 'DPO Pay refused the request (HTTP 403). Their firewall blocked this server – run "php artisan dpo:check" for details',
                $response->status() >= 500 => 'DPO Pay is temporarily unavailable (HTTP '.$response->status().')',
                ! $response->successful() => 'DPO Pay rejected the request (HTTP '.$response->status().')',
                default => 'Invalid response received from DPO Pay',
            };
        }

        return $data;
    }

    /**
     * HTTP client for DPO. Sends a plain, identifiable User-Agent: DPO's firewall
     * has been seen to reject the default "GuzzleHttp/7" agent with HTTP 403.
     */
    public function request(): PendingRequest
    {
        return Http::timeout($this->config['timeout'] ?? 30)
            ->withUserAgent($this->config['user_agent'] ?? 'PikoPay/1.0 (+https://pikozm.com)')
            ->accept('application/xml');
    }

    /** Optional customer fields that pre-fill DPO's hosted checkout. */
    private function customerXml(array $customer): string
    {
        $map = [
            'first_name' => 'customerFirstName',
            'last_name' => 'customerLastName',
            'email' => 'customerEmail',
            'phone' => 'customerPhone',
            'dial_code' => 'customerDialCode',
            'country' => 'customerCountry',
        ];

        $xml = '';
        foreach ($map as $key => $tag) {
            if (filled($customer[$key] ?? null)) {
                $xml .= "\n        <{$tag}>".$this->xml((string) $customer[$key])."</{$tag}>";
            }
        }

        return $xml;
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
