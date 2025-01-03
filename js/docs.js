/* Documentation sample */

// Function to load a page image into the book viewer
function loadPage(page) {
	var img = $('<img />'); // Create a new image element
	img.on('load', function() { // When the image loads
		var container = $('.sample-docs .p' + page); // Get the corresponding page container
		// Set the image size to fit the container
		img.css({ width: container.width(), height: container.height() });
		img.appendTo($('.sample-docs .p' + page)); // Append the image to the container
		container.find('.loader').remove(); // Remove the loader once the image is loaded
	});
	img.attr('src', 'pages/' + (page - 2) + '.png'); // Set the image source
}

// Function to add a new page to the book
function addPage(page, book) {
	var id, pages = book.turn('pages'); // Get the current number of pages
	var element = $('<div />', {}); // Create a new page element

	if (book.turn('addPage', element, page)) { // Add the new page to the book
		if (page < 38) { // Limit to 28 pages
			element.html('<div class="gradient"></div><div class="loader"></div>'); // Add gradient and loader
			loadPage(page); // Load the page content
		}
	}
}

// Function to update the navigation tabs based on current page
function updateTabs() {
	var tabs = { 7: '', 12: '', 14: '', 16: '', 23: '' },
		left = [],
		right = [],
		book = $('.sample-docs'),
		actualPage = book.turn('page'), // Get the actual page number
		view = book.turn('view'); // Get the current view

	// Loop through each tab to determine its position
	for (var page in tabs) {
		var isHere = view.indexOf(parseInt(page, 10)) != -1; // Check if page is in the current view

		if (page > actualPage && !isHere) // If page is after the current page and not in view
			right.push('<a href="#page/' + page + '">' + tabs[page] + '</a>');
		else if (isHere) { // If the page is currently in view
			if (page % 2 === 0) // If page is even
				left.push('<a href="#page/' + page + '" class="on">' + tabs[page] + '</a>');
			else // If page is odd
				right.push('<a href="#page/' + page + '" class="on">' + tabs[page] + '</a>');
		} else // If the page is before the current page
			left.push('<a href="#page/' + page + '">' + tabs[page] + '</a>');
	}

	// Update the left and right tab sections
	$('.sample-docs .tabs .left').html(left.join(''));
	$('.sample-docs .tabs .right').html(right.join(''));
}

// Function to calculate the total number of views in the book
function numberOfViews(book) {
	return book.turn('pages') / 2 + 1; // Returns total views based on pages
}

// Function to get the view number for a specific page
function getViewNumber(book, page) {
	return parseInt((page || book.turn('page')) / 2 + 1, 10); // Calculates view number based on page
}

// Function to move the slider's z-index for visibility
function moveBar(yes) {
	$('#slider .ui-slider-handle').css({ zIndex: yes ? -1 : 10000 }); // Set z-index for slider handle
}

// Function to set the preview thumbnail based on current view
function setPreview(view) {
	var previewWidth = 115,
		previewHeight = 73,
		previewSrc = 'pics/preview.jpg',
		preview = $(_thumbPreview.children(':first')), // Get the first child of the thumbnail preview
		numPages = (view == 1 || view == $('#slider').slider('option', 'max')) ? 1 : 2, // Determine number of pages for preview
		width = (numPages == 1) ? previewWidth / 2 : previewWidth; // Adjust width based on number of pages

	// Adjust thumbnail preview dimensions
	_thumbPreview.
		addClass('no-transition').
		css({
			width: width + 15,
			height: previewHeight + 15,
			top: -previewHeight - 30,
			left: ($($('#slider').children(':first')).width() - width - 15) / 2
		});

	preview.css({
		width: width,
		height: previewHeight
	});

	// Set the background image if not already set
	if (preview.css('background-image') === '' || preview.css('background-image') == 'none') {
		preview.css({ backgroundImage: 'url(' + previewSrc + ')' });

		// Add transition after setting background image
		setTimeout(function() {
			_thumbPreview.removeClass('no-transition');
		}, 0);
	}

	// Adjust the background position for the preview thumbnail
	preview.css({
		backgroundPosition: '0px -' + ((view - 1) * previewHeight) + 'px'
	});
}
