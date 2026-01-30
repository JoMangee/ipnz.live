<?php 
    ini_set("include_path", '/home2/ipnz/php:' . ini_get("include_path") );
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
    <link href="css/ipnz-live.css" rel="stylesheet">
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
                <a class="navbar-brand" href="./">
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
                            <a class="nav-link click-scroll" href="./#section_1">Home</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link click-scroll" href="./#section_2">About</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link click-scroll" href="./#section_3">Members</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link click-scroll" href="./#section_6">Contact</a>
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
                        <?php
                        // Handle contact form submission
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['contact']) && $_GET['contact'] === 'true') {
                            require('datacenter/database.php');
                            
                            $name = trim($_POST['contact-name'] ?? '');
                            $email = trim($_POST['contact-email'] ?? '');
                            $company = trim($_POST['contact-company'] ?? '');
                            $message = trim($_POST['contact-message'] ?? '');
                            
                            // Validate email
                            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                echo '<div class="alert alert-danger" style="margin:20px 0;">Invalid email address.</div>';
                            } else {
                                // Store contact message (you may want to create a contacts table)
                                // For now, create a record with status = 1 to distinguish from members
                                $stmt = $connection->prepare("INSERT INTO ipnz_members (name, email, phone, additional_request, status) VALUES (?, ?, ?, ?, 1)");
                                $stmt->bind_param("ssss", $name, $email, $company, $message);
                                
                                if ($stmt->execute()) {
                                    echo '<div class="alert alert-success" style="background-color: #4CAF50; color:white; margin:20px 0; text-align:center;">Thank you for your message! We\'ll be in touch soon.</div>';
                                } else {
                                    echo '<div class="alert alert-danger" style="margin:20px 0;">Sorry, there was an error. Please try again.</div>';
                                }
                                $stmt->close();
                            }
                        }
                        ?>
                        <form class="custom-form join-form mb-5 mb-lg-0" action="auth.php?contact=true" method="post" role="form">
                            <h2 class="text-center mb-4">Thank You!</h2>

                            <div class="join-form-body">
                                <p class="text-center mb-4">Your registration was successful! Want to get in touch?</p>
                                
                                <div class="row">
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <input type="text" name="contact-name" id="contact-name"
                                            class="form-control" placeholder="Your name" required>
                                    </div>

                                    <div class="col-lg-6 col-md-6 col-12">
                                        <input type="email" name="contact-email" id="contact-email"
                                            pattern="[^ @]*@[^ @]*" class="form-control" placeholder="Email address"
                                            required>
                                    </div>
                                </div>

                                <input type="text" class="form-control" name="contact-company"
                                    placeholder="Company or Affiliation">

                                <textarea name="contact-message" rows="4" class="form-control"
                                    id="contact-message" placeholder="Your message to us" required></textarea>

                                <div class="col-lg-4 col-md-10 col-8 mx-auto">
                                    <button type="submit" class="form-control">Send Message</button>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <a href="./" class="btn btn-outline-light">Return to Home</a>
                                </div>
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
                                <a href="#" class="social-icon-link" aria-label="X">
                                    <span class="bi-x"></span>
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

    <?php
    // Version marker: short digest over key files for deployment verification
    $versionMeta = include __DIR__ . '/version_meta.php';
    $vmFiles = $versionMeta['files'] ?? [];
    $vmParts = [];
    foreach ($vmFiles as $vmRel) {
        $vmPath = __DIR__ . '/' . $vmRel;
        if (is_file($vmPath)) {
            $vmParts[] = hash_file('sha256', $vmPath);
        }
    }
    $vmDigest = substr(hash('sha256', implode('', $vmParts)), 0, 12);
    echo "<!-- version=" . ($versionMeta['version'] ?? 'unknown') . " commit=" . ($versionMeta['commit'] ?? 'unknown') . " digest=" . $vmDigest . " -->";
    ?>
</body>
</html>