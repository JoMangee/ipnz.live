<?php 
    ini_set("include_path", '/home2/ipnz/php:' . ini_get("include_path") );
    use PhpDevCommunity\DotEnv;
    $absolutePathToEnvFile = '/home2/ipnz/ipnz-live/.env'; 
    (new DotEnv($absolutePathToEnvFile))->load();

    if (getenv('APP_ENV')=="authdev") {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    }elseif (getenv('APP_ENV')== 'live') {
        error_reporting(0);
        ini_set('display_errors', 0);
    }
    // authdev
    // define variables and set to empty values
    $name = $email = $memtype1 = $memtype2 = $phone = $comment = "";
    $formpost = array();

    // Database connection details
    $dbservername = getenv('DATABASE_DNS');
    $dbusername = getenv('DATABASE_USER');
    $dbpassword = getenv('DATABASE_PASSWORD');
    $dbname = getenv('DATABASE_NAME');

    // Create connection
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Function to sanitize inputs
    function sanitize_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    // Check if form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Sanitize input
        $name = sanitize_input($_POST['name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');

        // Handle file upload
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        if(isset($_POST["submit"])) {
            $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
            if($check !== false) {
                $uploadOk = 1;
            } else {
                echo "File is not an image.";
                $uploadOk = 0;
            }
        }

        // Check file size
        if ($_FILES["fileToUpload"]["size"] > 500000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
        // if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                echo "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.";
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }

        // SQL to create table if not exists
        $sql = "CREATE TABLE IF NOT EXISTS members (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(30) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(12),
            memtype VARCHAR(30),
            status VARCHAR(30),
            comment TEXT,
            avatar_id VARCHAR(128),
            image VARCHAR(255)
            createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        )";

        if ($conn->query($sql) === TRUE) {
            echo "Table users created successfully or already exists.";
        } else {
            echo "Error creating table: " . $conn->error;
        }

        // Insert data into the table
        $stmt = $conn->prepare("INSERT INTO members (name, email, phone, memtype, comment) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $target_file);

        if ($stmt->execute()) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    $conn->close();



// the original code:

 
     if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // define variables and set to empty values
        $name = $email = $memtype1 = $memtype2 = $phone = $comment = "";
        $formpost = array();

         $formpost = $_POST;
         $name = test_input($_POST["join-form-name"]);
         $email = test_input($_POST["join-form-email"]);
         $phone = test_input($_POST["join-form-phone"]);
         $memtype = test_input($_POST["joinForm"]);
         $comment = test_input($_POST["join-form-message"]);
     }
 
     function test_input($data) {
         $data = trim($data);
         $data = stripslashes($data);
         $data = htmlspecialchars($data);
         return $data;
     }
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="description" content="">
    <meta name="author" content="">

    <title>IPnz.live - auth</title>

    <!-- CSS FILES -->
    <link href="css/google-fonts.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-icons.css" rel="stylesheet">
    <link href="css/templatemo-festava-live.css" rel="stylesheet">
    <!-- Matomo -->
    <script>
        var _paq = window._paq = window._paq || [];
       /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
       _paq.push(['trackPageView']);
       _paq.push(['enableLinkTracking']);
       (function() {
         var u="//a.ipnz.live/";
         _paq.push(['setTrackerUrl', u+'matomo.php']);
         _paq.push(['setSiteId', '6']);
         var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
         g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
       })();
      </script>
      <noscript>
        <!-- Matomo Image Tracker-->
            <img referrerpolicy="no-referrer-when-downgrade" src="https://a.ipnz.live/matomo.php?idsite=6&amp;rec=1&amp;action_name=IPnz.live-auth" style="border:0" alt="" />
        <!-- End Matomo -->
        </noscript>
     <!-- End Matomo Code -->
</head>

<body>

    <main>
        <!-- <?php print_r($formpost); ?> -->

        <header class="site-header">
            <div class="container">
                <div class="row">

                    <div class="col-lg-12 col-12 d-flex flex-wrap">
                        <p class="d-flex me-4 mb-0">
                            <i class="bi-person custom-icon me-2"></i>
                            <strong class="text-dark">Auth to IPnz.live 2025</strong>
                        </p>
                    </div>

                </div>
            </div>
        </header>
        <script>
            window.fbAsyncInit = function() {
              FB.init({
                appId      : '1110554260864808',
                xfbml      : true,
                version    : 'v21.0'
              });
              FB.AppEvents.logPageView();
            };
          
            (function(d, s, id){
               var js, fjs = d.getElementsByTagName(s)[0];
               if (d.getElementById(id)) {return;}
               js = d.createElement(s); js.id = id;
               js.src = "https://connect.facebook.net/en_US/sdk.js";
               fjs.parentNode.insertBefore(js, fjs);
             }(document, 'script', 'facebook-jssdk'));
          </script>

        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="index.html">
                    IPnz.live
                </a>

                <a href="join.html" class="btn custom-btn d-lg-none ms-auto me-4">Join us</a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav align-items-lg-center ms-auto me-lg-5">
                        <li class="nav-item">
                            <a class="nav-link click-scroll" href="index.html#section_1">Home</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link click-scroll" href="index.html#section_2">About</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link click-scroll" href="index.html#section_3">Members</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link click-scroll" href="index.html#section_6">Contact</a>
                        </li>
                    </ul>

                    <a href="join.html" class="btn custom-btn d-lg-block d-none">Join us</a>
                </div>
            </div>
        </nav>

        <section class="join-section section-padding">
            <div class="section-overlay"></div>

            <div class="container">
                <div class="row">

                    <div class="col-lg-6 col-10 mx-auto">
                        <form class="custom-form join-form mb-5 mb-lg-0" action="auth.php?confirm" method="post" role="form">
                            <h2 class="text-center mb-4">Thanks for requesting to Join us</h2>

                            <div class="join-form-body">
                                <div class="row">
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <input type="text" name="join-form-name" id="join-form-name"
                                            class="form-control" placeholder="<?php echo $name; ?>" disabled>
                                    </div>

                                    <div class="col-lg-6 col-md-6 col-12">
                                        <input type="email" name="join-form-email" id="join-form-email"
                                             class="form-control" placeholder="<?php echo $email; ?>" disabled>                                 
                                    </div>
                                </div>

                                <input type="tel" class="form-control" name="join-form-phone"
                                    placeholder="<?php echo $phone; ?>" disabled>

                                <h6>Choose join Type</h6>

                                <div class="row">
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="form-check form-control">
                                            <input class="form-check-input" type="radio" name="joinForm"
                                                id="flexRadioDefault1" <?php if ($memtype == "Early access"){ echo "checked";} ?> disabled>
                                            <label class="form-check-label" for="flexRadioDefault1">
                                                Early access
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="form-check form-check-radio form-control">
                                            <input class="form-check-input" type="radio" name="joinForm"
                                                id="flexRadioDefault2" <?php if ($memtype == "Standard"){ echo "checked";} ?> disabled>
                                            <label class="form-check-label" for="flexRadioDefault2">
                                                Standard
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <textarea name="join-form-message" rows="3" class="form-control"
                                    id="join-form-message" placeholder="<?php echo $comment; ?>" disabled><?php echo $comment; ?></textarea>

                                <div class="col-lg-4 col-md-10 col-8 mx-auto">
                                    <button type="submit" class="form-control">Confirm Correct</button>
                                    <button type="submit" class="form-control">Cancel Join</button>
                                </div>

                                <input type="file" name="fileToUpload" id="fileToUpload" ><br>
                            </div>
                        </form>
                    </div>
                </div>
        </section>
    </main>


    <footer class="site-footer">
        <div class="site-footer-top">
            <div class="container">
                <div class="row">

                    <div class="col-lg-6 col-12">
                        <h2 class="text-white mb-lg-0">IPnz.live</h2>
                    </div>

                    <div class="col-lg-6 col-12 d-flex justify-content-lg-end align-items-center">
                        <ul class="social-icon d-flex justify-content-lg-end">
                            <li class="social-icon-item">
                                <a href="#" class="social-icon-link">
                                    <span class="bi-twitter"></span>
                                </a>
                            </li>

                            <li class="social-icon-item">
                                <a href="#" class="social-icon-link">
                                    <span class="bi-apple"></span>
                                </a>
                            </li>

                            <li class="social-icon-item">
                                <a href="#" class="social-icon-link">
                                    <span class="bi-instagram"></span>
                                </a>
                            </li>

                            <li class="social-icon-item">
                                <a href="#" class="social-icon-link">
                                    <span class="bi-youtube"></span>
                                </a>
                            </li>

                            <li class="social-icon-item">
                                <a href="#" class="social-icon-link">
                                    <span class="bi-pinterest"></span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row">

                <div class="col-lg-6 col-12 mb-4 pb-2">
                    <h5 class="site-footer-title mb-3">Links</h5>

                    <ul class="site-footer-links">
                        <li class="site-footer-link-item">
                            <a href="#" class="site-footer-link">Home</a>
                        </li>

                        <li class="site-footer-link-item">
                            <a href="#" class="site-footer-link">About</a>
                        </li>

                        <li class="site-footer-link-item">
                            <a href="#" class="site-footer-link">Members</a>
                        </li>

                        <li class="site-footer-link-item">
                            <a href="#" class="site-footer-link">Schedule</a>
                        </li>

                        <li class="site-footer-link-item">
                            <a href="#" class="site-footer-link">Pricing</a>
                        </li>

                        <li class="site-footer-link-item">
                            <a href="#" class="site-footer-link">Contact</a>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 col-12 mb-4 mb-lg-0">
                    <h5 class="site-footer-title mb-3">Have a question?</h5>

                    <p class="text-white d-flex mb-1">
                        <a href="tel: 090-080-0760" class="site-footer-link">
                            090-080-0760
                        </a>
                    </p>

                    <p class="text-white d-flex">
                        <a href="mailto:hello@company.com" class="site-footer-link">
                            hello@company.com
                        </a>
                    </p>
                </div>

                <div class="col-lg-3 col-md-6 col-11 mb-4 mb-lg-0 mb-md-0">
                    <h5 class="site-footer-title mb-3">Location</h5>

                    <p class="text-white d-flex mt-3 mb-2">
                        Silang Junction South, Tagaytay, Cavite, Philippines</p>

                    <a class="link-fx-1 color-contrast-higher mt-3" href="#">
                        <span>Our Maps</span>
                        <svg class="icon" viewBox="0 0 32 32" aria-hidden="true">
                            <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="16" cy="16" r="15.5"></circle>
                                <line x1="10" y1="18" x2="16" y2="12"></line>
                                <line x1="16" y1="12" x2="22" y2="18"></line>
                            </g>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="site-footer-bottom">
            <div class="container">
                <div class="row">

                    <div class="col-lg-3 col-12 mt-5">
                        <p class="copyright-text">Copyright © 2036 IPnz.live Company</p>
                        <p class="copyright-text">Distributed by: <a href="https://themewagon.com">ThemeWagon</a></p>
                    </div>

                    <div class="col-lg-8 col-12 mt-lg-5">
                        <ul class="site-footer-links">
                            <li class="site-footer-link-item">
                                <a href="#" class="site-footer-link">Terms &amp; Conditions</a>
                            </li>

                            <li class="site-footer-link-item">
                                <a href="#" class="site-footer-link">Privacy Policy</a>
                            </li>

                            <li class="site-footer-link-item">
                                <a href="#" class="site-footer-link">Your Feedback</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- JAVASCRIPT FILES -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.sticky.js"></script>
    <script src="js/custom.js"></script>
</body>
</html>