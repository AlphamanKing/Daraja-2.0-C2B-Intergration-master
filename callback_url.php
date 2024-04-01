<?php
 		header("Content-Type: application/json");

     $response = '{
         "ResultCode": 0, 
         "ResultDesc": "Confirmation Received Successfully"
     }';
 
     // DATA
     $mpesaResponse = file_get_contents('php://input');
 
     // log the response
     $logFile = "M_PESAConfirmationResponse.txt";
 
     // write to file
     $log = fopen($logFile, "a");
 
     fwrite($log, $mpesaResponse);
     fclose($log);
 
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
    // echo $response;
