@php 
$paypal_mode = env('PAYPAL_MODE');
$base_paypal_sdk_url = $paypal_mode == 'sandbox' ?  'https://sandbox.paypal.com/' : 'https://www.paypal.com/';
$base_paypal_sdk_url = 'https://www.paypal.com/';
$client_id = env('PAYPAL_' . strtoupper($paypal_mode) . '_CLIENT_ID');
@endphp
<!DOCTYPE html>

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1"> <!-- Ensures optimal rendering on mobile devices. -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<style>
  #paypal-button-container {
    max-width: 600px;
    margin: 0 auto;
    position: absolute;
    top: 32%;
    width: calc(100% - 30px);
    left: 50%;
    transform: translateX(-50%);
  }    
  .main .container{
        max-width: 600px;
        margin: 0 auto;
        position: absolute;
        top: 20%;
        width: calc(100% - 30px);
        left: 50%;
        transform: translateX(-50%);
    }
    .main .container div{
     
      display: flex;
      align-items: center;
      gap: 2px;
      justify-content: center;
      margin-bottom: 10px;
    }
    .main .container div p{
      margin: 0px;
    }

</style>
<body>
  <input type="hidden" id="url" value="{{env('APP_URL')}}" >
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
  <script src="{{ $base_paypal_sdk_url }}sdk/js?client-id={{ $client_id }}&vault=true&intent=subscription">
  </script>
  <div class="main">
    <div class="container">
      <div>
        <strong>Your selected plan:</strong> <p>{{$paypal_plan->name}}</p>       
      </div>
      <div>
        <strong>Plan price:</strong> <p>{{$paypal_plan->price}}</p>
      </div>
    </div>
  </div>
  <div id="paypal-button-container"> </div>
  <script>
   
    paypal.Buttons({
      createSubscription: function(data, actions) {
        return actions.subscription.create({
          'plan_id': '{{ $paypal_plan->plan_id }}',
          'custom_id' : '{{ $user_id }}', // Creates the subscription
        });
      },
      onApprove: function(data, actions) {
        $.ajax({ 
		        	headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
		          	type: "POST",
		          	url: $('#url').val()+'subscription-order', 
		          	data: {
                  'subscription_plan_price': '{{ $paypal_plan->price }}',
                  'subscription_plan_name': '{{ $paypal_plan->name }}',
                  'subscription_plan_id': '{{ $paypal_plan->id }}',
                  'paypal_plan_id': '{{ $paypal_plan->plan_id }}',
                  'user_id' : '{{ $user_id }}',
                  'data': data,
                },   
		          	success: function(data)
		          	{
		            	if(data.success){
                    const queryString = `price=${data.price}`;
                    const subscription_plan_id = `subscription_plan_id=${data.subscription_plan_id}`;

                    var url = $('#url').val() + 'subscription-status?' + queryString + '&' + subscription_plan_id;
                    window.location.href = url
                  }
		          	}
		        });
        // alert('You have successfully subscribed to ' + data.subscriptionID); // Optional message given to subscriber
      }
    }).render('#paypal-button-container'); // Renders the PayPal button
  </script>
</body>

</html>