<?php
session_start();
if(isset($_SESSION["username"])) {
echo "<p style=
'color: white !important;
text-transform: uppercase;'
>
Welcome " . $_SESSION["username"]
 . "</p";
}
  include(__DIR__ . "/../lib/helpers.php");



$id = $_POST['id'];

// tt3896198 id
// f3d054e8 key
if(isset($_POST['submit'])) {
  $data = array('type' => 'api', 'data' => array('id' => $id));
  $response = sendAPI($data, "rabbit");
  var_dump($response);
}

?>
<!DOCTYPE html>
<html>
    <head>
    </head>
    <body>
    <form method="post">
    <div class="searchBox">
    <input class="searchInput" type="search" id="id" name="id" placeholder="Movie Title">
      <button type="submit" name="submit" class="searchButton"> Search </button>
    </div>
    </form>
    <!-- <div class="form">
      <form method="post">
        <input type="search" name="id" id="id" placeholder="Search" required/>
        <button type="submit" name="submit">get data</button>
      </form>
    </div> -->
    </body>
</html>

<style>


body {
  background: #000000;  /* fallback for old browsers */
  background: -webkit-linear-gradient(to right, #434343, #000000);  /* Chrome 10-25, Safari 5.1-6 */
  background: linear-gradient(to right, #434343, #000000); /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */

}

.searchBox {
    position: absolute;
    top: 0;
    right: 0;
    background: #2f3640;
    height: 40px;
    border-radius: 40px;
    padding: 10px;

}

.searchBox:hover > .searchInput {
    width: 240px;
    padding: 0 6px;
}

.searchBox:hover > .searchButton {
  color : white;
}

.searchButton {
    color: white;
    float: right;
    width: 40px;
    height: 40px;
    border: transparent;
    background: #2f3640;
    display: flex;
    justify-content: center;
    align-items: center;
    transition: 0.4s;
}

.searchInput {
    border:none;
    background: none;
    outline:none;
    float:left;
    padding: 0;
    color: white;
    font-size: 12px;
    transition: 0.4s;
    line-height: 40px;
    width: 0px;

}

@media screen and (max-width: 620px) {
.searchBox:hover > .searchInput {
    width: 150px;
    padding: 0 6px;
}


</style>
