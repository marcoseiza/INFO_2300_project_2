<?php
// check current php version to ensure it meets 2300's requirements
function check_php_version()
{
  if (version_compare(phpversion(), '7.0', '<')) {
    define('VERSION_MESSAGE', "PHP version 7.0 or higher is required for 2300. Make sure you have installed PHP 7 on your computer and have set the correct PHP path in VS Code.");
    echo VERSION_MESSAGE;
    throw VERSION_MESSAGE;
  }
}
check_php_version();

function config_php_errors()
{
  ini_set('display_startup_errors', 1);
  ini_set('display_errors', 0);
  error_reporting(E_ALL);
}
config_php_errors();

// Open a connection to an SQLite database stored in filename: $db_filename.
// Returns: Connection to database.
function open_sqlite_db($db_filename)
{
    $db = new PDO('sqlite:' . $db_filename);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
}

// Execute a query ($sql) against a datbase ($db).
// Returns query results if query was successful.
// Returns null if query was not successful.
function exec_sql_query($db, $sql, $params = array())
{
  $query = $db->prepare($sql);
  if ($query and $query->execute($params)) {
    return $query;
  }
  return null;
}

$eateries_db = open_sqlite_db("secure/eateries.sqlite");
// $reviews = exec_sql_query($eateries_db, "SELECT * FROM reviews WHERE (eatery_id = :id)", )->fetchAll();
$eateries = exec_sql_query($eateries_db, "SELECT * FROM eateries")->fetchAll();

$review_checked = $review_name = $review_comment = "";
$eatery_name = $eatery_price = $eatery_type = $eatery_food_type = $eatery_address = $eatery_hours_open = $eatery_hours_close = $eatery_checked = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_POST["eatery-submit"])){

    $eatery_name = $_POST["eatery-name"];
    $eatery_price = $_POST["eatery-price"];
    $eatery_type = $_POST["eatery-type"];
    $eatery_food_type = $_POST["eatery-food-type"];
    $eatery_address = array(
                  "street" => $_POST["eatery-address-street"],
                  "city" => $_POST["eatery-address-city"],
                  "state" => $_POST["eatery-address-state"],
                  "zip" => $_POST["eatery-address-zip"]);
    $eatery_hours_open = $_POST["eatery-hours-open"];
    $eatery_hours_close = $_POST["eatery-hours-close"];
    $eatery_website = $_POST["eatery-website"];
    $eatery_phone = $_POST["eatery-phone"];
    $eatery_feedback = array(
                  "name" => "",
                  "type" => "",
                  "food_type" => "",
                  "address" => "",
                  "website" => "",
                  "phone" => ""
                );

    if (!preg_match("/^[a-z ,.'-]+$/i", $eatery_name)) {
      $eatery_checked = "checked";
      $eatery_feedback["name"] = "invalid";
    }

    if (empty($eatery_price)) {
      $eatery_checked = "checked";
    }

    if (!preg_match("/^[a-z ,.'-]+$/i", $eatery_type)) {
      $eatery_checked = "checked";
      $eatery_feedback["type"] = "invalid";
    }

    if (!preg_match("/^[a-z ,.&'-]+$/i", $eatery_food_type)) {
      $eatery_checked = "checked";
      $eatery_feedback["food_type"] = "invalid";
    }

    if (!preg_match("/^\d+\s[a-z ,.&'-]+\s[a-z]+$/i", $eatery_address["street"])) {
      $eatery_checked = "checked";
      $eatery_feedback["address_street"] = "invalid";
    }

    if (!preg_match("/^[a-z ,.&'-]+$/i", $eatery_address["city"])) {
      $eatery_checked = "checked";
      $eatery_feedback["address_city"] = "invalid";
    }

    if (!preg_match("/^[A-Z]{2}$/", $eatery_address["state"])) {
      $eatery_checked = "checked";
      $eatery_feedback["address_state"] = "invalid";
    }

    if (!preg_match("/^\d{5}$/", $eatery_address["zip"])) {
      $eatery_checked = "checked";
      $eatery_feedback["address_zip"] = "invalid";
    }

    if (empty($eatery_hours_open)) {
      $eatery_checked = "checked";
    }

    if (empty($eatery_hours_close)) {
      $eatery_checked = "checked";
    }

    if (!empty($eatery_website) && !filter_var($eatery_website, FILTER_VALIDATE_URL)) {
      $eatery_feedback["website"] = "invalid";
      $eatery_checked = "checked";
    } else {
      $eatery_website = filter_var($eatery_website, FILTER_SANITIZE_URL);
    }

    if (!preg_match("/^(?:\+?([1-9]{1,3}))?[- (]?([0-9]{3})[- )]?([0-9]{3})[- ]?([0-9]{4})$/", $eatery_phone) && !empty($eatery_phone)) {
      $eatery_checked = "checked";
      $eatery_feedback["phone"] = "invalid";
    }


    if (empty($eatery_checked)) {
      $eatery_address = $eatery_address["street"] . ", " . $eatery_address["city"] . " " . $eatery_address["state"] . ", " . $eatery_address["zip"];
      $eatery_hours_open = date("g:i A", strtotime($eatery_hours_open));
      $eatery_hours_close = date("g:i A", strtotime($eatery_hours_close));
      exec_sql_query($eateries_db,
      "INSERT INTO eateries (name, type, hours_open, hours_close, address, website, phone, food_type, price) VALUES (:name, :type, :hours_open, :hours_close, :address, :website, :phone, :food_type, :price)",
      array(
        ":name" => $eatery_name,
        ":type" => $eatery_type,
        ":hours_open" => $eatery_hours_open,
        ":hours_close" => $eatery_hours_close,
        ":address" => $eatery_address,
        ":website" => $eatery_website,
        ":phone" => $eatery_phone,
        ":food_type" => $eatery_food_type,
        ":price" => $eatery_price));

      $eateries = exec_sql_query($eateries_db, "SELECT * FROM eateries")->fetchAll();
    }

  } elseif (isset($_POST["review-submit"])) {
    $id = substr($_POST["review-submit"], -1);
    $answer_star_rating = $_POST["review-star-rating-" . $id];

    if (empty($answer_star_rating)) {
      $review_checked = "checked";
    }

    if (empty($review_checked)) {

      $reviews_data = exec_sql_query($eateries_db,
                        "SELECT number_reviews, star_rating FROM eateries WHERE id = :id",
                        array(":id" => $id))->fetchAll();


      $number_reviews = $reviews_data[0]["number_reviews"]; $star_rating = $reviews_data[0]["star_rating"];

      $star_rating = (($star_rating * $number_reviews) + $answer_star_rating) / ($number_reviews + 1);
      $number_reviews += 1;

      exec_sql_query($eateries_db,
                "UPDATE eateries SET number_reviews = " . $number_reviews . ", star_rating = " . $star_rating ." WHERE id = :id;",
                  array(":id" => $id));

      $review_name = $review_comment = "";
      $answer_star_rating = "";

      $eateries = exec_sql_query($eateries_db, "SELECT * FROM eateries")->fetchAll();
    }
  }
}

$search = $search_field = "";
$empty = FALSE;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_POST['search-submit'])) {
    $search = $_POST["search"];
    $search_field = $_POST["search-field"];
    if (!empty($search_field) && $search_field !== "none" && $search_field !== "") {
      $search_sql = "SELECT * FROM eateries WHERE " . $search_field . " like '%'||:search||'%'";
      if ($search_field == "price") {
        $search_sql = "SELECT * FROM eateries WHERE price = :search";
      }
      $params = array(":search" => $search);
      if (empty($search)) {
        $search_sql = "SELECT * FROM eateries";
        $params = array();
        $empty = TRUE;
      }
    } elseif ($search_field == "none" || $search_field == "") {
      $search_sql = "SELECT * FROM eateries WHERE
      name like '%'||:search||'%' or
      type like '%'||:search||'%' or
      food_type like '%'||:search||'%' or
      hours_open like '%'||:search||'%' or
      hours_close like '%'||:search||'%' or
      address like '%'||:search||'%' or
      website like '%'||:search||'%' or
      phone like '%'||:search||'%' or
      price = :search or
      star_rating like '%'||:search||'%'";
      $params = array(":search" => $search);
    } elseif (empty($search_field)) {
      $search_sql = "SELECT * FROM eateries";
      $params = array();
    }
    $eateries = exec_sql_query($eateries_db, $search_sql, $params)->fetchAll();
    if (empty($eateries)) {
      $eateries = exec_sql_query($eateries_db, "SELECT * FROM eateries")->fetchAll();
      $empty = TRUE;
    }
  }
}


function eatery_post($eatery, $answers) {?>
  <span class="anchor" id="anchor-<?php echo htmlspecialchars($eatery["id"])?>"></span>
  <div class="eatery" id="eatery-<?php echo htmlspecialchars($eatery["id"])?>">
      <div class="img">
        <?php
        if ($eatery["type"] == "restaurant") {?>
        <svg viewBox="0 0 31 31">
          <circle cx="15" cy="15" r="15"/>
          <path fill="white" stroke="white" stroke-width="1.6" d="
            M 11 23
            v -16
            c 2 -1 2 10 0 10
            z
            ;"/>
          <path fill="none" stroke="white" stroke-width="1" d="
            M 18.5 23
            v -16
            v 7
            h 2
            v -7
            m -2 7
            h -2
            v -7
            m 1.7 7
            v 9
            m 0.6 -9
            v 9
            ;"/>
        </svg>
        <?php }
        else if (preg_match("/(coffee)/i", $eatery["type"]) || preg_match("/(cafe)/i", $eatery["type"])) {?>
        <svg viewBox="0 0 31 31">
          <circle cx="15" cy="15" r="15"/>
          <path fill="none" stroke="white" stroke-width="1.5" d="
            M 20.3 16
            a 6 6 0 0 1 -13 0
            v -6
            h 13
            z
            ;"/>
          <path fill="none" stroke="white" stroke-width="1.5" d="
            M 7.3 10
            h 14
            a 3 3 0 0 1 0 6
            h -1
            v -6
            ;"/>
        </svg>
        <?php }
        else if ($eatery["type"] == "deli") { ?>
        <svg viewBox="0 0 31 31">
          <circle cx="15" cy="15" r="15"/>
          <path fill="none" stroke="white" stroke-width="1.5" d="
            M 11 6
            l 13.5 13.5
              -1.5 1.5
              -13.5 -13.5
              1.5 -1.5
              -3 3
              13.5 13.5
              1.5 -1.5
              -1.5 1.5
            h -13.5
            v -13.5
            ;"/>
          <path fill="none" stroke="white" stroke-width="1" d="
            M 8 22.5
            m 3 -3
            l 2 -2
            M 8 22.5
            m 5.5 -2.2
            l 1 -1
            M 8 22.5
            m 2.3 -5.5
            l 1 -1
            ;"/>
        </svg>
        <?php } else {?>
        <svg viewBox="0 0 31 31">
          <circle cx="15" cy="15" r="15"/>
          <path fill="white" stroke="white" stroke-width="1.6" d="
            M 11 23
            v -16
            c 2 -1 2 10 0 10
            z
            ;"/>
          <path fill="none" stroke="white" stroke-width="1" d="
            M 18.5 23
            v -16
            v 7
            h 2
            v -7
            m -2 7
            h -2
            v -7
            m 1.7 7
            v 9
            m 0.6 -9
            v 9
            ;"/>
        </svg>
        <?php } ?>
      </div>
      <div class="name">
        <h1><?php echo htmlspecialchars($eatery["name"])?></h1>
      </div>
      <div class="type">
          <h2><span><?php echo htmlspecialchars($eatery["price"])?></span> - <?php echo ucfirst(htmlspecialchars($eatery["food_type"]))?> / <?php echo ucfirst(htmlspecialchars($eatery["type"]))?></h2>
        </div>
      <div class="hours">
        <h2>Hours:</h2>
        <p><span><?php echo htmlspecialchars($eatery["hours_open"])?></span> to <span><?php echo htmlspecialchars($eatery["hours_close"])?></span></p>
        <?php
          date_default_timezone_set("America/New_York");
          $current_time = DateTime::createFromFormat('H:i A', date('h:i A', time()));
          $close_time = date("Y-m-d") . " " . $eatery["hours_close"];
          if(strpos($close_time, 'AM') !== false) {
            $close_time = date("Y-m-") . strval((date("d") + 1)) . " " . $eatery["hours_close"];
          }
          $close_time = DateTime::createFromFormat('Y-m-d H:i A', $close_time);
          $open_time = DateTime::createFromFormat('H:i A', $eatery["hours_open"]);

          $state = "closed";
          if ($current_time > $open_time && $current_time < $close_time) {
            $state = "open";
          }

        ?>
        <span class="<?php echo strtolower($state);?>"><?php echo strtoupper($state);?></span>
      </div>
      <div class="address">
        <h2>Address:</h2>
        <a href="<?php echo "https://www.google.com/maps/search/?api=1&query=" . urldecode($eatery["address"])?>" target="_blank"><?php echo htmlspecialchars($eatery["address"])?></a>
        <!-- url encoding google maps found in google's devoper guide
            https://developers.google.com/maps/documentation/urls/guide -->
        <!-- This is the format...
          https://www.google.com/maps/search/?api=1&parameter -->
      </div>
      <?php
          if ($eatery["website"]) {
            if (preg_match("/^(https?:\/\/www)/", $eatery["website"])) {
              $website = str_replace("http://", "", $eatery["website"]);
              $website = str_replace("https://", "", $eatery["website"]);
              $websiteLink = $eatery["website"];
            } elseif (preg_match("/^(www)/", $eatery["website"])) {
              $website = $eatery["website"];
              $websiteLink = "http://" . htmlspecialchars($eatery["website"]);
            } else {
              $websiteLink = "http://www." . htmlspecialchars($eatery["website"]);
              $website = "www." . htmlspecialchars($eatery["website"]);
            }
        ?>
        <div class="website">
          <h2>Website:</h2>
          <a href="<?php echo $websiteLink?>" target="_blank"><?php echo $website?></a>
        </div>
      <?php }?>
      <?php if ($eatery["phone"]) { ?>
        <div class="phone">
          <h2>Phone:</h2>
          <p><?php echo htmlspecialchars($eatery["phone"])?></p>
        </div>
      <?php }?>
      <div class="star-rating">
        <h2>Rating:</h2>
        <div class="rating-container">
          <div class="rating" style="--rating-percent: <?php $rating = ($eatery["star_rating"] * 100) / 5; echo htmlspecialchars($rating)?>%">
            <span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span>
          </div>
          <span>&#9734;</span><span>&#9734;</span><span>&#9734;</span><span>&#9734;</span><span>&#9734;</span>
        </div>
        <div class="rating-desc">
          <?php echo htmlspecialchars(substr($eatery["star_rating"], 0, 3))?> / 5 - <span><?php echo htmlspecialchars($eatery["number_reviews"]); ?> ratings</span>
        </div>
        <form id="review-editor-<?php echo htmlspecialchars($eatery["id"])?>" method="post" action="index.php#anchor-<?php echo htmlspecialchars($eatery["id"])?>">
          <h2>Click to Rate:</h2>
          <div class="review-star-rating">
            <input type="radio" name="review-star-rating-<?php echo htmlspecialchars($eatery["id"])?>" id="rating-5-<?php echo htmlspecialchars($eatery["id"])?>" value="5" <?php if ($answers["id"] == $eatery["id"] && $answers["star"] == "5") {echo 'checked=\"checked\"';}?> required>
            <label for="rating-5-<?php echo htmlspecialchars($eatery["id"])?>" class="rating-5"><span></span></label>
            <input type="radio" name="review-star-rating-<?php echo htmlspecialchars($eatery["id"])?>" id="rating-4-<?php echo htmlspecialchars($eatery["id"])?>" value="4" <?php if ($answers["id"] == $eatery["id"] && $answers["star"] == "4") {echo 'checked=\"checked\"';}?>>
            <label for="rating-4-<?php echo htmlspecialchars($eatery["id"])?>" class="rating-4"><span></span></label>
            <input type="radio" name="review-star-rating-<?php echo htmlspecialchars($eatery["id"])?>" id="rating-3-<?php echo htmlspecialchars($eatery["id"])?>" value="3" <?php if ($answers["id"] == $eatery["id"] && $answers["star"] == "3") {echo 'checked=\"checked\"';}?>>
            <label for="rating-3-<?php echo htmlspecialchars($eatery["id"])?>" class="rating-3"><span></span></label>
            <input type="radio" name="review-star-rating-<?php echo htmlspecialchars($eatery["id"])?>" id="rating-2-<?php echo htmlspecialchars($eatery["id"])?>" value="2" <?php if ($answers["id"] == $eatery["id"] && $answers["star"] == "2") {echo 'checked=\"checked\"';}?>>
            <label for="rating-2-<?php echo htmlspecialchars($eatery["id"])?>" class="rating-2"><span></span></label>
            <input type="radio" name="review-star-rating-<?php echo htmlspecialchars($eatery["id"])?>" id="rating-1-<?php echo htmlspecialchars($eatery["id"])?>" value="1" <?php if ($answers["id"] == $eatery["id"] && $answers["star"] == "1") {echo 'checked=\"checked\"';}?>>
            <label for="rating-1-<?php echo htmlspecialchars($eatery["id"])?>" class="rating-1"><span></span></label>
          </div>
          <label class="review-submit" for="review-submit-<?php echo $eatery["id"]?>">Go<input type="submit" form="review-editor-<?php echo htmlspecialchars($eatery["id"])?>" name="review-submit" id="review-submit-<?php echo $eatery["id"]?>" value="review-submit-<?php echo $eatery["id"]?>"></label>
        </form>
      </div>
    </div>
<?php };
?>
