<?php
session_start();
include(__DIR__ . "/../lib/helpers.php");
require_once(__DIR__ . "/components/navbar.php");
$data = false;
if (isset($_GET['id'])) {
    $sendID = $_GET["id"];
    $data = array('type' => 'fetch', 'data' => array('id' => $sendID));
    $result = send($data, "dmz");
    if ($code == 0) {
        $data = $result["message"];
    }
    echo $data['id'];
}
?>

<html>

<head>
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
                        <button class="bg-blue-700 p-3 rounded">Bookmark</button>
                    </div>

                </div>
            </div>
        <?php } else {
            echo "<h1>Page not Found.</h1>";
        } ?>
    </div>
</body>

</html>