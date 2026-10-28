<?php

namespace App\Console\Commands;

use App\Services\DpoPay;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Diagnose connectivity to the DPO Pay API.
 *
 *   php artisan dpo:check
 *
 * Sends a harmless verifyToken request for a made-up token, once with this app's
 * User-Agent and once with the default Guzzle one, and shows this machine's public IP.
 * A healthy connection returns XML (usually a "transaction not found" style result).
 */
class DpoCheck extends Command
{
    protected $signature = 'dpo:check';

    protected $description = 'Check that this server can reach the DPO Pay API';

    public function handle(DpoPay $dpo): int
    {
        $config = config('dpo');
        $this->line('Endpoint:      '.$config['api_endpoint']);
        $this->line('Company token: '.(filled($config['company_token']) ? substr($config['company_token'], 0, 6).'… (set)' : 'MISSING'));

        try {
            $this->line('Public IP:     '.trim(Http::timeout(10)->get('https://api.ipify.org')->body()));
        } catch (Throwable) {
            $this->line('Public IP:     (could not look up)');
        }

        $xml = '<?xml version="1.0" encoding="utf-8"?><API3G><CompanyToken>'.e($config['company_token']).'</CompanyToken>'
            .'<Request>verifyToken</Request><TransactionToken>00000000-0000-0000-0000-000000000000</TransactionToken></API3G>';

        $ok = false;
        foreach ([
            'App User-Agent ('.$config['user_agent'].')' => $dpo->request(),
            'Default Guzzle User-Agent' => Http::timeout(30),
        ] as $label => $client) {
            $this->newLine();
            $this->info($label);

            try {
                $res = $client->withBody($xml, 'application/xml')->post($config['api_endpoint']);
            } catch (Throwable $e) {
                $this->error('  Connection failed: '.$e->getMessage());
                continue;
            }

            $isXml = str_contains($res->body(), '<API3G');
            $this->line('  HTTP status: '.$res->status().($isXml ? '  (DPO answered)' : ''));
            $this->line('  Server:      '.($res->header('Server') ?: '-'));
            if ($res->header('CF-Ray')) {
                $this->line('  CF-Ray:      '.$res->header('CF-Ray').'  (Cloudflare firewall in front of DPO)');
            }
            $this->line('  Body:        '.mb_strimwidth(trim(preg_replace('/\s+/', ' ', strip_tags($res->body()))), 0, 300, '…'));
            if ($label !== 'Default Guzzle User-Agent' && $isXml) {
                $ok = true;
                $result = $this->resultCode($res->body());
            }
        }

        $this->newLine();
        if (! $ok) {
            $this->warn('DPO did not accept the request from this machine.');
            $this->line('If both attempts show HTTP 403, DPO is blocking this IP address. Send the public IP above to DPO support');
            $this->line('(support@directpay.online) and ask them to allow it, or test from your live server instead of localhost.');

            return self::FAILURE;
        }

        $accountStatus = $this->explain($result ?? ['', '']);

        return $this->probePaymentRequest($dpo) === self::SUCCESS ? $accountStatus : self::FAILURE;
    }

    /**
     * Send real createToken requests (K1.00, never charged) in three variations to find
     * what, if anything, DPO's CloudFront firewall blocks in the payment request body.
     */
    private function probePaymentRequest(DpoPay $dpo): int
    {
        $this->newLine();
        $this->info('Payment request test (createToken for K1.00 – creates an unpaid test token, nothing is charged)');

        $appComplete = $dpo->callbackUrl('zampayroll.payment.complete');
        $appCancel = $dpo->callbackUrl('zampayroll.payment.cancelled');
        $base = [
            'amount' => 1,
            'company_ref' => 'DPOCHECK-'.now()->format('His'),
            'service_type' => config('zampayroll.dpo_service_type'),
            'service_description' => 'ZamPayroll Tikula subscription (Monthly) - Connection test',
        ];
        $customer = ['first_name' => 'Test', 'last_name' => 'Customer', 'email' => config('zampayroll.admin_email'),
            'phone' => '970000000', 'dial_code' => 'ZM', 'country' => 'ZM'];

        $variants = [
            'A. As the app sends it' => $base + ['redirect_url' => $appComplete, 'back_url' => $appCancel, 'customer' => $customer],
            'B. Public https return URLs' => $base + ['redirect_url' => 'https://pikozm.com/zampayroll-subscriptions/payment/complete',
                'back_url' => 'https://pikozm.com/zampayroll-subscriptions/payment/cancelled', 'customer' => $customer],
            'C. Public URLs, no customer details' => $base + ['redirect_url' => 'https://pikozm.com/zampayroll-subscriptions/payment/complete',
                'back_url' => 'https://pikozm.com/zampayroll-subscriptions/payment/cancelled'],
        ];

        $this->line('  Return URL used by the app: '.$appComplete);

        $blocked = [];
        foreach ($variants as $label => $params) {
            try {
                $res = $dpo->request()->withBody($dpo->createTokenXml($params), 'application/xml')->post(config('dpo.api_endpoint'));
            } catch (Throwable $e) {
                $this->error("  {$label}: connection failed – ".$e->getMessage());
                $blocked[$label] = true;
                continue;
            }

            if (str_contains($res->body(), '<API3G')) {
                [$code, $text] = $this->resultCode($res->body());
                $this->line("  {$label}: passed the firewall · DPO result {$code} {$text}");
                $blocked[$label] = false;
            } else {
                $this->error("  {$label}: BLOCKED (HTTP {$res->status()}, ".($res->header('Server') ?: 'unknown server').')');
                $blocked[$label] = true;
            }
        }

        $this->newLine();
        [$a, $b, $c] = array_values($blocked);

        if (! $a) {
            $this->info('The payment request passes DPO\'s firewall.');

            return self::SUCCESS;
        }

        if (! $b) {
            $this->error('DPO\'s firewall blocks the app\'s return URLs ('.$appComplete.').');
            $this->line('Fix: set DPO_CALLBACK_BASE_URL in .env to a public https address, then run "php artisan config:clear".');
            $this->line('  On the live server: DPO_CALLBACK_BASE_URL=https://pikozm.com');
            $this->line('  On your Mac: start a tunnel (e.g. "ngrok http 8000") and use its https URL, so DPO can send you back here.');
        } elseif (! $c) {
            $this->error('DPO\'s firewall blocks the customer details in the request.');
            $this->line('Send this output to DPO support (support@directpay.online) so they can adjust their firewall rules.');
        } else {
            $this->error('DPO\'s firewall blocks every payment request from this machine.');
            $this->line('Send this output and your public IP to DPO support (support@directpay.online), or test from the live server.');
        }

        return self::FAILURE;
    }

    /** @return array{0: string, 1: string} [code, explanation] from DPO's XML reply */
    private function resultCode(string $body): array
    {
        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body);
        libxml_use_internal_errors($previous);

        return $xml === false ? ['', ''] : [trim((string) $xml->Result), trim((string) $xml->ResultExplanation)];
    }

    /**
     * Codes 801–804 mean DPO rejected the account or request itself. Anything else
     * (e.g. "transaction not found" for our made-up token) means the account is accepted.
     */
    private function explain(array $result): int
    {
        [$code, $text] = $result;
        $this->line("DPO result: {$code} {$text}");

        $problem = match ($code) {
            '801' => 'The company token is missing. Set DPO_COMPANY_TOKEN in .env.',
            '802' => 'DPO does not accept this company token yet: the account is not active (or the token is wrong). '
                .'Ask DPO support (support@directpay.online) to activate your live account. '
                .'Until then, card and mobile money payments will fail. To test, use DPO\'s test token with service type 85325.',
            '803', '804' => 'DPO could not read the request. Check DPO_API_ENDPOINT in .env.',
            default => null,
        };

        if ($problem) {
            $this->error('DPO is reachable, but payments will not work yet.');
            $this->line($problem);

            return self::FAILURE;
        }

        $this->info('DPO is reachable and accepts your company token. Payments should work from this machine.');

        return self::SUCCESS;
    }
}
