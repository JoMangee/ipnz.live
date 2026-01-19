<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="description" content="">
    <meta name="author" content="">

    <title>IPnz.live - join</title>

    <!-- CSS FILES -->
    <link href="css/google-fonts.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-icons.css" rel="stylesheet">
    <link href="css/templatemo-festava-live.css" rel="stylesheet">
        <style>
        .frame {
            width: 100%;
            height: 800px;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen, Ubuntu, Cantarell, Fira Sans,
                Droid Sans, Helvetica Neue, sans-serif;
            padding: 20px;
            font-size: 14px;
            border: none;
        }

        .warning {
            background-color: #df68a2;
            padding: 3px;
            border-radius: 5px;
            color: white;
        }
    </style>
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
            <img referrerpolicy="no-referrer-when-downgrade" src="https://a.ipnz.live/matomo.php?idsite=6&amp;rec=1&amp;action_name=IPnz.live-join" style="border:0" alt="" />
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
                            <strong class="text-dark">Welcome to IPnz.live 2025</strong>
                        </p>
                    </div>

                </div>
            </div>
        </header>


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
                            <a class="nav-link click-scroll" href="./#section_4">Schedule</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link click-scroll" href="./#section_5">Pricing</a>
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

                    <div class="col-lg-10 col-10 mx-auto">
                        <form id="form" class="custom-form join-form mb-5 mb-lg-0" action="" method="post" role="form">
                            <h2 class="text-center mb-4">Get started here</h2>
                            <?php
                            require('datacenter/clientregistration.php');
                            ?>
                            <div class="join-form-body">
                                <div class="row">
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <input type="text" name="join-form-name" id="join-form-name" value="<?php echo isset($GLOBALS['form_data']['join-form-name']) ? htmlspecialchars($GLOBALS['form_data']['join-form-name']) : ''; ?>"
                                            class="form-control" placeholder="Name" required>
                                    </div>

                                    <div class="col-lg-6 col-md-6 col-12">
                                        <input type="email" name="join-form-email" id="join-form-email"
                                            pattern="[^ @]*@[^ @]*" class="form-control" placeholder="Email address"
                                            value="<?php echo isset($GLOBALS['form_data']['join-form-email']) ? htmlspecialchars($GLOBALS['form_data']['join-form-email']) : ''; ?>"
                                            required>
                                    </div>
                                    <!-- <div class="col-lg-6 col-md-6 col-12">
                                        <input type="text" name="join-form-avatar" id="join-form-avatar"
                                            class="form-control" placeholder="Avatar Url" required>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-12">
                
                                            
                                            <button type="button" class="btn btn-primary"><a href="avatar">Create Your Avatar Here</a></button>
                                    </div> -->
                                </div>

                                <input type="tel" class="form-control" name="join-form-phone"
                                    placeholder="Ph 028255788 or 028-25578835 or 028-2557-8835" 
                                    pattern="(\d{3}[- ]?\d{3,4}[- ]?\d{4}|\d{3}[- ]?\d{6})" 
                                    value="<?php echo isset($GLOBALS['form_data']['join-form-phone']) ? htmlspecialchars($GLOBALS['form_data']['join-form-phone']) : ''; ?>"
                                    required>

                                <h6>Choose join Type</h6>

                                <div class="row">
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="form-check form-control">
                                            <input class="form-check-input" type="radio" name="join-type"
                                                id="flexRadioDefault1" value="0"
                                                <?php echo (!isset($GLOBALS['form_data']['join-type']) || $GLOBALS['form_data']['join-type'] == '0') ? 'checked="checked"' : ''; ?>>
                                            <label class="form-check-label" for="flexRadioDefault1">
                                                Early access
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="form-check form-check-radio form-control">
                                            <input class="form-check-input" type="radio" name="join-type"
                                                id="flexRadioDefault2" value="1"
                                                <?php echo (isset($GLOBALS['form_data']['join-type']) && $GLOBALS['form_data']['join-type'] == '1') ? 'checked="checked"' : ''; ?>>
                                            <label class="form-check-label" for="flexRadioDefault2">
                                                Standard
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <textarea name="join-form-message" rows="3" class="form-control"
                                    id="join-form-message" placeholder="Additional Request"><?php echo isset($GLOBALS['form_data']['join-form-message']) ? htmlspecialchars($GLOBALS['form_data']['join-form-message']) : ''; ?></textarea>
                                <h6>Create Your Avatar <span style="color: #999; font-weight: normal; font-size: 14px;">(Optional - we'll use a default if skipped)</span></h6>
                            <input type="button" value="Open Ready Player Me" onClick="displayIframe()" />
                            <p>Avatar URL:</p> 
                            <input id="avatarUrl" name="avatarUrl" class="form-control border-0" type="text" readonly/>


                            <iframe id="frame" class="frame" allow="camera *; microphone *; clipboard-write" hidden></iframe>

                            <script>
                                const subdomain = 'ipnz'; // Replace with your custom subdomain
                                const frame = document.getElementById('frame');

                                frame.src = `https://${subdomain}.readyplayer.me/avatar?frameApi`;

                                window.addEventListener('message', subscribe);
                                document.addEventListener('message', subscribe);

                                function subscribe(event) {
                                    const json = parse(event);

                                    if (json?.source !== 'readyplayerme') {
                                        return;
                                    }

                                    // Susbribe to all events sent from Ready Player Me once frame is ready
                                    if (json.eventName === 'v1.frame.ready') {
                                        frame.contentWindow.postMessage(
                                            JSON.stringify({
                                                target: 'readyplayerme',
                                                type: 'subscribe',
                                                eventName: 'v1.**'
                                            }),
                                            '*'
                                        );
                                    }

                                    // Get avatar GLB URL
                                    if (json.eventName === 'v1.avatar.exported') {
                                        console.log(`Avatar URL: ${json.data.url}`);
                                        document.getElementById('avatarUrl').value = `Avatar URL: ${json.data.url}`;
                                        document.getElementById('frame').hidden = true;
                                    }

                                    // Get user id
                                    if (json.eventName === 'v1.user.set') {
                                        console.log(`User with id ${json.data.id} set: ${JSON.stringify(json)}`);
                                    }
                                }

                                function parse(event) {
                                    try {
                                        return JSON.parse(event.data);
                                    } catch (error) {
                                        return null;
                                    }
                                }

                                function displayIframe() {
                                    document.getElementById('frame').hidden = false;
                                }
                            </script>
                            <script>
                                (function() {
                                  function prefillFromLocal() {
                                    try {
                                      var memberUuid = localStorage.getItem('ipnz_member_uuid');
                                      var profileStr = localStorage.getItem('ipnz_member_profile');
                                      if (!memberUuid || !profileStr) return;
                                      var profile = JSON.parse(profileStr);
                                      // Prefill fields
                                      var nameEl = document.getElementById('join-form-name');
                                      var emailEl = document.getElementById('join-form-email');
                                      var phoneEl = document.querySelector('input[name="join-form-phone"]');
                                      var msgEl = document.getElementById('join-form-message');
                                      var avatarEl = document.getElementById('avatarUrl');
                                      var radioEarly = document.getElementById('flexRadioDefault1');
                                      var radioStd = document.getElementById('flexRadioDefault2');
                                      if (nameEl && typeof profile.name === 'string') nameEl.value = profile.name;
                                      if (emailEl && typeof profile.email === 'string') emailEl.value = profile.email;
                                      if (phoneEl && typeof profile.phone === 'string') phoneEl.value = profile.phone;
                                      if (msgEl && typeof profile.additional_request === 'string') msgEl.value = profile.additional_request;
                                      if (avatarEl && typeof profile.avatar_url === 'string') avatarEl.value = (profile.avatar_url || '');
                                      if (profile.join_type === 'standard') {
                                        if (radioStd) radioStd.checked = true;
                                      } else {
                                        if (radioEarly) radioEarly.checked = true;
                                      }
                                      // Disable fields by default (email stays disabled for safety)
                                      ['join-form-name','join-form-message'].forEach(function(id){ var el=document.getElementById(id); if(el){ el.disabled=true; }});
                                      if (phoneEl) phoneEl.disabled = true;
                                      if (avatarEl) avatarEl.disabled = true;
                                      if (radioEarly) radioEarly.disabled = true;
                                      if (radioStd) radioStd.disabled = true;
                                      if (emailEl) emailEl.disabled = true;
                                      // Set hidden member_uuid
                                      var idEl = document.getElementById('member_uuid');
                                      if (idEl) idEl.value = memberUuid;
                                      // Update button label and show edit link
                                      var btn = document.getElementById('submitBtn');
                                      var edit = document.getElementById('editDetails');
                                      if (btn) btn.textContent = 'Update details';
                                      if (edit) edit.style.display = 'inline';
                                    } catch(e) { /* ignore */ }
                                  }
                                  function enableEditing(evt){
                                    evt && evt.preventDefault();
                                    ['join-form-name','join-form-message'].forEach(function(id){ var el=document.getElementById(id); if(el){ el.disabled=false; }});
                                    var phoneEl = document.querySelector('input[name="join-form-phone"]');
                                    var avatarEl = document.getElementById('avatarUrl');
                                    var radioEarly = document.getElementById('flexRadioDefault1');
                                    var radioStd = document.getElementById('flexRadioDefault2');
                                    if (phoneEl) phoneEl.disabled = false;
                                    if (avatarEl) avatarEl.disabled = false;
                                    if (radioEarly) radioEarly.disabled = false;
                                    if (radioStd) radioStd.disabled = false;
                                    var btn = document.getElementById('submitBtn');
                                    if (btn) btn.textContent = 'Save changes';
                                  }
                                  document.addEventListener('DOMContentLoaded', function(){
                                    prefillFromLocal();
                                    var edit = document.getElementById('editDetails');
                                    if (edit) edit.addEventListener('click', enableEditing);
                                  });
                                })();
                            </script>
                                <div class="col-lg-4 col-md-10 col-8 mx-auto">
                                    <input type="hidden" name="member_uuid" id="member_uuid" value="">
                                    <input type="hidden" name="referrer_code" id="referrer_code" value="">
                                    <button type="submit" name="submit" id="submitBtn" class="form-control">Join us</button>
                                    <div class="text-center mt-2">
                                        <a href="#" id="editDetails" class="site-footer-link" style="display:none;">Edit details</a>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
        </section>
    </main>

</script>
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
                            <a href="#" class="site-footer-link">Contact</a>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 col-12 mb-4 mb-lg-0">
                    <h5 class="site-footer-title mb-3">Have a question?</h5>

                    <p class="text-white d-flex mb-1">
                        <a href="tel: 028-2557-8835" class="site-footer-link">
                            +642825578835
                        </a>
                    </p>

                    <p class="text-white d-flex">
                        <a href="mailto:hello-ops@IPnz.live" class="site-footer-link">
                            ops+hello@IPnz.live
                        </a>
                    </p>
                </div>

                <div class="col-lg-3 col-md-6 col-11 mb-4 mb-lg-0 mb-md-0">
                    <h5 class="site-footer-title mb-3">Location</h5>

                    <p class="text-white d-flex mt-3 mb-2">
                        New Zealand</p>

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
    <script src="js/referral.js"></script>
</body>
</html>