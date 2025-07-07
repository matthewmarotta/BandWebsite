$(document).ready(function() {
  
    function getRecentSearches() {
        return JSON.parse(localStorage.getItem('recentSearches') || '[]');
    }

   
    function saveSearchQuery(query) {
        let recentSearches = getRecentSearches();
       
        recentSearches = recentSearches.filter(item => item !== query);
    
        recentSearches.unshift(query);
      
        recentSearches = recentSearches.slice(0, 10);
        localStorage.setItem('recentSearches', JSON.stringify(recentSearches));
    }

   
    function showRecentSearches() {
        const recentSearches = getRecentSearches();
        const recentSearchesList = $("#recent-searches");
        recentSearchesList.empty();
        recentSearches.forEach(query => {
            const listItem = $("<li>").text(query);
            listItem.on("click", function() {
                $("#form").val(query);
                recentSearchesList.hide();
            });
            recentSearchesList.append(listItem);
        });
        if (recentSearches.length > 0) {
            recentSearchesList.show();
        }
    }

    
    function fetchSuggestions(query) {
        $.get("http://localhost/BandWebsite/FetchSuggestions.php", { query: query }, function(data) {
            var suggestions = JSON.parse(data);
            const recentSearchesList = $("#recent-searches");
            recentSearchesList.empty();
            suggestions.forEach(suggestion => {
                const listItem = $("<li>").text(suggestion);
                listItem.on("click", function() {
                    $("#form").val(suggestion);
                    recentSearchesList.hide();
                 
                    $(".fa-search").click();
                });
                recentSearchesList.append(listItem);
            });
            if (suggestions.length > 0) {
                recentSearchesList.show();
            } else {
                showRecentSearches();
            }
        });
    }

    
    $("#form").on("focus", showRecentSearches);

     
     $("#form").on("input", function() {
        var query = $(this).val().trim();
        if (query) {
            fetchSuggestions(query);
        } else {
            showRecentSearches();
        }
    });

    
    $(".fa-search").click(function() {
        var query = $("#form").val();
        if (query) {
            saveSearchQuery(query);
        }
        
        
        $("#recent-searches").hide();
    });

   
    $(document).on("click", function(event) {
        if (!$(event.target).closest('.search-box').length) {
            $("#recent-searches").hide();
        }
    });
});
