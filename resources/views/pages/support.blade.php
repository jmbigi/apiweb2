<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 2rem;
            background-color: #000026; /* Blue background color */
            color: white; /* White text color */
        }

        h1, h2 {
            margin: 1rem 0;
            color: inherit; /* Inherit white text color */
        }

        .contact-info {
            margin-bottom: 1.5rem;
        }

        .contact-info p {
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: inherit; /* Inherit white text color */
        }

        .contact-methods {
            margin-top: 1rem;
        }

        .contact-form {
            margin-top: 2rem;
        }

        .contact-form input[type="text"],
        .contact-form input[type="email"],
        .contact-form textarea {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .contact-form input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .contact-form input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .message-sent {
            background-color: #4CAF50; /* Green */
            color: white;
            padding: 10px;
            margin-top: 1rem;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>Welcome to the {{ config('app.name') }} Support Page</h1>
    <h2>Contact Us</h2>

    <div class="contact-info">
        <p>Owner of the APP: BIBLIOSCORES S.L. (hereinafter referred to as BIBLIOSCORES).</p>
        <p>VAT Number (CIF): B13684584</p>
        <p>Address: Calle los Almendros, 11 – 1 D, Calp, 03710, Alicante</p>
        <p>Phone: 604464537</p>
        <p>Contact Email: f.bolo@biblioscores.com</p>
    </div>

    <p>If you can't find the answer to your question on this support page, feel free to contact us using the form below:</p>

    <form class="contact-form" action="{{ route('send-email-support-en') }}" method="GET">
        @csrf
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="message">Message:</label>
        <textarea id="message" name="message" rows="4" required></textarea>

        <input type="submit" value="Send">
    </form>

    @if(session('success'))
        <div class="message-sent">
            {{ session('success') }}
        </div>
    @endif
</body>
</html>
