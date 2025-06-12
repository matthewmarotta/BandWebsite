<?php
require_once 'shared.php';

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
        const userId = `user_${timestamp}_${randomString}`; 
        localStorage.setItem('userId', userId);
    }
  
function getUserId() {
    let userId = localStorage.getItem('userId');
    if (!userId) {
        userId = generateUniqueUserId();
        localStorage.setItem('userId', userId);
    }
    return userId;
    }

    function clearCartItems() {
    const userId = getUserId();
    
    $.ajax({
        url: 'http://localhost/BandWebsite/ClearCart.php',
        method: 'POST',
        contentType: 'application/json', 
        data: JSON.stringify({
            userId: userId,   
        }),
        success: function(response) {
            console.log("All items cleared from cart", response.message);
        },
        error: function(xhr) {
            console.error('AJAX request failed:', xhr.responseText);
        }
    });
    }

     function getCartItems() {
        return new Promise((resolve, reject) => {
        const userId = getUserId();
        
        $.ajax({
            url: 'http://localhost/BandWebsite/RetrieveFromCart.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                userId: userId
            }),
            success: function(response) {
                console.log("Cart data retrieved:", response);

                const cartItems = response.cartItems;
               
                const transformedItems = cartItems.map(item => {
                    return {
                        imageUrl: item.Image_Url,           
                        itemName: item.Name,               
                        itemPrice: parseFloat(item.Price),  
                        quantity: item.Quantity            
                    };
                });

                console.log("Transformed items:", transformedItems);

                resolve(transformedItems); 
            },
            error: function(xhr) {
                console.error('AJAX request failed:', xhr.responseText);
                reject(xhr.responseText);
            }
        });
    });
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
      <a href="http://localhost/BandWebsite/Merch.html">home</a>
      <h1>Payment Status</h1>

      <h3>PaymentIntent</h3>
      <p>ID <?= $paymentIntent->id; ?></p>
      <p>Status: <?= $paymentIntent->status; ?></p>
      <p>Amount: $<?= $paymentIntent->amount*0.01; ?></p>
      <p>Currency: <?= $paymentIntent->currency; ?></p>
      <p>Payment Method: <?= $paymentIntent->payment_method; ?></p>
      <h2 id="confirm-purchase-message">Thankyou for your purchase! An order summary has been sent to your email</h2>
      
    </main>
  </body>
</html>

<script>
document.addEventListener('DOMContentLoaded', async () => {
            let userId = getUserId() || [];
            //var cartItems = getCartItemsFromLocalStorage() || [];
            let cartItems = [];

            const date = new Date().toISOString();

            const paymentIntentData = {
                id: '<?= $paymentIntent->id; ?>',
                status: '<?= $paymentIntent->status; ?>',
                amount: <?= $paymentIntent->amount; ?>,
                currency: '<?= $paymentIntent->currency; ?>',
                payment_method: '<?= $paymentIntent->payment_method; ?>'
            };

            getCartItems().then(items => {
                cartItems = items || [];
            });

          //  var formItems = getFormItemsFromLocalStorage() || [];
            
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
                updateQuantity(cartItems,response.orderId, userId, date, paymentIntentData);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX request failed:', xhr.responseText);
        }
    }); 
    });

    function updateQuantity(cartItems, orderId, userId, date, paymentIntentData) {        
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
            processItemsWithOrderId(cartItems, orderId, userId, date, paymentIntentData);
        });
    }
   
function processItemsWithOrderId(cartItems, orderId, userId, date, paymentIntentData) {
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

            $.ajax({
                url: 'http://localhost/BandWebsite/UpdatePaymentInfo.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    id: paymentIntentData.id,
                    orderId: orderId, 
                    date: date, 
                    payment_method: paymentIntentData.payment_method, 
                    status: paymentIntentData.status,
                    currency: paymentIntentData.currency,
                    amount: paymentIntentData.amount
                }),
                success: function(response) {
                    console.log("payment info sent successfully:", response);
                },
                error: function(xhr, status, error) {
                    console.error('payment info sending failed:', xhr.responseText);
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
        clearCartItems();
      //  saveFormItemsToLocalStorage([]);
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
