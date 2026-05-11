<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Training Status Alert</title>
    </head>
    <body style="font-family: Arial, sans-serif; background-color:#f5f5f5; padding:20px;">

        <div style="max-width:600px; margin:auto; background:#ffffff; padding:20px; border-radius:8px;">
            
            <h2 style="color:#333;">Training Status Alert</h2>

            @php
                $label = $status === 'lapsed' ? 'Lapsed' : 'Expiring Soon';
            @endphp

            @if($type === 'user')
                <p>Hello {{ $user->fname }} {{ $user->lname }},</p>

                <p>
                    Your <strong>Training Teach</strong> status is:
                    <strong style="color:red;">{{ $label }}</strong>.
                </p>

                <p>
                    Your current training validation date is:
                    @if($training)
                        <strong style="color:red;">{{ \Carbon\Carbon::parse($training->validation_date)->format('d M Y') }}</strong>
                    @else
                        <strong style="color:red;">N/A</strong>
                    @endif
                </p>

                <p>
                    Please take the necessary action to complete or renew your training.
                </p>

            @else
                <p>Hello Admin,</p>

                <p>
                    User <strong>{{ $user->fname }} {{ $user->lname }}</strong> ({{ $user->email }}) has a
                    <strong>Training Teach</strong> status of:
                    <strong style="color:red;">{{ $label }}</strong>.
                </p>

                <p>
                    Their current training validation date is:
                    @if($training)
                        <strong style="color:red;">{{ \Carbon\Carbon::parse($training->validation_date)->format('d M Y') }}</strong>
                    @else
                        <strong style="color:red;">N/A</strong>
                    @endif
                </p>

                <p>
                    Please review and take appropriate action if required.
                </p>
            @endif

            <hr style="margin:20px 0;">
        </div>

    </body>
</html>
