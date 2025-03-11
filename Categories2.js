
function clearGrid() {
    // Reset all grid items
    for (var i = 1; i <= 5; i++) {
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
                $("#item-price" + (i + 1)).html("<p>" + "$" + items[i].Price + " per day</p>");
                $("#item-availability" + (i + 1)).html(function() {    
                    if(items[i].Availability == 1) {
                        return '<p class="status-available">Status: Available</p>' + 
                         '<button class="add-to-cart-button-Available">Add to cart</button>';
                        
                    } else {
                        return '<p class="status-disabled">Status: Unavailable</p>' + 
                        '<button class="add-to-cart-button-Unavailable">Add to cart</button>';
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
        $.get("http://localhost/BandWebsite/PopulateGriditemswithsearch.php?query=" + query, function(data3){
        
            var items = JSON.parse(data3);
            for (var i = 0; i < items.length; i++) {
                $("#grid-image" + (i + 1)).attr("src", items[i].Image_URL);
                $("#category-header").html("<h2>Results</h2>");
                $("#item-description" + (i + 1)).html("<p class='item-description'>" + items[i].Name + "</p>");
                $("#item-price" + (i + 1)).html("<p class='item-price'>" + "$" + items[i].Price + " per day</p>");
                $("#item-availability" + (i + 1)).html(function() {    
                    if(items[i].Availability == 1) {
                        return '<p class="status-available">Status: Available</p>' + 
                         '<button class="add-to-cart-button-Available">Add to cart</button>';
                    } else {
                        return '<p class="status-disabled">Status: Unavailable</p>' + 
                        '<button class="add-to-cart-button-Unavailable">Add to cart</button>';
                    } 
                    });
            }
        });
    });
});

var totalAmount1 = 0.00;
var totalAmount2 = 0.00;

$(document).ready(function() {

    function saveCartItemsToLocalStorage(cartItems) {
        var expirationTime = new Date().getTime() + (24 * 60 * 60 * 1000);
        localStorage.setItem('cartItems', JSON.stringify({ items: cartItems, expires: expirationTime }));
    }

    function getCartItemsFromLocalStorage() {
        var storedData = JSON.parse(localStorage.getItem('cartItems'));
        if (storedData && storedData.expires > new Date().getTime()) {
            return storedData.items;
        } else {
            return [];
        }
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
            '<i class="fa fa-times remove-item"></i>' +
            '<i class="fa fa-plus add-item"></i>' +
        '</div>');
            $(".cart-grid").prepend($newCartItem);
    
            totalAmount1 += item.itemPrice;
            totalAmount2 += item.itemPrice;
        });

        $("#total1").text("Total: $" + totalAmount1.toFixed(2));
        $("#total2").text("$" + totalAmount2.toFixed(2));
    }

    var cartItems = getCartItemsFromLocalStorage() || [];
    updateCartUI(cartItems);

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

    updateCheckoutButton();

    $(document).on("click", ".add-to-cart-button-Available", function() {
        var $gridItem = $(this).closest(".grid-item");
        var imageUrl = $gridItem.find(".grid-image").attr("src");
        var itemName = $gridItem.find(".item-description").text();
        var itemPrice = parseFloat($gridItem.find(".item-price").text().replace("$", ""));

        /*if (cartItems.length > 0 && cartItems[0].itemName !== itemName) {
            cartItems = [];
        }*/

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

        saveCartItemsToLocalStorage(cartItems);
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
            $("#total2").text("$" + totalAmount2.toFixed(2));

            var itemName = $cartItem.find('.cart-item-name').text();
            var itemIndex = cartItems.findIndex(item => item.itemName === itemName);
            if (itemIndex !== -1) {
                cartItems[itemIndex].quantity = quantity;
                cartItems[itemIndex].itemPrice = totalPrice;
                saveCartItemsToLocalStorage(cartItems);
            }
        }

        if (quantity == 0) {
            $cartItem.remove();
            var itemName = $cartItem.find('.cart-item-name').text();
            cartItems = cartItems.filter(item => item.itemName !== itemName);
            saveCartItemsToLocalStorage(cartItems);
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
            $("#total2").text("$" + totalAmount2.toFixed(2));

            var itemName = $cartItem.find('.cart-item-name').text();
            var itemIndex = cartItems.findIndex(item => item.itemName === itemName);
            if (itemIndex !== -1) {
                cartItems[itemIndex].quantity = quantity;
                cartItems[itemIndex].itemPrice = itemPrice * quantity;
                saveCartItemsToLocalStorage(cartItems);
            }
        }
    });

    $(".clear-button").click(function() {
        $(".cart-grid").find('.cart-item').remove();
        totalAmount1 = 0.00;
        totalAmount2 = 0.00;
        $("#total1").text("Total: $" + totalAmount1.toFixed(2));
        $("#total2").text("$" + totalAmount2.toFixed(2));
        cartItems = [];
        saveCartItemsToLocalStorage(cartItems);
        updateCheckoutButton();
    });
});



















































