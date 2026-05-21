<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BakerDan Order Update</title>
</head>
<body style="margin: 0; padding: 0; background: #fcf9f2; color: #1c1c18; font-family: Arial, Helvetica, sans-serif;">
    <div style="display: none; max-height: 0; overflow: hidden; opacity: 0;">
        Your BakerDan order #{{ $summary['order_id'] }} is now on the way.
    </div>

    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background: #fcf9f2; padding: 48px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="width: 100%; max-width: 640px; background: #fffdf8; border: 1px solid #dac1ba; border-radius: 12px; overflow: hidden; box-shadow: 0 18px 48px rgba(49, 49, 44, 0.08);">
                    <tr>
                        <td align="center" style="padding: 40px 32px 30px; border-bottom: 1px solid #eadbd6;">
                            <div style="font-family: Georgia, 'Times New Roman', serif; font-size: 30px; line-height: 1.2; font-weight: 700; letter-spacing: 8px; color: #1c1c18;">
                                BAKERDAN
                            </div>
                            <div style="margin-top: 10px; font-size: 12px; line-height: 1.4; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; color: #c1694f;">
                                Wholesale Bakery
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 42px 40px 24px;">
                            <h1 style="margin: 0 0 14px; font-family: Georgia, 'Times New Roman', serif; font-size: 28px; line-height: 1.25; font-weight: 600; color: #1c1c18;">
                                Your order is on the way.
                            </h1>

                            <p style="margin: 0 0 26px; font-size: 16px; line-height: 1.7; color: #55433e;">
                                Hi {{ $summary['customer_name'] }}, order #{{ $summary['order_id'] }} has been marked as shipped by BakerDan. Here is the confirmed order summary.
                            </p>

                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 26px; background: #f6f3ec; border: 1px solid #dac1ba; border-radius: 10px;">
                                <tr>
                                    <td style="padding: 24px 24px 22px;">
                                        <div style="margin-bottom: 12px; font-size: 12px; line-height: 1.4; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #c1694f;">
                                            Shipment Update
                                        </div>
                                        <div style="font-family: Georgia, 'Times New Roman', serif; font-size: 34px; line-height: 1.2; font-weight: 700; color: #1c1c18;">
                                            Order #{{ $summary['order_id'] }}
                                        </div>
                                        <div style="margin-top: 8px; font-size: 14px; line-height: 1.7; color: #55433e;">
                                            {{ $summary['item_count'] }} item{{ $summary['item_count'] === 1 ? '' : 's' }} &bull; {{ $summary['payment_method'] }} &bull; {{ $summary['payment_status'] }}
                                        </div>
                                        @if (! empty($summary['placed_at']))
                                            <div style="margin-top: 4px; font-size: 13px; line-height: 1.7; color: #87726d;">
                                                Placed {{ $summary['placed_at'] }}
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <div style="margin-bottom: 16px; font-size: 12px; line-height: 1.4; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #c1694f;">
                                Order Summary
                            </div>

                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
                                @foreach ($summary['items'] as $item)
                                    <tr>
                                        <td style="padding: 16px 0; border-bottom: 1px solid #eadbd6; vertical-align: top;">
                                            <div style="font-size: 15px; line-height: 1.5; font-weight: 700; color: #1c1c18;">
                                                {{ $item['name'] }}
                                            </div>
                                            <div style="margin-top: 4px; font-size: 13px; line-height: 1.6; color: #55433e;">
                                                Qty {{ $item['quantity'] }} &times; {{ $item['unit_price_label'] }}
                                                @if (! empty($item['details']))
                                                    <br>{{ $item['details'] }}
                                                @endif
                                            </div>
                                        </td>
                                        <td align="right" style="padding: 16px 0 16px 16px; border-bottom: 1px solid #eadbd6; vertical-align: top; white-space: nowrap; font-size: 15px; line-height: 1.5; font-weight: 700; color: #1c1c18;">
                                            {{ $item['line_total_label'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </table>

                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 22px; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 5px 0; font-size: 14px; line-height: 1.6; color: #55433e;">Subtotal</td>
                                    <td align="right" style="padding: 5px 0; font-size: 14px; line-height: 1.6; color: #1c1c18;">{{ $summary['subtotal_label'] }}</td>
                                </tr>
                                @if (($summary['discount'] ?? 0) > 0)
                                    <tr>
                                        <td style="padding: 5px 0; font-size: 14px; line-height: 1.6; color: #55433e;">
                                            Discount
                                            @if (! empty($summary['promo_code']))
                                                ({{ $summary['promo_code'] }})
                                            @endif
                                        </td>
                                        <td align="right" style="padding: 5px 0; font-size: 14px; line-height: 1.6; color: #93452e;">- {{ $summary['discount_label'] }}</td>
                                    </tr>
                                @endif
                                @if (($summary['shipping'] ?? 0) > 0)
                                    <tr>
                                        <td style="padding: 5px 0; font-size: 14px; line-height: 1.6; color: #55433e;">Shipping</td>
                                        <td align="right" style="padding: 5px 0; font-size: 14px; line-height: 1.6; color: #1c1c18;">{{ $summary['shipping_label'] }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="padding: 16px 0 0; border-top: 1px solid #dac1ba; font-size: 16px; line-height: 1.6; font-weight: 700; color: #1c1c18;">Total</td>
                                    <td align="right" style="padding: 16px 0 0; border-top: 1px solid #dac1ba; font-family: Georgia, 'Times New Roman', serif; font-size: 24px; line-height: 1.2; font-weight: 700; color: #93452e;">{{ $summary['total_label'] }}</td>
                                </tr>
                            </table>

                            @if (! empty($summary['shipping_address']))
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 28px; background: #ffffff; border: 1px solid #eadbd6; border-radius: 10px;">
                                    <tr>
                                        <td style="padding: 18px 20px;">
                                            <div style="margin-bottom: 6px; font-size: 12px; line-height: 1.4; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #c1694f;">
                                                Delivery Details
                                            </div>
                                            <div style="font-size: 14px; line-height: 1.7; color: #55433e;">
                                                {{ $summary['shipping_address'] }}
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding: 30px 40px; border-top: 1px solid #eadbd6; background: #ffffff;">
                            <p style="margin: 0 0 12px; font-size: 14px; line-height: 1.7; color: #55433e;">
                                <strong style="font-family: Georgia, 'Times New Roman', serif; color: #1c1c18;">BakerDan Wholesale Bakery</strong><br>
                                Thank you for ordering from us. We packed your baked goods with care.
                            </p>
                            <p style="margin: 0; font-size: 12px; line-height: 1.6; color: #87726d;">
                                Questions? Contact us at
                                <a href="mailto:{{ config('mail.from.address') }}" style="color: #93452e; text-decoration: none;">{{ config('mail.from.address') }}</a>
                            </p>
                        </td>
                    </tr>
                </table>

                <div style="width: 100%; max-width: 640px; margin-top: 24px; font-size: 12px; line-height: 1.6; color: #87726d; text-align: center;">
                    &copy; {{ now()->year }} BakerDan. All rights reserved.
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
