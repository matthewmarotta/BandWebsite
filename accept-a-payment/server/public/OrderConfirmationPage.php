<?php
require_once 'shared.php';

try {
  $paymentIntent = $stripe->paymentIntents->create([
    'automatic_payment_methods' => ['enabled' => true],
    'amount' => 1999, //change to a value thats
    'currency' => 'aud',
  ]);
} catch (\Stripe\Exception\ApiErrorException $e) {
  http_response_code(400);
  error_log($e->getError()->message);
?>
  <h1>Error</h1>
  <p>Failed to create a PaymentIntent</p>
  <p>Please check the server logs for more information</p>
<?php
  exit;
} catch (Exception $e) {
  error_log($e);
  http_response_code(500);
  exit;
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <!--Dependencies for stripe-->
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://js.stripe.com/v3/"></script>     
    <script src="./utils.js"></script>  
    <script>
        /*
          function getFormItemsFromLocalStorage() {
        console.log("getFormItemsFromLocalStorage function called");
        console.log(localStorage.getItem('formItems'));
        var cartItems = JSON.parse(localStorage.getItem('formItems'));
         console.log(formItems.items); 
        if (formItems && formItems.expires > new Date().getTime()) {
            return formItems.items;
        } else {
            return [];
        }
    }*/
    
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

    function saveFormItemsToLocalStorage(formItems) {
        var expirationTime = new Date().getTime() + (24 * 60 * 60 * 1000); 
        localStorage.setItem('formItems', JSON.stringify({ items: formItems, expires: expirationTime }));
    }

    function handleInsufficientStock(message) {
        alert(message);
        console.log("cart not cleared");
    }
    </script>
    <!--<script>
      document.addEventListener('DOMContentLoaded', async () => {
        const stripe = Stripe('', {
          apiVersion: '2020-08-27',
        });

        const elements = stripe.elements({
          clientSecret: ''
        });
        const paymentElement = elements.create('payment');
        paymentElement.mount('#payment-element');

        const paymentForm = document.querySelector('#payment-form');
        paymentForm.addEventListener('submit', async (e) => { //addEventListener NULL
          // Avoid a full page POST request.
          e.preventDefault();

          // Disable the form from submitting twice.
          paymentForm.querySelector('button').disabled = true;

          // Confirm the card payment that was created server side:
          
          //processing
          const {error} = await stripe.confirmPayment({
            elements,
            confirmParams: {
              return_url: `${window.location.origin}/BandWebsite/accept-a-payment/server/public/return.php`
            }
          });
          //processing end

          if(error) {
            addMessage(error.message);

            // Re-enable the form so the customer can resubmit.
            paymentForm.querySelector('button').disabled = false;
            return;
          }
        });
      });
    </script>-->
    <!-- End of Dependenceis for stripe -->
     
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="/BandWebsite/CSS4SWWEBSITE2.css" type="text/css"> 
    <title>My website</title>

    <style>
        .invalid {
            border-color: red;
        }

        .valid {
            border-color: green;
        }

        .invalid-message {
            display: none;
        }
    </style>
</head>

<body>
   
    <div class="top-nav-bar-merchpage">
        <a href="http://localhost/BandWebsite/Merch.html">  
        <img class="nav-icon-merch-page" src="http://localhost/BandWebsite/Images/LOGO STREET WORMS - RESIZED (NAV-ICON).png">
        </a>
    </div>

    <section class="header">
      
    <div class="content">
        <div id="main-header" class="Merch-site-header">
        <h2>Order Confirmation Page</h2>
        </div>
        
        <h2>Order Summary</h2>
        <div id="cart-container">
        
        </div>
        <p class="total" id="total3">Total: 0.00</p>

        <a href="http://localhost/BandWebsite/Merch.html">  
        <button id="cancel-reservation">Cancel Order</button>
        </a>
        
        
       <img id="order-cancelled-image" src="http://localhost/BandWebsite/Images/face.jpg">
        <h2 id="order-cancelled-message">Your order has been cancelled, bye bye</h2>
        
    </div>
    
    <div class="form1">
    <h2 class="customer-info-heading">Customer Information</h2>
   
    <form action="#" method="post" class="form" id="payment-form">


        <label for="first_name">First Name:</label>
        <input type="text" id="first_name" name="first_name" autocomplete="given-name" pattern="[a-zA-Z]+" required><br><br>
        <p class="invalid-message" style="display: none;">Input is invalid, name must be alphabetic</p><br><br>
    
        <label for="last_name">Last Name:</label>
        <input type="text" id="last_name" name="last_name" autocomplete="family-name" required><br><br>
    
        
        <label for="street">Street Address:</label>
        <input type="text" id="street" name="street" required><br><br>
        
        <label for="city">City/Suburb:</label>
        <input type="text" id="city" name="city" required><br><br>
        
        <label for="state">State:</label>
        <select id="state" name="state" required>
            <option value="">Select State/Territory</option>
            <option value="NSW">New South Wales</option>
            <option value="VIC">Victoria</option>
            <option value="QLD">Queensland</option>
            <option value="WA">Western Australia</option>
            <option value="SA">South Australia</option>
            <option value="TAS">Tasmania</option>
            <option value="ACT">Australian Capital Territory</option>
            <option value="NT">Northern Territory</option>
            <option value="Others">Others</option>
        </select><br><br>
        
        <label for="mobile">Mobile Number:</label>
        <input type="tel" id="mobile" name="mobile" pattern="[0-9]{10}" placeholder="e.g., 0412345678" required><br><br>
        
        <label for="email">Email Address:</label>
       
        <input type="email" id="email" name="email"  pattern=".+\.com$" required title="Please enter a valid email address ending with '.com'"><br><br>
        <!--Payment form start-->
        
      <h2>Payment</h2>

        <label for="payment-element">Payment details</label>
        <div id="payment-element">
          <!-- Elements will create input elements here -->
        </div>

        <!-- We'll put the error messages in this element -->
        <div id="payment-errors" role="alert"></div>

        <button id="submit">Pay</button>

        <div id="messages" role="alert" style="display: none;"></div>
 
         <!--Payment form end
        <input id ="confirm-purchase" type="submit" value="Confirfm Order">--> <h2 id="confirm-purchase-message">Thankyou for your purchase! An order summary has been sent to your email</h2>
        
    </form>
    </div>
     
    </section>


   
    
    <div class="spacer"></div>
    
    <!--Script to display cart items in order summary-->
<script>
 $(document).ready(function() {
    
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
    
    // Function to display cart items
    function displayCartItems() {
        console.log("displayCartItems function called");
        
        
     
        var $cartContainer = $('#cart-container');

       
        $cartContainer.empty();

    var dataItems = getCartItemsFromLocalStorage() || [];
    
      console.log("the items are: ",dataItems);
     
      dataItems.forEach(function(item) {
            var $cartItem = $('<div class="cart-item">' +
                            '<img src="' + item.imageUrl + '" class="cart-image">' +
                            '<p class="descriptor">Name:</p>' +
                            '<p class="cart-item-name">' + item.itemName + '</p>' +
                            '<p class="cart-item-quantity">Quantity:</p>' +
                            '<p class="cart-item-quantity">' + item.quantity + '</p>' +
                        '</div>');
            $cartContainer.append($cartItem);
        });
}
displayCartItems();

 function calculateTotal(cartItems) {
        var total = 0;
        cartItems.forEach(function(item) {
            total += item.itemPrice;
        });
        return total;
    }

   
    function displayTotal(total) {
        $("#total3").text("Total: $" + total.toFixed(2));
    }

   
    var cartItems = getCartItemsFromLocalStorage() || [];

 
    var total = calculateTotal(cartItems);

    $.ajax({
            url: 'http://localhost/BandWebsite/accept-a-payment/server/public/return.php', 
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                total: total 
            }),
            success: function(response) {
                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                    } catch (e) {
                        console.error("Failed to parse response:", response);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to update total amount in payment intent', xhr.responseText);
            }
        });

 
    displayTotal(total);
   


//Script for cancelling an order and clearing cart via the cancel reservation button
$(document).on('click', '#cancel-reservation', function(event) {
        if (this.checkValidity()) {
            var cartItems = getCartItemsFromLocalStorage() || [];    
         } else {
                console.error('Cart items data is not an array or is invalid.');
            }
          
            $(".cart-grid").find('.cart-item').remove();
            $("#total1").text("Total: $0.00");
            $("#total2").text("$0.00");
            saveCartItemsToLocalStorage([]); 
            saveFormItemsToLocalStorage([]);
            updateCheckoutButton();
            document.getElementById("order-cancelled-message").style.display = "block"; 
            document.getElementById("order-cancelled-image").style.display = "block"; 
        })
});
</script>

<!--Function for Stripe Payment-->
<script>
      document.addEventListener('DOMContentLoaded', async () => {
        var cartItems = getCartItemsFromLocalStorage() || [];
        var quantityUpdateRequests = [];
        var insufficientStock = false;
        var insufficientStockMessage = "";
       
        const stripe = Stripe('<?= $_ENV["STRIPE_PUBLISHABLE_KEY"]; ?>', {
          apiVersion: '2020-08-27',
        });
       
        const elements = stripe.elements({
          clientSecret: '<?= $paymentIntent->client_secret; ?>'
        });
        
        const paymentElement = elements.create('payment');

        paymentElement.mount('#payment-element');

        const paymentForm = document.querySelector('#payment-form');

        paymentForm.addEventListener('submit', async (e) => { //addEventListener NULL
          // Avoid a full page POST request.
          e.preventDefault();
          // Disable the form from submitting twice.
          paymentForm.querySelector('button').disabled = true;
          // Check the database for quantity

    // First check availability for all items
    cartItems.forEach(function(cartItem) {
        var request = $.ajax({
            url: 'http://localhost/BandWebsite/CheckAvailability.php', 
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                itemName: cartItem.itemName,
                quantity: cartItem.quantity 
            }),
            success: function(response) {
                // Parse response if it's a string
                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                    } catch (e) {
                        console.error("Failed to parse response:", response);
                    }
                }
                
                // Check for insufficient stock message
                if (response && response.success === false && response.message && 
                    response.message.includes("Not enough available stock")) {
                    console.log("Insufficient stock detected:", response.message);
                    insufficientStock = true;
                    insufficientStockMessage = response.message;
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX request failed:', xhr.responseText);
                insufficientStock = true; // Consider any error as insufficient stock
            }
        });
        quantityUpdateRequests.push(request);
    });

        await $.when.apply($, quantityUpdateRequests);
    
        console.log("All availability checks completed, insufficientStock:", insufficientStock);
        
        if (insufficientStock) {
            // If any insufficient stock was found, show the message and stop
            handleInsufficientStock(insufficientStockMessage);
            return;
        }
        // Confirm the card payment that was created server side:
          //processing
          
          const {error} = await stripe.confirmPayment({
            elements,
            confirmParams: {
              return_url: `${window.location.origin}/BandWebsite/accept-a-payment/server/public/return.php`
            }
          });
          //processing end
          if(error) {
            addMessage(error.message);
            // Re-enable the form so the customer can resubmit.
            paymentForm.querySelector('button').disabled = false;
            return;
          }
        
    });  
});


    document.addEventListener('DOMContentLoaded', async () => {
        const paymentForm = document.querySelector('#payment-form');
       
        paymentForm.addEventListener('submit', (e) => {
        var formItems = [];
        var firstName = $("#first_name").val();
        var lastName = $("#last_name").val();
        var street = $("#street").val();
        var city = $("#city").val();
        var state = $("#state").val();
        var mobile = $("#mobile").val();
        var email = $("#email").val(); 
            
        formItems.push({
            firstName: firstName,
            lastName: lastName,
            street: street,
            city: city,
            state: state,
            mobile: mobile,
            email: email 
        });
        console.log("Save form items to local storage called");
        saveFormItemsToLocalStorage(formItems);
        });
    });
    </script>

<!--Script to validate form entry, providing live feedback-->
<script>
    
    function addInputListeners() {
        var inputs = document.querySelectorAll('input[type="text"], input[type="tel"], input[type="email"], select');
        
        inputs.forEach(function(input) {
        
            input.addEventListener('input', function(event) {
                var isValid = event.target.checkValidity(); 
                var errorMessage = event.target.parentElement.querySelector('.invalid-message'); 
                
                
                if (!isValid) {
                    event.target.classList.remove('valid');
                    event.target.classList.add('invalid');
                    errorMessage.style.display = 'block';
                } else {
                    event.target.classList.remove('invalid');
                    event.target.classList.add('valid');
                    errorMessage.style.display = 'none';
                }
            });
        });
    }
    
  
    window.addEventListener('DOMContentLoaded', addInputListeners);
</script>
<!--Script to delay the cancellation button from returning to homepage so you can see the cancellation message-->
<script>
    document.getElementById('cancel-reservation').addEventListener('click', function(event) {
        event.preventDefault();
        setTimeout(function() {
            window.location.href = 'http://localhost/BandWebsite/Merch.html';
        }, 2000);
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>