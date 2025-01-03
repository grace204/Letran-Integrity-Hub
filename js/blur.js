// Function to toggle blur and save the state in localStorage
function toggleBlur() {
    const blurOverlay = document.getElementById('blur-overlay');
    if (blurOverlay.style.display === 'none' || blurOverlay.style.display === '') {
        blurOverlay.style.display = 'block';
        localStorage.setItem('isBlurred', 'true');
    } else {
        blurOverlay.style.display = 'none';
        localStorage.setItem('isBlurred', 'false');
    }
}

// Function to load the blur state from localStorage
function loadBlurState() {
    const isBlurred = localStorage.getItem('isBlurred');
    const blurOverlay = document.getElementById('blur-overlay');
    if (isBlurred === 'true') {
        blurOverlay.style.display = 'block';
    } else {
        blurOverlay.style.display = 'none';
    }
}

// Load the blur state when the page loads
document.addEventListener('DOMContentLoaded', function() {
    loadBlurState();
});

// Add event listener for hotkey Ctrl + Alt + 1
document.addEventListener('keydown', function(event) {
    if (event.ctrlKey && event.altKey && event.key === '1') {
        toggleBlur();
    }
});