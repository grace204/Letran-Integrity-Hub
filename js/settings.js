
// Function to capture image from webcam and upload to server
async function captureAndUploadImage() {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: true });
    const video = document.createElement('video');
    video.srcObject = stream;
    await video.play();

    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const context = canvas.getContext('2d');
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    const imageData = canvas.toDataURL('image/png');
    stream.getTracks().forEach(track => track.stop()); // Stop webcam

    // Send the image data to the server
    uploadImage(imageData);
  } catch (err) {
    console.error('Error accessing webcam:', err);
  }
}

// Function to upload image to the server
function uploadImage(imageData) {
  fetch('../save_image.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ image: imageData }),
  })
  .then(response => response.json())
  .then(data => {
    console.log('Image saved:', data);
  })
  .catch(error => {
    console.error('Error saving image:', error);
  });
}

// Disable context menu to prevent right-click inspection
document.addEventListener('contextmenu', event => {
  event.preventDefault();
});

// Disable key combinations for F12, Ctrl+Shift+I, Ctrl+Shift+J, and Ctrl+U
document.addEventListener('keydown', event => {
  if (event.key === 'F12' || (event.ctrlKey && event.shiftKey && (event.key === 'I' || event.key === 'J')) || (event.ctrlKey && event.key === 'U')) {
    event.preventDefault();
    alert('Do not use inspect!');
  }
});

// Show alert and prevent default behavior for right-click
document.addEventListener('mousedown', event => {
  if (event.button === 2) {
    event.preventDefault();
  }
});

// Reload the page on copy or cut actions
//function refreshPageOnCopyOrCut() {
  //document.addEventListener('copy', () => {
    //window.location.reload();
  //});

  //document.addEventListener('cut', () => {
    //window.location.reload();
  //});

  //document.addEventListener('keydown', event => {
    //if ((event.ctrlKey || event.metaKey) && (event.key === 'c' || event.key === 'v' || event.key === 'x')) {
      //window.location.reload();
    //}
  //});
//}
//refreshPageOnCopyOrCut();

// Disable text selection on the entire body
function disableSelection(element) {
  element.addEventListener('mousedown', function(e) {
    if (e.ctrlKey && e.key === 'A') {
      e.preventDefault();
    }
  });
  element.style.userSelect = 'none'; // Disable text selection
  element.style.MozUserSelect = 'none';
  element.style.msUserSelect = 'none';
  element.style.webkitUserSelect = 'none';
}

// Initialize features on DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
  disableSelection(document.body); // Disable text selection on the whole body
  startLogoutTimer(); // Start the inactivity timer when the DOM is loaded
});

// Custom console warning message
(function() {
  function customConsoleMessage() {
    console.clear();
    console.log('%cSige lang Warning!', 'font-size: 90px; font-weight: bold; color: red;');
    console.log(
      `%cThis is a browser feature intended for developers.
If someone told you to copy-paste something here to enable a Barangay-torres feature or "hack" someone's account, it is a scam and will give them access to your Barangay management system account.

See more
https://cybercrime.doj.gov.ph/republic-act-no-10175-cybercrime-prevention-act-of-2012/ for more information.`,
      'font-size: 20px; color: white;'
    );
  }

  function detectDevTools() {
    const devtools = /./;
    devtools.toString = function() {
      customConsoleMessage();
      return '';
    };
    console.log(devtools);
  }

  setInterval(detectDevTools, 1000);
})();

// Redirect to backend/logout.php after 30 minutes of inactivity
function redirectToLogout() {
  window.location.href = '../logout.php'; // Update path as needed
}

let timeout;

function startLogoutTimer() {
  timeout = setTimeout(() => {
    redirectToLogout();
  }, 1800000); // 30 minutes timeout (1800000 milliseconds)
}

document.addEventListener('mousemove', function() {
  clearTimeout(timeout);
  startLogoutTimer();
});

document.addEventListener('keydown', function() {
  clearTimeout(timeout);
  startLogoutTimer();
});

// Disable F5 and Ctrl+R
window.addEventListener('keydown', function (e) {
  if (e.key === 'F5' || (e.ctrlKey && e.key === 'r')) {
      e.preventDefault();
  }
});

// Disable right-click
window.addEventListener('contextmenu', function (e) {
  e.preventDefault();
});