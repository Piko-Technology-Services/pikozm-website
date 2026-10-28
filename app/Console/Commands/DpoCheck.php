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
            $this->line('  HTTP status: '.$res->status().($isXml ? '  ✔ DPO answered with XML' : ''));
            $this->line('  Server:      '.($res->header('Server') ?: '-'));
            if ($res->header('CF-Ray')) {
                $this->line('  CF-Ray:      '.$res->header('CF-Ray').'  (Cloudflare firewall in front of DPO)');
            }
            $this->line('  Body:        '.mb_strimwidth(trim(preg_replace('/\s+/', ' ', strip_tags($res->body()))), 0, 300, '…'));
            $ok = $ok || ($label !== 'Default Guzzle User-Agent' && $isXml);
        }

        $this->newLine();
        if ($ok) {
            $this->info('DPO is reachable. Payments should work from this machine.');

            return self::SUCCESS;
        }

        $this->warn('DPO did not accept the request from this machine.');
        $this->line('If both attempts show HTTP 403, DPO is blocking this IP address. Send the public IP above to DPO support');
        $this->line('(support@directpay.online) and ask them to allow it, or test from your live server instead of localhost.');

        return self::FAILURE;
    }
}
