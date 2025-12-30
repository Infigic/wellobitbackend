<x-mail::message>
# 👋 Hello {{ $name }},

You're almost there!  
Use the OTP below to verify your email address:

<x-mail::panel>
## 🔐 {{ $otp }}
</x-mail::panel>

This OTP is valid for **60 minutes**.  
Please do not share it with anyone.

If you didn't request this, feel free to ignore this message.

Thanks,<br>
{{ config('app.name') }}

<x-mail::subcopy>
Need help? Reach us at support {{ Str::after(config('app.url'), '//') }}

</x-mail::subcopy>
</x-mail::message>
