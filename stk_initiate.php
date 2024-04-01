<?php
if(isset($_POST['submit'])){

// Capture the cart details and username from the POST data
$_SESSION['cart_items'] = json_decode($_POST['items'], true);
$_SESSION['username'] = $_POST['username'];
$_SESSION['total_amount'] = $_POST['amount'];

  date_default_timezone_set('Africa/Nairobi');

  # access token
  $consumerKey = 'yXXSNGKMgycy63JpUhqc1vu05ZKloyDNg4AwcePcvE3PXkGH'; //Fill with your app Consumer Key
  $consumerSecret = 'evwEatnrHch9AFChWenoesAiFQkaQMRUWSZ5R2WDIilHGnFA3qn8cAZkriVgBhFL'; // Fill with your app Secret

  # define the variales
  # provide the following details, this part is found on your test credentials on the developer account
  $BusinessShortCode = '174379';
  $Passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';  
  
  /*
    This are your info, for
    $PartyA should be the ACTUAL clients phone number or your phone number, format 2547********
    $AccountRefference, it maybe invoice number, account number etc on production systems, but for test just put anything
    TransactionDesc can be anything, probably a better description of or the transaction
    $Amount this is the total invoiced amount, Any amount here will be 
    actually deducted from a clients side/your test phone number once the PIN has been entered to authorize the transaction. 
    for developer/test accounts, this money will be reversed automatically by midnight.
  */
  
   $PartyA = $_POST['phone']; // This is your phone number, 
  $AccountReference = '2255';
  $TransactionDesc = 'Test Payment';
  $Amount = $_POST['amount'];;
 
  # Get the timestamp, format YYYYmmddhms -> 20181004151020
  $Timestamp = date('YmdHis');    
  
  # Get the base64 encoded string -> $password. The passkey is the M-PESA Public Key
  $Password = base64_encode($BusinessShortCode.$Passkey.$Timestamp);

  # header for access token
  $headers = ['Content-Type:application/json; charset=utf8'];

    # M-PESA endpoint urls
  $access_token_url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
  $initiate_url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

  # callback url
  $CallBackURL = 'https://secret-plains-70423-283428ea4e96.herokuapp.com/callback_url.php';  

  $curl = curl_init($access_token_url);
  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($curl, CURLOPT_HEADER, FALSE);
  curl_setopt($curl, CURLOPT_USERPWD, $consumerKey.':'.$consumerSecret);
  $result = curl_exec($curl);
  $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  $result = json_decode($result);
  $access_token = $result->access_token;  
  curl_close($curl);

  # header for stk push
  $stkheader = ['Content-Type:application/json','Authorization:Bearer '.$access_token];

  # initiating the transaction
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $initiate_url);
  curl_setopt($curl, CURLOPT_HTTPHEADER, $stkheader); //setting custom header

  $curl_post_data = array(
    //Fill in the request parameters with valid values
    'BusinessShortCode' => $BusinessShortCode,
    'Password' => $Password,
    'Timestamp' => $Timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => $Amount,
    'PartyA' => $PartyA,
    'PartyB' => $BusinessShortCode,
    'PhoneNumber' => $PartyA,
    'CallBackURL' => $CallBackURL,
    'AccountReference' => $AccountReference,
    'TransactionDesc' => $TransactionDesc
  );

  $data_string = json_encode($curl_post_data);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
  $curl_response = curl_exec($curl);

  if (curl_errno($curl)) {
    // Handle CURL error
    echo "<p style='color: red;'>Error: " . curl_error($curl) . "</p>";
} else {
    $response = json_decode($curl_response, true);
    if ($response && $response['ResponseCode'] == "0") {
        // Display the receipt details with styling
        echo "<div style='font-family: Arial, sans-serif; margin: 20px; padding: 20px; border: 1px solid #ddd;'>";
        echo "<h1 style='color: green;'>Payment Receipt</h1>";
        echo "<h3 style='color: brown;'>GREENHOUSE ECOMMERCE</h3>";
        echo "<p><strong>Dear " . htmlspecialchars($_SESSION['username']) . ", your purchase details are as follows:</strong></p>";
        echo "<p><strong>Transaction successful!</strong></p>";
        echo "<p>Merchant Request ID: " . htmlspecialchars($response['MerchantRequestID']) . "</p>";
        echo "<p>Checkout Request ID: " . htmlspecialchars($response['CheckoutRequestID']) . "</p>";
        echo "<p>Total Amount: " . htmlspecialchars($_SESSION['total_amount']) . "</p>";
        echo "<p>Items and their Prices:</p>";
        echo "<ul>";
        foreach ($_SESSION['cart_items'] as $item) {
            echo "<li>" . htmlspecialchars($item['name']) . " ---- " . htmlspecialchars($item['price']) . "</li>";
        }
        echo "</ul>";
        echo "<p><strong>After you have made the payment, we will verify the payment from our end and then deliver the goods in 3-6 business days</strong></p>";
        echo "<h3>Thank you for shopping with us!</h3>";
        echo "</div>";
    } else {
        // Handle case where response is not successful
        echo "<p style='color: red;'>Transaction failed or response is not in the expected format.</p>";
        echo "<p>Response Description: " . htmlspecialchars($response['ResponseDescription']) . "</p>";
        echo "<p>Customer Message: " . htmlspecialchars($response['CustomerMessage']) . "</p>";
    }
}
curl_close($curl);
 // print_r($curl_response);

 // echo $curl_response;
};

