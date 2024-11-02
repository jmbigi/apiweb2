@component('mail::message')
<h1>Hello!</h1>
<p>You are receiving this email because we received a forgot password request for your account.</p>
    {{ $mail_details['body'] }}
@endcomponent
