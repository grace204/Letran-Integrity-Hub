function fetchSuggestions() {
    const query = document.getElementById('search-input').value;

    if (query.length > 2) { // Only fetch if input is greater than 2 characters
      const xhr = new XMLHttpRequest();
      xhr.open('GET', `../user/search_suggestions.php?q=${encodeURIComponent(query)}`, true);
      xhr.onload = function() {
        if (this.status === 200) {
          document.getElementById('suggestions').innerHTML = this.responseText;
        }
      };
      xhr.send();
    } else {
      document.getElementById('suggestions').innerHTML = '';
    }
  }

  function selectSuggestion(value) {
    document.getElementById('search-input').value = value;
    document.getElementById('suggestions').innerHTML = '';
    // You may redirect the user or perform other actions here
  }