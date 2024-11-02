<?php

if (isset($_GET['page'])) {
    $actions = array('config.php', 'functions.php');
    $page = $_GET['page'];
    if (! in_array($page, $actions)){
     $page = 'error';}
    
    include($page);}

   include('config.php');





   
// Function to validate cookie and retrieve user information
function validateCookie($db){
    $loginid = $_COOKIE['loggedin'];

    if(!isset($_COOKIE['loggedin'])) {
        header("Location: index.php");
        exit;
    }

       // Prevent SQL injection by binding parameters
       $stmt = $db->prepare("SELECT * FROM users WHERE loginid = :loginid");
       $stmt->bindParam(':loginid', $loginid, PDO::PARAM_STR);
       $stmt->execute();
       $user = $stmt->fetch(PDO::FETCH_ASSOC);
   
       if(!$user) {
           header("Location: index.php");
           exit;
       }
   
       return $user;
   }



   function isMemberOfGroup($db, $loginid, $group_id) {
    $stmt = $db->prepare("SELECT * FROM group_members WHERE loginid = :loginid AND group_id = :group_id");
    $stmt->bindParam(':loginid', $loginid, PDO::PARAM_STR);
    $stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
    $stmt->execute();
    return ($stmt->rowCount() > 0);
}


//as the db variable of include('config.php'); is outside the scope of function, so we need pass parameter
function retrieve($db, $loginid) {
    $stmt = $db->prepare("SELECT * FROM users WHERE loginid = :loginid");
    $stmt->bindValue(':loginid', $loginid, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user;
  }
  

?>