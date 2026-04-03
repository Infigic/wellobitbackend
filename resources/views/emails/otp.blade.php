<x-mail::message>

Hi {{ $name }},

You’re almost there — use the verification code below to confirm your email address.

<table role="presentation" cellpadding="0" cellspacing="0">
<tr>
<td style="padding:20px 0;">

<div style="
    display:inline-block;
    border-top:1px solid #6b7c93;
    border-bottom:1px solid #6b7c93;
    padding:12px 20px;
    font-size:28px;
    letter-spacing:10px;
    font-weight:600;
">
{{ $otp }}
</div>

</td>
</tr>
</table>

This code expires in 60 minutes.

🔒 Never share this code with anyone. Wellobit will never ask for it.

If you didn’t request this code, you can safely ignore this email.

Thanks,  
Wellobit Support Team

</x-mail::message>
