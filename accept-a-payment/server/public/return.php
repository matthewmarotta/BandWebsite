<?php
require_once 'shared.php';

// Returning after redirecting to a payment method portal.
$paymentIntent = $stripe->paymentIntents->retrieve(
   $_GET['payment_intent'],
);
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>

function updateCheckoutButton() {
        var totalAmountText = $("#total2").text();
        var totalAmount = parseFloat(totalAmountText.replace('$', '')).toFixed(2);
        if (totalAmount <= 0.00) {
            $("#checkout-button").removeClass("enabled").addClass("disabled");
            var emptyStatusElement = document.getElementById("cart-empty-status");
            if (emptyStatusElement) {
                emptyStatusElement.style.display = "block";
            }
        } else {
            $("#checkout-button").removeClass("disabled").addClass("enabled");
            var emptyStatusElement = document.getElementById("cart-empty-status");
            if (emptyStatusElement) {
                emptyStatusElement.style.display = "none";
            }
        }
    }

function generateUniqueUserId() {
        const timestamp = new Date().getTime();
        const randomString = Math.random().toString(36).substring(2, 15);
        return `user_${timestamp}_${randomString}`;
    }
  
function getUserId() {
    //let userId = JSON.parse(localStorage.getItem('userId'));
    let userId = localStorage.getItem('userId');
    if (!userId) {
        userId = generateUniqueUserId();
        localStorage.setItem('userId', userId);
    }
    return userId;
    }

    function saveCartItemsToLocalStorage(cartItems) {
        var expirationTime = new Date().getTime() + (24 * 60 * 60 * 1000);
        localStorage.setItem('cartItems', JSON.stringify({ items: cartItems, expires: expirationTime }));
    }

    function saveFormItemsToLocalStorage(formItems) {
        var expirationTime = new Date().getTime() + (24 * 60 * 60 * 1000); 
        localStorage.setItem('formItems', JSON.stringify({ items: formItems, expires: expirationTime }));
    }
    
    function getFormItemsFromLocalStorage() {
        console.log("getFormItemsFromLocalStorage function called");
        console.log(localStorage.getItem('formItems'));
        var formItems = JSON.parse(localStorage.getItem('formItems'));
         console.log(formItems.items); 
        if (formItems && formItems.expires > new Date().getTime()) {
            return formItems.items;
        } else {
            return [];
        }
    }

    function getCartItemsFromLocalStorage() {
        console.log("getCartItemsFromLocalStorage function called");
        console.log(localStorage.getItem('cartItems'));
        var cartItems = JSON.parse(localStorage.getItem('cartItems'));
         console.log(cartItems.items); 
        if (cartItems && cartItems.expires > new Date().getTime()) {
            return cartItems.items;
        } else {
            return [];
        }
    }
</script>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>PaymentIntent</title>
    <link rel="stylesheet" href="css/base.css" />
    <script src="https://js.stripe.com/v3/"></script>
  </head>
  <body>
    <main>
      <a href="/">home</a>
      <h1>Payment Status</h1>

      <h3>PaymentIntent</h3>
      <p><a href="https://dashboard.stripe.com/test/payments/<?= $paymentIntent->id; ?>" target="_blank">Dashboard</a></p>
      <p>ID <?= $paymentIntent->id; ?></p>
      <p>Status: <?= $paymentIntent->status; ?></p>
      <p>Amount: <?= $paymentIntent->amount; ?></p>
      <p>Currency: <?= $paymentIntent->currency; ?></p>
      <p>Payment Method: <?= $paymentIntent->payment_method; ?></p>
      <a href='/'>Restart demo</a>

      <h2 id="confirm-purchase-message">Thankyou for your purchase! An order summary has been sent to your email</h2>
    </main>
  </body>
</html>

<script>
document.addEventListener('DOMContentLoaded', async () => {
            let userId = getUserId() || [];
            var cartItems = getCartItemsFromLocalStorage() || [];
            var formItems = getFormItemsFromLocalStorage() || [];
            
            console.log("this is the user id in form: " + userId);
            $.ajax({
                url: 'http://localhost/BandWebsite/create_orderID.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                action: 'create_orderID'
                }),
            success: function(response) {
                if (response && response.success && response.orderId && response.message.includes("Order created successfully")) {
                console.log("Order created");          
                updateQuantity(cartItems,response.orderId, userId);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX request failed:', xhr.responseText);
        }
    }); 
    formItems.forEach(function(formItem) { 
            console.log(formItem);    
            console.log("ID IN HERE IS ", userId);   
            $.ajax({
                         
                url: 'http://localhost/BandWebsite/UpdateUserInformation.php',
                method: 'POST',
                contentType: 'application/json',
            
                data: JSON.stringify({
                    userId: userId,
                    firstName: formItem.firstName,
                    lastName: formItem.lastName,
                    street: formItem.street,
                    city: formItem.city,
                    state: formItem.state,
                    mobile: formItem.mobile,
                    email: formItem.email
                }),
                success: function(response) {
                    if (response && response.success) {
                console.log("User Info Updated");
            }
            },
            error: function(xhr, status, error) {
            console.error('AJAX request failed:', xhr.responseText);
            }    
        });
    });
    });

    function updateQuantity(cartItems, orderId, userId) {        
        var updateRequests = [];
        
        cartItems.forEach(function(cartItem) {
            var updateRequest = $.ajax({
                url: 'http://localhost/BandWebsite/UpdateQuantity.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    itemName: cartItem.itemName,
                    quantity: cartItem.quantity 
                }),
                success: function(response) {
                    console.log("Quantity Updated for:", cartItem.itemName);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX request failed:', xhr.responseText);
                }
            });
            updateRequests.push(updateRequest);
        });
        
        // Only process the order after all quantities are updated
        $.when.apply($, updateRequests).then(function() {
            console.log("Process items called - all quantities updated successfully");
            processItemsWithOrderId(cartItems, orderId, userId);
        });
    }
   
function processItemsWithOrderId(cartItems, orderId, userId) {
    // Create array to track all AJAX requests
    var ajaxRequests = [];
   
    cartItems.forEach(function(cartItem) {
        
        var request = $.ajax({
            url: 'http://localhost/BandWebsite/insert.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                itemName: cartItem.itemName,
                itemPrice: cartItem.itemPrice,
                quantity: cartItem.quantity,
                orderId: orderId
            }),
            success: function(response) {
              
                if (response && response.success) {  
                    console.log("Success:", response.message);
                    console.log("Order ID:", response.orderId);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX request failed:', xhr.responseText);
                console.log("failed to update order information table");
            }
        });
        
        ajaxRequests.push(request);
    });
    
    // Wait for all AJAX requests to complete before clearing cart
    $.when.apply($, ajaxRequests).done(function() {
        console.log("All items processed with Order ID: " + orderId);
        console.log("this is the user id in process items: " + userId);
        
        $.ajax({
        url: 'http://localhost/BandWebsite/UpdateOrderRecord.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
        orderId: orderId,
            userId: userId
        }),
        success: function(response) {
            console.log(response);
            
            // Add email sending AJAX call here
            $.ajax({
                url: 'http://localhost/BandWebsite/SendOrderEmail.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    orderId: orderId,
                    userId: userId
                }),
                success: function(emailResponse) {
                    console.log("Email sent successfully:", emailResponse);
                },
                error: function(xhr, status, error) {
                    console.error('Email sending failed:', xhr.responseText);
                }
            });
        },
        error: function(xhr, status, error) {
            console.error('AJAX request failed:', xhr.responseText);
        }
        });
        
        // Clear cart only after all requests complete
        $(".cart-grid").find('.cart-item').remove();
        $("#total1").text("Total: $0.00");
        $("#total2").text("$0.00");
        console.log("cart cleared");
        saveCartItemsToLocalStorage([]);
        saveFormItemsToLocalStorage([]);
        updateCheckoutButton();
        
        setTimeout(function() {
            window.scrollTo({
                top: document.documentElement.scrollHeight,
                behavior: 'smooth'
            });
        }, 200);
        
        document.getElementById("confirm-purchase-message").style.display = "block";
      });
  }
  </script>
