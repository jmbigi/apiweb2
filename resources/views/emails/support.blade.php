<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Support Request</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 2rem;
            background-color: #000026; /* Blue background color */
            color: white; /* White text color */
        }

        h2 {
            margin-bottom: 1rem;
            color: inherit; /* Inherit white text color */
        }

        p {
            margin-bottom: 0.5rem;
        }

        strong {
            font-weight: bold;
        }

        .support-request {
            padding: 1rem;
            border: 2px solid #007bff;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="support-request">
        <h2>New Support Request</h2>
        
        <p><strong>Name:</strong> {{ $data['name'] }}</p>
        <p><strong>Email:</strong> {{ $data['email'] }}</p>
        <p><strong>Message:</strong> {{ $data['message'] }}</p>
    </div>
</body>
</html>
