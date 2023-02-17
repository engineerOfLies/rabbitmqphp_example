<?php
session_start();
include(__DIR__ . "/../lib/helpers.php");
require_once(__DIR__ . "/components/navbar.php");
$data = false;
$username = "";
if (isset($_GET['id'])) {
    $sendID = $_GET["id"];
    // $data = array('type' => 'fetch', 'data' => array('id' => $sendID));
    // $result = send($data, "dmz");
    // TO SAVE FETCHING FROM DATABASE WHEN DEVELOPING LOCALLY
    $result = array("code" => 0, "message" => array("Title" => "Harry Potter and the Deathly Hallows: Part 2", "Year" => "2011", "Rated" => "PG-13", "Released" => "15 Jul 2011", "Runtime" => "130 min", "Genre" => "Adventure, Family, Fantasy", "Director" => "David Yates", "Writer" => "Steve Kloves, J.K. Rowling", "Actors" => "Daniel Radcliffe, Emma Watson, Rupert Grint", "Plot" => "Harry Daniel Radcliffe, Ron Rupert Grint, and Hermione Emma Watson continue their quest of finding and destroying Voldemort's Ralph Fiennes' three remaining Horcruxes, the magical items responsible for his immortality. But as the mystical Deathly Hallows are uncovered, and Voldemort finds out about their mission, the biggest battle begins, and life as they know it will never be the same again.", "Language" => "English, Latin", "Country" => "United Kingdom, United States", "Awards" => "Nominated for 3 Oscars. 47 wins & 94 nominations total", "Poster" => "https://m.media-amazon.com/images/M/MV5BMGVmMWNiMDktYjQ0Mi00MWIxLTk0N2UtN2ZlYTdkN2IzNDNlXkEyXkFqcGdeQXVyODE5NzE3OTE@._V1_SX300.jpg"));
        $data = $result["message"];
    // if ($code == 0) {
    //     $data = $result["message"];
    // }
    echo $data['id'];
}

if(isset($_SESSION["username"])) {
    $username =  $_SESSION["username"];
}   
?>

<html>

<head>
    <script>
        // IIFE
    </script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #272727;
            color: white;
        }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body>
    <div class="container mx-auto grid place-items-center">
        <!-- Checks if an 'id' parameter is given -->
        <?php if ($_GET["id"]) { ?>
            <div class=" space-y-2 flex gap-12 max-w-[1200px]">
                <img src="<?php
                            echo $data["Poster"];

                            ?>" class="mx-auto max-w-[500px]" />
                <div class="flex-grow space-y-4">
                    <p class="uppercase text-xs"><?php echo $data["Type"] ?></p>
                    <h1 class="text-4xl  font-bold"><?php echo $data["Title"] ?></h1>
                    <div class="text-sm space-y-2">
                        <p class=" leading-loose text-white"><?php echo $data["Year"] ?></p>
                        <ul class="flex items-center space-x-2">
                            <?php
                            $genres = explode(",", $data["Genre"]);
                            for ($i = 0; $i < count($genres); $i++) { ?>
                                <li><?php echo $genres[$i] ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                    <hr class="" />

                    <h4 class="text-xl font-bold text-white">Summary</h4>
                    <p class="text leading-loose text-neutral-300"><?php echo $data["Plot"] ?></p>

                    <div>
                        <button id="bookmark" class="bg-blue-700 p-3 rounded">Bookmark</button>
                    </div>

                </div>
            </div>
        <?php } else {
            echo "<h1>Page not Found.</h1>";
        } ?>
        <input hidden id="username" value="<?php echo $username?>" />
    </div>

    <script>
        const btn = document.getElementById("bookmark");
        const input = document.getElementById("isBookmarked");
        
        (function checkBookmark() {

            const params = new Proxy(new URLSearchParams(window.location.search), {
                get: (seachParams, prop) => seachParams.get(prop)
            });
            const id = params.id;
            const req = new XMLHttpRequest();
            const username = document.getElementById("username").value;
            req.open("POST", "functions/checkBookmark.php", true);
            req.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
            req.onprogress = () => {
                btn.innerText = "Loading...";
             
            }

            req.onload = () => {
                if (req.readyState == 4 && req.status == 200) {

                    console.log(req.responseText);
                    const res = JSON.parse(req.responseText);
                    if (res.message == true) {
                        btn.innerText = "Remove bookmark";
                        btn.classList.remove("bg-blue-700");
                        btn.classList.add("bg-orange-700");
                        btn.classList.add("hover:bg-orange-600");
                        btn.classList.add("transition-colors");
                        
                    } else {
                        btn.innerText = "Bookmark";
                    }
                   
                }
            }
            const reqBody = {
                movie_id: id,
                username,
                
            }

            req.send(JSON.stringify(reqBody));
        })();

        // TODO: Add request to bookmark item
        const handleClick = () => {
            console.log(Boolean(btn.dataset.status));
        

        }
        btn.addEventListener("click", handleClick)
    </script>
</body>

</html>