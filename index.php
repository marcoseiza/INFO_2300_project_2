<?php include("includes/init.php");?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>DATABASE PROJECT</title>
  <link rel="stylesheet" href="style/header.css">
  <link rel="stylesheet" href="style/main.css">
  <link rel="stylesheet" href="style/eatery-add.css">
  <link rel="stylesheet" href="style/reviews.css">
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
                <option value="price" <?php if ($search_field == "price") {echo "selected";} ?>>Price Rating ($, $$, $$$)</option>
                <option value="food_type" <?php if ($search_field == "food_type") {echo "selected";} ?>>Food Type</option>
                <option value="type" <?php if ($search_field == "type") {echo "selected";} ?>>Eatery Type (Restaurant...)</option>
                <option value="star_rating" <?php if ($search_field == "star_rating") {echo "selected";} ?>>Star Rating</option>
                <option value="hours_open" <?php if ($search_field == "hours_open") {echo "selected";} ?>>Opening Hour</option>
                <option value="hours_close" <?php if ($search_field == "hours_close") {echo "selected";} ?>>Closing Hour</option>
                <option value="address" <?php if ($search_field == "address") {echo "selected";} ?>>Address</option>
                <option value="website" <?php if ($search_field == "website") {echo "selected";} ?>>Website</option>
                <option value="phone" <?php if ($search_field == "phone") {echo "selected";} ?>>Phone</option>
              </select>
              <svg viewBox="0 0 30 30">
                  <path class="search-field-button" fill="none" stroke-width="2" d="
                              M 5 10
                              l 10 10 10 -10
                              ;"/>
              </svg>
          </label>
          <input type="text" class="search-box" name="search" id="search" fill="none" stroke-width="2" placeholder="Search - Search by Field on the Left" value="<?php echo htmlspecialchars($search)?>">
          <label for="submit" class="submit">
            <input type="submit" id="submit" value="" name="search-submit">
            <svg viewBox="0 0 30 30">
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
      <div class="empty"><span>No results, sorry :(  Here's what we have...</span></div>
    <?php }
    foreach ($eateries as $eatery) {
      eatery_post($eatery, $answers = array("id" => $id, "checked" => $review_checked, "name" => $review_name, "star" => $answer_star_rating, "comment" => $review_comment, "feedback" => $review_feedback));}
    ?>
    <input type="checkbox" name="eatery-add" id="eatery-add"
    <?php
    if ($eatery_checked) {
      echo "checked = 'checked'";
    }
    ?>>
    <div class="eatery-add" id="scroll-eatery-add">
      <label for="eatery-add">
        <svg viewBox="0 0 30 30">
          <path fill="none" stroke-width="2" d="
              M 15 22
              v -14
              M 8 15
              h 14
              ;"/>
        </svg>
      </label>
      <form method="post" action="index.php#eatery-add-form" id="eatery-add-form">
        <h2>Insert an Eatery!</h2>
        <div class="eatery-name">
          <label for="eatery-name">Name of the eatery: <span class="required">*</span></label>
          <input type="text" name="eatery-name" id="eatery-name" <?php echo "class='" . $eatery_feedback["name"] . "'"?>
          value="<?php echo htmlspecialchars($eatery_name)?>" required>
        </div>
        <div class="eatery-price">
          <div class="eatery-price-range">
            <input type="radio" name="eatery-price" id="$$$" value="$$$" required <?php if ($eatery_price == "$$$") {echo 'checked=\"checked\"';}?>>
            <label for="$$$">$$$</label>
            <input type="radio" name="eatery-price" id="$$" value="$$" <?php if ($eatery_price == "$$") {echo 'checked=\"checked\"';}?>>
            <label for="$$">$$</label>
            <input type="radio" name="eatery-price" id="$" value="$" <?php if ($eatery_price == "$") {echo 'checked=\"checked\"';}?>>
            <label for="$">$</label>
          </div>
          <h2>Price Rating: <span class="required">*</span></h2>
        </div>
        <div class="eatery-food-type">
          <label for="eatery-food-type">What do they serve there? <span class="required">*</span>
<span class="example">Ex: American Food, Pho Noodles, etc...</span></label>
          <input type="text" name="eatery-food-type" id="eatery-food-type" <?php echo "class='" . $eatery_feedback["food_type"] . "'"?>
          value="<?php echo htmlspecialchars($eatery_food_type)?>" required>
        </div>
        <div class="eatery-type">
          <label for="eatery-type">What kind of eatery is it? <span class="required">*</span>
<span class="example">Ex: Restaurant, Deli, Coffee Shop, etc...</span></label>
          <input type="text" name="eatery-type" id="eatery-type" <?php echo "class='" . $eatery_feedback["type"] . "'"?>
          value="<?php echo htmlspecialchars($eatery_type)?>" required>
        </div>
        <div class="eatery-hours">
          <div class="eatery-hours-open">
            <label for="eatery-hours-open">Opening Hour <span class="required">*</span>
<span class="example">Ex: 06:30 AM</span></label>
            <input type="time" name="eatery-hours-open" id="eatery-hours-open"
          value="<?php echo htmlspecialchars($eatery_hours_open)?>" required>
          </div>
          <div class="eatery-hours-close">
            <label for="eatery-hours-close">Closing hour <span class="required">*</span>
<span class="example">Ex: 10:30 PM</span></label>
            <input type="time" name="eatery-hours-close" id="eatery-hours-close"
          value="<?php echo htmlspecialchars($eatery_hours_close)?>" required>
          </div>
        </div>
        <div class="eatery-address">
          <label for="eatery-address">Address: Street, City State, ZIP Code <span class="required">*</span>
<span class="example">Ex: 120 Dryden Rd, Ithaca NY, 14850</span></label>
          <div>
            <input type="text" name="eatery-address-street" id="eatery-address-street" <?php echo "class='" . $eatery_feedback["address_street"] . "'"?>
            value="<?php echo htmlspecialchars($eatery_address["street"])?>" placeholder="Street" required>
            <input type="text" name="eatery-address-city" id="eatery-address-city" <?php echo "class='" . $eatery_feedback["address_city"] . "'"?>
            value="<?php echo htmlspecialchars($eatery_address["city"])?>" placeholder="City" required>
            <input type="text" name="eatery-address-state" id="eatery-address-state" <?php echo "class='" . $eatery_feedback["address_state"] . "'"?>
            value="<?php echo htmlspecialchars($eatery_address["state"])?>" placeholder="State" required>
            <input type="text" name="eatery-address-zip" id="eatery-address-zip" <?php echo "class='" . $eatery_feedback["address_zip"] . "'"?>
            value="<?php echo htmlspecialchars($eatery_address["zip"])?>" placeholder="ZIP Code" required>
          </div>
        </div>
        <h3>Extra Info:</h3>
        <span></span>
        <div class="eatery-phone">
          <label for="eatery-phone">Phone Number
<span class="example">Ex: +1(999)999-9999</span></label>
          <input type="tel" name="eatery-phone" id="eatery-phone" value="<?php echo htmlspecialchars($eatery_phone)?>"
          <?php echo "class='" . $eatery_feedback["phone"] . "'"?>>
        </div>
        <div class="eatery-website">
          <label for="eatery-website">Website</label>
          <input type="url" name="eatery-website" id="eatery-website"
          value="<?php echo htmlspecialchars($eatery_website)?>" <?php echo "class='" . $eatery_feedback["website"] . "'"?>>
        </div>
        <label for="eatery-submit">Submit<span>&#8594;</span><input type="submit" name="eatery-submit" id="eatery-submit"></label>
        <span class="required-message">All fields with <span class="required">*</span> are required</span>
      </form>
    </div>
  </main>
</body>

</html>
