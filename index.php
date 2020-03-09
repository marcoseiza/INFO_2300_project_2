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
                <option value="" disabled selected>Field</option>
                <option value="none" <?php if ($search_field == "none") {echo "selected";} ?>>None</option>
                <option value="name" <?php if ($search_field == "name") {echo "selected";} ?>>Name</option>
                <option value="hours_open" <?php if ($search_field == "hours_open") {echo "selected";} ?>>Opening Hour</option>
                <option value="hours_close" <?php if ($search_field == "hours_close") {echo "selected";} ?>>Closing Hour</option>
                <option value="address" <?php if ($search_field == "address") {echo "selected";} ?>>Address</option>
                <option value="website" <?php if ($search_field == "website") {echo "selected";} ?>>Website</option>
                <option value="phone" <?php if ($search_field == "phone") {echo "selected";} ?>>Phone</option>
                <option value="star_rating" <?php if ($search_field == "star_rating") {echo "selected";} ?>>Star Rating</option>
                <option value="type" <?php if ($search_field == "type") {echo "selected";} ?>>Food Type</option>
              </select>
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30">
                  <path class="search-field-button" fill="none" stroke-width="2" d="
                              M 5 10
                              l 10 10 10 -10
                              ;"/>
              </svg>
          </label>
          <input type="text" class="search-box" name="search" id="search" fill="none" stroke-width="2" placeholder="Search By Name - Leave Empty to Sort by Field" value="<?php echo htmlspecialchars($search)?>">
          <label for="submit" class="submit">
            <input type="submit" id="submit" value="" name="search-submit">
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
      eatery_post($eatery, $answers = array("id" => $id, "checked" => $checked, "name" => $name, "star" => $star_rating, "comment" => $comment));}
    ?>
  </main>
</body>

</html>
