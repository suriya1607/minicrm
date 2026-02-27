<h3>Hello {{ $user->name }},</h3>

<p>
You have been invited to join <strong>{{ $appname }}</strong>.
</p>

<p>
Click the button below to set your password and activate your account.
</p>

<div style="text-align:center;margin:30px 0;">
    <a href="{{ $url }}"
       style="background:#2563eb;color:#ffffff;
              padding:12px 32px;
              border-radius:6px;
              text-decoration:none;
              font-weight:bold;">
        Set Your Password
    </a>
</div>

<p>
If you did not request this account, please ignore this email.
</p>

<p>
Regards,<br>
<strong>{{ $appname }} Team</strong>
</p>