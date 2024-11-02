
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <title>Payment Success</title>
    <!-- Add any CSS styles or meta tags here -->
</head>
<style>
    .main .container{
        max-width: 600px;
        margin: 0 auto;
        position: absolute;
        top: 3%;
        width: calc(100% - 30px);
        left: 50%;
        transform: translateX(-50%);
    }
    .main .container div{
        text-align: center;
    }
</style>
<body>
    <div class="main">
        @if(isset($price))
            <div class="container">
                <div>
                    <img src="{{ asset('media/logos/logo-dark.svg') }}" class="max-h-100px" alt="" />
                </div>
                <div>
                    <p><strong> Selected plan : </strong>{{ $subscription_plan->name }}</p>
                </div>
                <div>
                    <p><strong>Plan description : </strong>{{ $subscription_plan->description }}</p>
                </div>
                <div>
                    <p><strong>Plan price : </strong>{{ $price }}</p>
                </div>
                <div>
                    <h1>Payment Successful</h1>
                </div>
                <div>
                    <p>Thank you for your payment. Your transaction was successful.</p>
                </div>
            </div>
        @else
            <div class="container">
                <h1>Payment failed</h1>
                <p>Please try again. Your transaction was failed.</p>        
            </div>
        @endif
    </div>
</body>
</html>