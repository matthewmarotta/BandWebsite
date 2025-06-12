<?php
require_once __DIR__ . '/accept-a-payment/server/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/accept-a-payment/server');
$dotenv->load();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!--Dependencies for stripe-->
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://js.stripe.com/v3/"></script>     
    <script src="http://localhost/BandWebsite/accept-a-payment/server/public/utils.js"></script>  
    <script>
    function getUserId() {
    let userId = localStorage.getItem('userId');
    if (!userId) {
        userId = generateUniqueUserId();
        localStorage.setItem('userId', userId);
    }
    return userId;
    }
    
    function getCartTotal() {
        return new Promise((resolve, reject) => {
        const userId = getUserId();
        
        $.ajax({
            url: 'http://localhost/BandWebsite/RetrieveCartTotal.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                userId: userId
            }),
            success: function(response) {
                console.log("Cart total retrieved:", response);
               
                const total = parseFloat(response.total || 0);   
                console.log("Total: ", total); 
                resolve(total);
            },
            error: function(xhr) {
                console.error('AJAX request failed:', xhr.responseText);
                reject(xhr.responseText);
            }
        });
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
  
    function saveFormItems(formItems) {
        const userId = getUserId();
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
    }




    function handleInsufficientStock(message) {
        alert(message);
        console.log("cart not cleared");
    }
    </script>
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
  
        
      <h2>Payment</h2>
        <label for="payment-element">Payment details</label>
        <div id="payment-element">
         
        </div>
     
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
        var totalAmountText = $("#total3").text();
        var totalAmount = parseFloat(totalAmountText.replace('Total: $', '').replace('$', '')).toFixed(2);
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
    
    // Function to calculate total 
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
    
    // Function to display cart items 
    function displayCartItems(cartItems) {
        console.log("displayCartItems function called with items:", cartItems);
        
        var $cartContainer = $('#cart-container');
        $cartContainer.empty();
        
        if (!cartItems || cartItems.length === 0) {
            $cartContainer.append('<p>Your cart is empty</p>');
            displayTotal(0);
            return;
        }
     
        cartItems.forEach(function(item) {
            var $cartItem = $('<div class="cart-item">' +
                            '<img src="' + item.imageUrl + '" class="cart-image">' +
                            '<p class="descriptor">Name:</p>' +
                            '<p class="cart-item-name">' + item.itemName + '</p>' +
                            '<p class="cart-item-price">Price: $' + item.itemPrice.toFixed(2) + '</p>' +
                            '<p class="cart-item-quantity">Quantity: ' + item.quantity + '</p>' +
                        '</div>');
            $cartContainer.append($cartItem);
        });
        
        // Calculate and display total
        var total = calculateTotal(cartItems);
        displayTotal(total);
    }
    
    // Get cart items and display them 
    getCartItems().then(function(cartItems) {
        console.log("Cart items retrieved:", cartItems);
        displayCartItems(cartItems);
        updateCheckoutButton();
    }).catch(function(error) {
        console.error("Error retrieving cart items:", error);
        displayCartItems([]); 
    });
    
    $(document).on('click', '#cancel-reservation', function(event) {
        event.preventDefault();
        
        getCartItems().then(function(cartItems) {
            console.log("Cancelling order for items:", cartItems);
            
            // Clear the display
            $("#cart-container").empty();
            $("#total3").text("Total: $0.00");
            
            // Clear server-side cart
            clearCartItems(); 
           // saveFormItemsToLocalStorage([]);
            updateCheckoutButton();
            
            // Show cancellation message
            document.getElementById("order-cancelled-message").style.display = "block"; 
            document.getElementById("order-cancelled-image").style.display = "block"; 
            
        }).catch(function(error) {
            console.error('Error during cancellation:', error);
        });
    });
});
</script>
<!--Function for Stripe Payment-->
<script>

document.addEventListener('DOMContentLoaded', async () => {
    try {
        const userId = await getUserId();
        const total = await getCartTotal();
        console.log("Cart total for payment:", total);
        
        if (total <= 0) {
            console.log("Empty Cart");
            return;
        }
        
        const paymentIntentResponse = await fetch('http://localhost/BandWebsite/accept-a-payment/server/public/create_payment_intent.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                total: total,
                userId: userId
            })
        });
        
        if (!paymentIntentResponse.ok) {
            throw new Error('Failed to create payment intent');
        }
        
        const paymentData = await paymentIntentResponse.json();
        console.log("Payment intent created:", paymentData);
        
        const stripe = Stripe('<?php echo $_ENV["STRIPE_PUBLISHABLE_KEY"]; ?>', {
            apiVersion: '2020-08-27',
        });
        
        const elements = stripe.elements({
            clientSecret: paymentData.client_secret
        });
        
        const paymentElement = elements.create('payment');
        paymentElement.mount('#payment-element');
        
        const paymentForm = document.querySelector('#payment-form');
        paymentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            paymentForm.querySelector('button').disabled = true;
            
            try {
                const cartItems = await getCartItems();
                console.log("Processing payment for items:", cartItems);
                
                var quantityUpdateRequests = [];
                var insufficientStock = false;
                var insufficientStockMessage = "";
                
            
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
                            if (typeof response === 'string') {
                                try {
                                    response = JSON.parse(response);
                                } catch (e) {
                                    console.error("Failed to parse response:", response);
                                }
                            }
                            
                            if (response && response.success === false && response.message && 
                                response.message.includes("Not enough available stock")) {
                                console.log("Insufficient stock detected:", response.message);
                                insufficientStock = true;
                                insufficientStockMessage = response.message;
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX request failed:', xhr.responseText);
                            insufficientStock = true;
                        }
                    });
                    quantityUpdateRequests.push(request);
                });
                
                await $.when.apply($, quantityUpdateRequests);
                
                if (insufficientStock) {
                    handleInsufficientStock(insufficientStockMessage);
                    paymentForm.querySelector('button').disabled = false;
                    return;
                }
                
                // Confirm the payment
                const {error} = await stripe.confirmPayment({
                    elements,
                    confirmParams: {
                        return_url: `${window.location.origin}/BandWebsite/accept-a-payment/server/public/return.php`
                    }
                }); 
                
                if(error) {
                    console.error('Payment error:', error.message);
                    paymentForm.querySelector('button').disabled = false;
                    return;
                }
                
            } catch (error) {
                console.error("Error processing payment:", error);
                paymentForm.querySelector('button').disabled = false;
            }
        });
        
        // Form data collection
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
           // saveFormItemsToLocalStorage(formItems);
              saveFormItems(formItems);
        });
        
    } catch (error) {
        console.error('Error initializing payment system:', error);
        const paymentElement = document.getElementById('payment-element');
        if (paymentElement) {
            paymentElement.innerHTML = '<p style="color: red;">Error loading payment system. Please refresh the page.</p>';
        }
    }
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
                    if (errorMessage) errorMessage.style.display = 'block';
                } else {
                    event.target.classList.remove('invalid');
                    event.target.classList.add('valid');
                    if (errorMessage) errorMessage.style.display = 'none';
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