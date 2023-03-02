<html>

<head>
<style>
    #show {
        display: none;
    }
</style>

</head>

<body>

<p class="addRating" onclick="showShow()"> Add Rating </p>

<div id="show">
<form method="post">
      <input class="searchInput" id="rating" name="rating" autocomplete="off">
        <button type="submit" name="addRating" class=""> Add Rating </button>
      </div>
      </form>
</div>

<script>
function showShow() {
    document.getElementById("show").style.display = "block";
}

</script>
</body>

</html>