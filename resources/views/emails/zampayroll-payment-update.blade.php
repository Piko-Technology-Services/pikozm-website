@php
    $s = $subscription;
    $headline = match ($event) {
        'paid' => ['Payment received', '#198A00'],
        'cancelled' => ['Payment cancelled', '#EF7D00'],
        'manual' => ['Manual payment requested', '#0A3B22'],
        default => ['Payment failed', '#DE2010'],
    };
    $rows = [
        'Reference' => $s->reference,
        'Plan' => $s->plan_name.' · '.$s->billingLabel(),
        'Amount' => $s->formattedAmount().' '.$s->currency,
        'Payment method' => $s->methodLabel(),
        'Mobile number' => $s->mobile_number ? \App\Support\ZambianPhone::display($s->mobile_number) : null,
        'Attempt' => $s->payment_method === 'mobile_money' ? (string) $s->attempts : null,
        'Gateway response' => $s->last_message ? $s->last_message.($s->last_result_code ? " (code {$s->last_result_code})" : '') : null,
        'DPO token' => $s->dpo_token,
        'Company' => $s->company_name,
        'Contact' => $s->contact_name,
        'Email' => $s->email,
        'Phone' => $s->phone,
        'Employees' => $s->employees ? (string) $s->employees : null,
        'Time' => now()->timezone('Africa/Lusaka')->format('j M Y, H:i').' (Lusaka)',
    ];
@endphp
<!doctype html>
<html lang="en">
<body style="margin:0;padding:24px;background:#F3F6F2;font-family:Arial,Helvetica,sans-serif;color:#13231A;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:10px;overflow:hidden;">
        <tr>
            <td style="background:#0A3B22;padding:20px 24px;">
                <span style="color:#ffffff;font-size:18px;font-weight:bold;">ZamPayroll subscriptions</span>
            </td>
        </tr>
        <tr>
            <td style="height:6px;line-height:6px;font-size:0;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
                    <td style="background:#198A00;height:6px;"></td><td style="background:#DE2010;width:15%;"></td><td style="background:#000000;width:15%;"></td><td style="background:#EF7D00;width:15%;"></td>
                </tr></table>
            </td>
        </tr>
        <tr>
            <td style="padding:24px;">
                <h1 style="margin:0 0 6px;font-size:22px;color:{{ $headline[1] }};">{{ $headline[0] }}</h1>
                <p style="margin:0 0 20px;color:#4A5B50;">{{ $s->company_name }} · {{ $s->plan_name }} ({{ $s->billingLabel() }})</p>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                    @foreach ($rows as $label => $value)
                        @if (filled($value))
                            <tr>
                                <td style="padding:7px 0;border-bottom:1px solid #E4EBE5;color:#4A5B50;width:40%;vertical-align:top;">{{ $label }}</td>
                                <td style="padding:7px 0;border-bottom:1px solid #E4EBE5;font-weight:bold;">{{ $value }}</td>
                            </tr>
                        @endif
                    @endforeach
                </table>
                @if ($event === 'manual')
                    <p style="margin:20px 0 0;color:#4A5B50;">The customer chose manual payment and was sent to the ZamPayroll sign-in page. Follow up with an invoice or bank details.</p>
                @elseif ($event !== 'paid')
                    <p style="margin:20px 0 0;color:#4A5B50;">The customer can retry from the checkout page. Reply to this email to contact them directly.</p>
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
