
function clearGrid() {
    // Reset all grid items
    for (var i = 1; i <= 6; i++) {
        $("#grid-image" + i).attr("src", ""); 
        $("#item-description" + i).html(""); 
        $("#item-price" + i).html(""); 
        $("#item-availability" + i).html("");
    }
    console.log("Grid Cleared");
}

function clearemptygridItem() {
    var gridItems = document.querySelectorAll(".grid-item");
 // Loop through the NodeList
    gridItems.forEach(function(item) {
    console.log(item);
    description = item.querySelector(".item-description");
    
   console.log("Description element:", description); 
   console.log("Description:", description ? description.innerHTML : "No description found");
    if (description.innerHTML.trim() === "") {
        console.log(item.id + " is empty");
        item.style.display = "none"; 
    } else {
        item.style.display = "";
    }
    }); 
}


$(document).ready(function() {
    $(".slide-image").click(function() {
        console.log("Clear Grid Called"); 
        clearGrid();
       
        var category = $(this).attr("id");
        console.log("Category ID:", category);
        
        $.get("http://localhost/BandWebsite/PopulateGriditems2.php?category=" + category, function(data){
            var items = JSON.parse(data);
            
            for (var i = 0; i < items.length; i++) {
               
                $("#grid-image" + (i + 1)).attr("src", items[i].Image_URL);
                $("#category-header").html("<h2>" + items[i].Category_Name + "</h2>");
                $("#item-description" + (i + 1)).html("<p>" + items[i].Name + "</p>");
                $("#item-price" + (i + 1)).html("<p>" + "$" + items[i].Price + "</p>");
                $("#item-availability" + (i + 1)).html(function() {    
                    if(items[i].Availability == 1) {
                        return  '<button class="add-to-cart-button-Available">Add to cart</button>';
                        
                    } else {
                        return '<button class="add-to-cart-button-Unavailable">Add to cart</button>';
                    } 
                    
                    });
            }
            clearemptygridItem();  
        });
        
    });
});


$(document).ready(function() {
    $(".fa-search").click(function() {
        console.log("search button clicked");
        var query = document.getElementById('form').value;
       
        clearGrid();
        $.get("http://localhost/BandWebsite/PopulateGriditemswithsearch.php?query=" + query, function(data){
            console.log("THIS IS THE DATA!2" + data);
            var items = JSON.parse(data);
            console.log("THIS IS THE DATA3!" + data);
            for (var i = 0; i < items.length; i++) {
                $("#grid-image" + (i + 1)).attr("src", items[i].Image_URL);
                $("#category-header").html("<h2>Results</h2>");
                $("#item-description" + (i + 1)).html("<p>" + items[i].Name + "</p>");
                $("#item-price" + (i + 1)).html("<p class='item-price'>" + "$" + items[i].Price + "</p>");
                $("#item-availability" + (i + 1)).html(function() {    
                    if(items[i].Availability == 1) {
                        return  '<button class="add-to-cart-button-Available">Add to cart</button>';
                    } else {
                        return  '<button class="add-to-cart-button-Unavailable">Add to cart</button>';
                    } 
                    });
            }  
            console.log("THIS IS THE DATA!4" + data);
            clearemptygridItem();  
        });
    });
});

$(document).ready(function() {
    

    $(".carousel-description").click(function() {
        console.log("carousel link clicked");
        var query = $(this).text();
        clearGrid();
        $.get("http://localhost/BandWebsite/PopulateGriditemswithsearch.php?query=" + query, function(data){
        
            var items = JSON.parse(data);
            for (var i = 0; i < items.length; i++) {
                $("#grid-image" + (i + 1)).attr("src", items[i].Image_URL);
                $("#category-header").html("<h2>Results</h2>");
                $("#item-description" + (i + 1)).html("<p>" + items[i].Name + "</p>");
                $("#item-price" + (i + 1)).html("<p class='item-price'>" + "$" + items[i].Price + "</p>");
                $("#item-availability" + (i + 1)).html(function() {    
                    if(items[i].Availability == 1) {
                        return '<button class="add-to-cart-button-Available">Add to cart</button>';
                    } else {
                        return '<button class="add-to-cart-button-Unavailable">Add to cart</button>';
                    } 
                    });
            }
            clearemptygridItem();  
        });
    });
});

var totalAmount1 = 0.00;
var totalAmount2 = 0.00;

$(document).ready(function() {

    function getUserId() {
    let userId = localStorage.getItem('userId');
    if (!userId) {
        userId = generateUniqueUserId();
        localStorage.setItem('userId', userId);
    }
    return userId;
    }

    
function saveCartItems(cartItems) {
    const userId = getUserId();
    const expirationTime = new Date().getTime() + (24 * 60 * 60 * 1000);
    
    $.ajax({
        url: 'http://localhost/BandWebsite/UpdateCart.php',
        method: 'POST',
        contentType: 'application/json', 
        data: JSON.stringify({
            userId: userId,   
            expirationTime: expirationTime,
            cartItems: cartItems, 
        }),
        success: function(response) {
            console.log("All items updated in cart", response.message);
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
                setTimeout(updateCheckoutButton, 300);
                console.log("Update checkout button called in ajax");
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


    function updateCartUI(cartItems) {
        $(".cart-item").remove();
        totalAmount1 = 0.00;
        totalAmount2 = 0.00;
        cartItems.forEach(function(item) {
            var $newCartItem = $('<div class="cart-item">' +
            '<img src="' + item.imageUrl + '" class="cart-image">' +
             '<p class="descriptor">Name:</p>' +
             '<p class="cart-item-name">' + item.itemName + '</p>' +
            '<p class="descriptor">Price:</p>' +
            '<p class="cart-item-price">$' +  item.itemPrice.toFixed(2) + '</p>' + 
            '<p class="descriptor">Quantity:</p>' +
            '<p class="cart-item-quantity">' + item.quantity + '</p>' +
            '<i class="fa fa-minus remove-item"></i>' +
            '<i class="fa fa-plus add-item"></i>' +
        '</div>');
            $(".cart-grid").prepend($newCartItem);
    
            totalAmount1 += item.itemPrice;
            totalAmount2 += item.itemPrice;
        });

        $("#total1").text("Total: $" + totalAmount1.toFixed(2));

    }

    let cartItems = [];

    getCartItems().then(items => {
        cartItems = items || [];
        updateCartUI(cartItems);
    });

    function updateCheckoutButton() {
            var totalAmountText = $("#total1").text();  
            var amountMatch = totalAmountText.match(/[\d\.]+/);
            var totalAmount = amountMatch ? parseFloat(amountMatch[0]).toFixed(2) : 0.00;

            console.log("Parsed Total:", totalAmount);

            if (totalAmount <= 0) {
                $("#checkout-button").removeClass("enabled").addClass("disabled");
                document.getElementById("cart-empty-status").style.display = "block";
            } else {
                $("#checkout-button").removeClass("disabled").addClass("enabled");
                document.getElementById("cart-empty-status").style.display = "none";
            }
        }

   
        
 

    $(document).on("click", ".add-to-cart-button-Available", function() {
        var $gridItem = $(this).closest(".grid-item");
        var imageUrl = $gridItem.find(".grid-image").attr("src");
        var itemName = $gridItem.find(".item-description").text();
        var itemPrice = parseFloat($gridItem.find(".item-price").text().replace("$", ""));

        var existingItem = cartItems.find(item => item.itemName === itemName);
        if (existingItem) {  
            existingItem.quantity++;
            existingItem.itemPrice += itemPrice;
        } else {
            cartItems.push({
                imageUrl: imageUrl,
                itemName: itemName,
                itemPrice: itemPrice,
                quantity: 1,
            });
        }
        saveCartItems(cartItems);
        updateCartUI(cartItems);
        updateCheckoutButton();
    });

    $(".cart-grid").on("click", ".remove-item", function() {
        var $cartItem = $(this).closest(".cart-item");
        var $quantityElement = $cartItem.find('.cart-item-quantity');
        var quantity = parseInt($quantityElement.text());
        var itemPrice = parseFloat($cartItem.find(".cart-item-price").text().replace("$", "")) / quantity;

        if (quantity > 0) {  
            quantity -= 1;
            $quantityElement.text(quantity);

            var totalPrice = itemPrice * quantity;
            $cartItem.find('.cart-item-price').text("$" + totalPrice.toFixed(2));

            totalAmount1 -= itemPrice;
            totalAmount2 -= itemPrice;
            $("#total1").text("Total: $" + totalAmount1.toFixed(2));
       

            var itemName = $cartItem.find('.cart-item-name').text();
            var itemIndex = cartItems.findIndex(item => item.itemName === itemName);
            if (itemIndex !== -1) {
                cartItems[itemIndex].quantity = quantity;
                cartItems[itemIndex].itemPrice = totalPrice;
                saveCartItems(cartItems);
            }
        }

        if (quantity == 0) {
            $cartItem.remove();
            var itemName = $cartItem.find('.cart-item-name').text();
            cartItems = cartItems.filter(item => item.itemName !== itemName);
            saveCartItems(cartItems);
            updateCheckoutButton();
        }
    });

    $(".cart-grid").on("click", ".add-item", function() {
        var $cartItem = $(this).closest(".cart-item");
        var $quantityElement = $cartItem.find('.cart-item-quantity');
        var quantity = parseInt($quantityElement.text());
        var itemPrice = parseFloat($cartItem.find(".cart-item-price").text().replace("$", "")) / quantity;

        if (quantity > 0) {  
            quantity += 1;
            $quantityElement.text(quantity);

            var totalPrice = itemPrice * quantity;
            $cartItem.find('.cart-item-price').text("$" + totalPrice.toFixed(2));

            totalAmount1 += itemPrice;
            totalAmount2 += itemPrice;
            $("#total1").text("Total: $" + totalAmount1.toFixed(2));
         

            var itemName = $cartItem.find('.cart-item-name').text();
            var itemIndex = cartItems.findIndex(item => item.itemName === itemName);
            if (itemIndex !== -1) {
                cartItems[itemIndex].quantity = quantity;
                cartItems[itemIndex].itemPrice = itemPrice * quantity;
                saveCartItems(cartItems);
                updateCheckoutButton();
            }
        }
    });

    $(".clear-button").click(function() {
        $(".cart-grid").find('.cart-item').remove();
        totalAmount1 = 0.00;
        totalAmount2 = 0.00;
        $("#total1").text("Total: $" + totalAmount1.toFixed(2));
     
        cartItems = [];
        clearCartItems();
        updateCheckoutButton();
    });
});



















































