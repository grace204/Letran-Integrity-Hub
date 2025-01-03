<?php
session_start();
//if (!isset($_SESSION["user_id"])) {
//    header("Location: ./index.php");
//    exit();
//}

include './includes/connect.php';

//$user_id = $_SESSION["user_id"];
//$role = $_SESSION["role"];

// Get the student_id from the URL
$student_id = $_GET['id'];

// Fetch student details
$student_sql = "SELECT * FROM students WHERE student_id = $student_id";
$student_result = $conn->query($student_sql);

if ($student_result->num_rows > 0) {
    $student = $student_result->fetch_assoc();
} else {
    die("Student not found.");
}

// Fetch student violations
$violation_sql = "SELECT * FROM violations WHERE student_id = $student_id ORDER BY violation_date DESC";
$violation_result = $conn->query($violation_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>LETRAN INTEGRITY HUB: A WEB-BASED STUDENT VIOLATION MANAGEMENT SYSTEM - Dashboard</title>
    <link href="./vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="./css/flip.css" rel="stylesheet">
    <link rel="stylesheet" href="css/docs.css">
    <link rel="stylesheet" href="css/jquery.ui.css">
    <link rel="icon" type="image/png" href="./assets/<?= htmlspecialchars($favicon) ?>?v=<?= $cache_buster ?>">
    <style>
    /* Main container for centering the content */
    .centered-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 20px;
    }

    /* Styling for individual elements (Name, Email, QR Code) */
    .centered-content p {
        font-size: 1rem;
        margin: 5px 0;
        text-align: center;
    }

    .qr-code img {
        border: 2px solid #ccc;
        border-radius: 8px;
        width: 150px;
        height: 150px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        margin: 15px 0;
    }

    /* For the table */
    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .table th, .table td {
        padding: 12px;
        text-align: left;
        font-size: 0.9rem;
    }

    .table th {
        background-color: #1f3c88;
        color: white;
        text-align: center;
    }

    .table td {
        text-align: center;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #f9f9f9;
    }

    .table-striped tbody tr:hover {
        background-color: #f1f1f1;
    }

    /* Card for overall layout */
    .card {
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 20px;
        width: 80%;
        margin: 0 auto;
        background-color: white;
    }

    /* For the button */
    .button {
        background-color: #1f3c88;
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 8px;
        display: inline-block;
        margin-top: 20px;
        font-size: 1rem;
    }

    .button:hover {
        background-color: #ff6f61;
        transition: 0.3s;
    }
    .btn-tiz {
    position: fixed;
    color: #fff;
    top: 100px;
    left: 300px;
    background-color: #4e73df;
    border-color: #4e73df;
}
</style>


</head>

<body id="page-top">
<!-- Modal for the flipbook (simplified) -->
<div class="modal fade" id="flipbookModal" tabindex="-1" role="dialog" aria-labelledby="flipbookModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document" style="max-width: 90%;">
    <div class="modal-content" style="border: none;">
      <div class="modal-body" style="padding: 0;">

        <div id="canvas">
            <div id="book-zoom">
                <div class="sample-docs">
                    <div ignore="1" class="tabs"><div class="left">  </div> <div class="right"> </div></div>
                    <div class="hard"></div>
                    <div class="hard"></div>
                    <div class="hard p37"></div>
                    <div class="hard p38"></div>
                </div>
            </div>
        </div>
      </div>
      <!--<div>
        <button type="button" class="btn btn-tiz" data-dismiss="modal">Close</button>
      </div>-->
    </div>
  </div>
</div>
    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Content Row -->
                    <a class="back-button" href="http://localhost/letran/">&#8592;</a> <!-- Arrow Back Button -->
                    <div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Student Details</h6>
            </div>
            <div class="card-body">
                <!-- Centered Content (Name, Email, QR Code) -->
                <div class="centered-content">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($student['student_name'] . ' ' . $student['middlename']) . ' ' . $student['lastname'] . ' ' . $student['suffix']; ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($student['student_email']); ?></p>
                    <div class="qr-code">
                        <img src="./qr_codes/<?php echo htmlspecialchars($student['qr_code']); ?>" alt="QR Code">
                    </div>
                </div>

                <!-- Violation History -->
                <h2>Violation History</h2>
                <?php if ($violation_result->num_rows > 0): ?>
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Violation Type</th>
                                <th>Description</th>
                                <th>Date and Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($violation = $violation_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($violation['violation_type']); ?></td>
                                    <td><?php echo htmlspecialchars($violation['violation_description']); ?></td>
                                    <td>
                                        <?php 
                                        $formatted_date = date("F j, Y", strtotime($violation['violation_date']));
                                        $formatted_time = date("h:i A", strtotime($violation['violation_date'])); 
                                        echo "$formatted_date at $formatted_time"; 
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No violations recorded.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Trigger button to open the modal -->
<button type="button" class="btn btn-primary btn-open-handbook" data-toggle="modal" data-target="#flipbookModal">
  College Handbook
</button>
<!-- Trigger button to open the modal -->
<button type="button" class="btn btn-primary btn-open-handbook" data-toggle="modal" data-target="#flipbookModal">
  Open Handbook
</button>
    <script src="./vendor/jquery/jquery.min.js"></script>
    <script src="./vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="./vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="./js/sb-admin2.min.js"></script>
    <script type="text/javascript" src="./extras/jquery.min.js"></script>
    <script type="text/javascript" src="./extras/jquery-ui.min.js"></script>
    <script type="text/javascript" src="./lib/hash.js"></script>
    <script type="text/javascript" src="./lib/turn.min.js"></script>
    <script type="text/javascript" src="js/docs.js"></script>
    <script type="text/javascript">

function loadApp() {

    var flipbook = $('.sample-docs');

    if (flipbook.width() == 0 || flipbook.height() == 0) {
        setTimeout(loadApp, 10);
        return;
    }

    $('#book-zoom').on('wheel', function(event) {
        event = event.originalEvent;
        var data = $(this).data(),
            flipbook = $('.sample-docs'),
            view = flipbook.turn('view', page).filter( (page) => page != 0 ),
            page = flipbook.turn('page'),
            delay = 500;

        if (typeof(data.scrollTimer) === 'undefined') data.scrollTimer = 0;
        if (data.scrollTimer > event.timeStamp) return;
        data.scrollTimer = event.timeStamp + delay;

        if (event.deltaY > 0) page = Math.max(...view) + 1;
        if (event.deltaY < 0) page = Math.min(...view) - 1;

        page = Math.max(Math.min(flipbook.turn('pages'), page), 1);
        flipbook.turn('page', page);
    });

    $("#slider").slider({
        min: 1,
        max: 100,
        start: function(event, ui) {
            if (!window._thumbPreview) {
                _thumbPreview = $('<div />', {'class': 'thumbnail'}).html('<div></div>');
                setPreview(ui.value);
                _thumbPreview.appendTo($(ui.handle));
            } else {
                setPreview(ui.value);
            }
            moveBar(false);
        },
        slide: function(event, ui) {
            setPreview(ui.value);
        },
        stop: function() {
            if (window._thumbPreview) _thumbPreview.removeClass('show');
            $('.sample-docs').turn('page', Math.max(1, $(this).slider('value')*2 - 2));
        }
    });

    Hash.on('^page\/([0-9]*)$', {
        yep: function(path, parts) {
            var page = parts[1];
            if (page !== undefined) {
                if ($('.sample-docs').turn('is'))
                    $('.sample-docs').turn('page', page);
            }
        },
        nop: function(path) {
            if ($('.sample-docs').turn('is'))
                $('.sample-docs').turn('page', 1);
        }
    });

    $(document).on('keydown', function(e) {
        var previous = 37, next = 38;
        switch (e.keyCode) {
            case previous:
                $('.sample-docs').turn('previous');
                break;
            case next:
                $('.sample-docs').turn('next');
                break;
        }
    });

    flipbook.turn({
        elevation: 50,
        acceleration: false,
        gradients: true,
        autoCenter: true,
        duration: 1000,
        pages: 37,
        when: {
            turning: function(e, page, view) {
                var book = $(this),
                    currentPage = book.turn('page'),
                    pages = book.turn('pages');

                if (currentPage > 3 && currentPage < pages - 3) {
                    if (page == 1) {
                        book.turn('page', 2).turn('stop').turn('page', page);
                        e.preventDefault();
                        return;
                    } else if (page == pages) {
                        book.turn('page', pages - 1).turn('stop').turn('page', page);
                        e.preventDefault();
                        return;
                    }
                } else if (page > 3 && page < pages - 3) {
                    if (currentPage == 1) {
                        book.turn('page', 2).turn('stop').turn('page', page);
                        e.preventDefault();
                        return;
                    } else if (currentPage == pages) {
                        book.turn('page', pages - 1).turn('stop').turn('page', page);
                        e.preventDefault();
                        return;
                    }
                }

                Hash.go('page/' + page).update();

                if (page == 1 || page == pages)
                    $('.sample-docs .tabs').hide();
            },
            turned: function(e, page, view) {
                var book = $(this);
                $('#slider').slider('value', getViewNumber(book, page));

                if (page != 1 && page != book.turn('pages'))
                    $('.sample-docs .tabs').fadeIn(500);
                else
                    $('.sample-docs .tabs').hide();

                book.turn('center');
                updateTabs();
            },
            start: function(e, pageObj) {
                moveBar(true);
            },
            end: function(e, pageObj) {
                var book = $(this);
                setTimeout(function() {
                    $('#slider').slider('value', getViewNumber(book));
                }, 1);
                moveBar(false);
            },
            missing: function(e, pages) {
                for (var i = 0; i < pages.length; i++)
                    addPage(pages[i], $(this));
            }
        }
    }).turn('page', 2);

    $('#slider').slider('option', 'max', numberOfViews(flipbook));
    flipbook.addClass('animated');
    $('#canvas').css({visibility: 'visible'});
}

$('#canvas').css({visibility: 'hidden'});
loadApp();

</script>
</body>
</html>