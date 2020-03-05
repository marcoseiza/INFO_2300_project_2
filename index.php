<?php include("includes/init.php");?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>TODO</title>
  <link rel="stylesheet" href="style/header.css">
  <link rel="stylesheet" href="style/main.css">
</head>

<body>
  <header>
    <div class="search">
      <form method="post" action="index.php">
          <label class="search-field" for="search-field">
              <select name="search-field" id="search-field">
                <option value="" disabled selected>Type</option>
                <option value="none" <?php if ($search_field == "none") {echo "selected";} ?>>None</option>
                <option value="restaurant" <?php if ($search_field == "restaurant") {echo "selected";} ?>>Restaurant</option>
                <option value="coffee" <?php if ($search_field == "coffee") {echo "selected";} ?>>Coffee</option>
                <option value="deli" <?php if ($search_field == "deli") {echo "selected";} ?>>Deli</option>
              </select>
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30">
                  <path class="search-field-button" fill="none" stroke-width="2" d="
                              M 5 10
                              l 10 10 10 -10
                              ;"/>
              </svg>
          </label>
          <input type="text" class="search-box" name="search" id="search" fill="none" stroke-width="2" placeholder="Search By Name" value="<?php echo htmlspecialchars($search)?>">
          <label for="submit" class="submit">
            <input type="submit" id="submit" value="">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30">
                <path class="upload-bottom" fill="none" stroke-width="2" d="
                            M 10 12
                            a 8 8 0 1 0 16 0
                            a 8 8 0 1 0 -16 0
                            h -10
                            ;"/>
            </svg>
          </label>
      </form>
    </div>
  </header>
  <main>
    <?php
    if ($empty) { ?>
      <div class="empty"><span>There are no results with that name and/or type</span></div>
    <?php }
    foreach ($eateries as $eatery) {
      eatery_post($eatery);}
    ?>
  </main>
</body>

</html>
