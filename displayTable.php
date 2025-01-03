<?php
session_start();
include 'connection.php'; 
$useremail = $_SESSION['email'];
echo $useremail;

//sets number of rows to display in a page
$start = 0;
$rowsPerPage = 4;

//get the total number of rows
$records = $conn->query("SELECT * FROM `tablelistfingerprintenrolled` WHERE `email` = '$useremail'");
$numOfRows = $records->num_rows;
// $pageVisible = 5;
// for pagination show number of pages
$pageVisible = min(1 + floor($numOfRows / 5), 5);

//calculating the number of rows each pages.
$pages = ceil($numOfRows / $rowsPerPage);

//if the user clicks on the pagination buttons, we set a new starting point.
if (isset($_GET['page-nr'])){
    $page = $_GET['page-nr'] - 1;
    $start = $page * $rowsPerPage;
}

// echo'
//     <script>
//         alert("clicked on username : '.$numOfRows.'");
//         window.location.href="userlist.php";
//     </script>';
//     exit();
$pageMiddle = ceil($pageVisible / 2 ) ;

$result = $conn->query("SELECT * FROM `tablelistfingerprintenrolled` WHERE `email` = '$useremail' LIMIT $start,$rowsPerPage");

if(isset($_GET['searchbar'])){
    $searchthis = "%".strval($_GET['searchbar'])."%";
    $records = $conn->query("SELECT * FROM `tablelistfingerprintenrolled` WHERE `email` = '$useremail' AND `name` LIKE '$searchthis'");
    $numOfRows = $records->num_rows;
    $pageVisible = min(1 + floor($numOfRows / 5), 5);
    //calculating the number of rows each pages.
    $pages = ceil($numOfRows / $rowsPerPage);
    if($pageVisible > $pages) $pageVisible = $pages;
    // echo'
    // <script>
    //     alert("clicked on username : '.$pages.'");
    //     window.location.href="userlist.php";
    // </script>';
    // exit();
    $result = $conn->query("SELECT * FROM `tablelistfingerprintenrolled`  WHERE `email` = '$useremail' AND `name` LIKE '$searchthis' LIMIT $start,$rowsPerPage");
}
?>