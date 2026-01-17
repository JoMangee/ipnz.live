<?php
require('database.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Something posted

    if (isset($_POST["submit"])) {
        $name = $connection -> real_escape_string(trim($_POST['join-form-name']));
        $email = $connection -> real_escape_string(trim($_POST['join-form-email']));
        $phone = $connection -> real_escape_string(trim($_POST['join-form-phone']));
        $addrqst = $connection -> real_escape_string(trim($_POST['join-form-message']));
        $avatarUrl = $connection -> real_escape_string(trim($_POST['avatarUrl']));
    
        // $name = $_POST['name'];
        // $position = $_POST['position'];
        // $companyname = $_POST['companyname'];
        // $phone = $_POST['phone'];
        // $email = $_POST['email'];
        // $existingorback = $_POST['existingorback'];
        // $ifoutsource = $_POST['ifoutsource'];             

        if ($avatarUrl <> '') {
            $queryClientIfExists = "SELECT * FROM ipnz_members where email = '$email'";
            $sqlClientIfExists = mysqli_query($connection, $queryClientIfExists);
            // // $str = "";
            if (mysqli_num_rows($sqlClientIfExists) == 0) {

                // if (isset($_POST['flexRadioDefault1'])) {
                    $queryCreateClient = "INSERT INTO ipnz_members (`name`, `email`,`phone`, `additional_request`, `avatar_url`) VALUES ('$name', '$email', '$phone', '$addrqst', '$avatarUrl'); ";
                // }else {
                //     $queryCreateClient = "INSERT INTO ipnz_members (`name`, `email`,`phone`, `join_type`, `additional_request`) VALUES ('$name', '$email', '$phone', 1,  $addrqst; ";
                // }
                

                $sqlCreateClient = mysqli_multi_query($connection, $queryCreateClient);
                echo '<div align="center"><div class="alert alert-primary" role="alert" style="background-color: #4CAF50; border: 1px solid #aaa; color:white; text-align:center; width:100%; border-radius:0px; margin-top:20px; font-family: "GothamPro", sans-serif;">Sign up Success! Thank you.</div></div>';
            }else{
                echo '<div align="center mt-30"><div class="alert alert-primary" role="alert" style="background-color: #ea0309; border: 1px solid #ea0309; color:white; text-align:center; width:100%; border-radius:0px; margin-top:20px; font-family: "GothamPro", sans-serif;">Sign up Failed! Email already exists.</div></div>';  
            }
        }else{
                echo '<div align="center mt-30"><div class="alert alert-primary" role="alert" style="background-color: #ea0309; border: 1px solid #ea0309; color:white; text-align:center; width:100%; border-radius:0px; margin-top:20px; font-family: "GothamPro", sans-serif;">Sign up Failed! Please select your avatar.</div></div>';  
        }

        // echo "<strong>Client Details</strong>
        // <dl>
        // <dt>Name</dt><dd>{$name}</dd>                                    
        // <dt>Position</dt><dd>{$position}</dd>                                     
        // <dt>Company Name</dt><dd>{$companyname}</dd>                                     
        // <dt>Phone</dt><dd>{$phone}</dd>                                    
        // <dt>Email</dt><dd>{$email}</dd>                                    
        // <dt>Existing Customer Service or Back Office?</dt><dd>{$existingorback}</dd>                                    
        // <dt>If Outsource?</dt><dd>{$ifoutsource}</dd>
        // </dl>";
        // echo '<div align="center"><div class="alert alert-primary" role="alert" style="background-color: #ea0309; border: 1px solid #ea0309; color:white; text-align:center; width:100%; border-radius:0px; margin-top:20px; font-family: "GothamPro", sans-serif;">Sign up Successful! Thank you.</div></div>';
    } 
        
}
?>