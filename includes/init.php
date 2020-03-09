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

$search = $search_field = "";
$empty = FALSE;
$checked = "";
$name = $comment = "";
$star_rating = "0";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_POST['search-submit'])) {
    $search = $_POST["search"];
    $search_field = $_POST["search-field"];
    if (!empty($search_field) && $search_field !== "none") {
      $search_sql = "SELECT * FROM eateries WHERE " . $search_field . " like '%'||:search||'%'";
      $params = array(":search" => $search);
    } elseif ($search_field == "none") {
      $search_sql = "SELECT * FROM eateries WHERE
      name like '%'||:search||'%' or
      hours_open like '%'||:search||'%' or
      hours_close like '%'||:search||'%' or
      address like '%'||:search||'%' or
      website like '%'||:search||'%' or
      phone like '%'||:search||'%' or
      star_rating like '%'||:search||'%'";
      $params = array(":search" => $search);
    } elseif (empty($search_field)) {
      var_dump($search_field, $_POST);
      $search_sql = "SELECT * FROM eateries";
      $params = array();
    }
    $eateries = exec_sql_query($eateries_db, $search_sql, $params)->fetchAll();
  } else {
    $eateries = exec_sql_query($eateries_db, "SELECT * FROM eateries")->fetchAll();
  }

  if (isset($_POST["review-submit"])) {
    $id = substr($_POST["review-submit"], -1);
    $name = $_POST["review-name-" . $id];
    $answer_star_rating = $_POST["review-star-rating-" . $id];
    $comment = $_POST["review-comment-" . $id];
    $checked = "";

    if (empty($name)) {
      $name = "Anonymous";
    } elseif (preg_match("/^[a-z ,.'-]+$/i", $name)) {
      $name = str_replace("'", "\'", $name);
    }

    if (empty($answer_star_rating)) {
      $checked = "checked";
    }

    if (empty($comment)) {
      $checked = "checked";
    }

    if (empty($checked)) {
      exec_sql_query($eateries_db,
      "INSERT INTO reviews (eatery_id, name, review, star_rating) VALUES (:id, :name, :review, :star_rating)",
      array(":id" => $id, ":name" => $name, ":review" => $comment, ":star_rating" => $answer_star_rating));
    }
  }

} else {
  $eateries = exec_sql_query($eateries_db, "SELECT * FROM eateries")->fetchAll();
}

foreach ($eateries as $eatery) {
  $reviews = exec_sql_query($eateries_db, "SELECT * FROM reviews WHERE (eatery_id = :id)", array(":id" => $eatery["id"]))->fetchAll();
  exec_sql_query($eateries_db, "UPDATE eateries SET number_reviews = :number_reviews WHERE id = :id;",
                  array(":id" => $eatery["id"], ":number_reviews" => sizeof($reviews)));
  $star_rating = 0;
  foreach ($reviews as $review) {
    $star_rating = $star_rating + $review["star_rating"];
  }
  if (sizeof($reviews) !== 0) {
    $star_rating = $star_rating / sizeof($reviews);
  } else {
    $star_rating = 5;
  }
  exec_sql_query($eateries_db, "UPDATE eateries SET star_rating = :star_rating WHERE id = :id;", array(":id" => $eatery["id"], ":star_rating" => $star_rating));
}

$eateries = exec_sql_query($eateries_db, "SELECT * FROM eateries")->fetchAll();

$eatery_value = 0;
foreach ($eateries as $eatery) {
  $reviews = exec_sql_query($eateries_db, "SELECT * FROM reviews WHERE (eatery_id = :id)", array(":id" => $eatery["id"]))->fetchAll();
  $eatery["reviews"] = $reviews;
  $eateries[$eatery_value] = $eatery;
  $eatery_value += 1;
}

function review($review) {?>
  <div class="review">
    <h2><?php echo htmlspecialchars($review["name"])?></h2>
    <div class="rating-container">
      <div class="rating" style="--rating-percent: <?php $rating = ($review["star_rating"] * 100) / 5; echo htmlspecialchars($rating)?>%">
          <span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span>
        </div>
        <span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span>
      </div>
      <p><?php echo htmlspecialchars($review["review"])?></p>
    </div>
<?php };

function eatery_post($eatery, $answers) {?>
  <div class="eatery" id="eatery-<?php echo htmlspecialchars($eatery["id"])?>">
      <div class="img">
        <?php
        if ($eatery["type"] == "restaurant") {?>
        <svg viewBox="0 0 30 30">
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
        else if ($eatery["type"] == "coffee") {?>
        <svg viewBox="0 0 30 30">
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
        <svg viewBox="0 0 30 30">
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
        <?php }?>
      </div>
      <div class="name">
        <h1><?php echo htmlspecialchars($eatery["name"])?></h1>
        <div class="type">
          <h2>- <?php echo ucfirst(htmlspecialchars($eatery["food_type"]))?> / <?php echo ucfirst(htmlspecialchars($eatery["type"]))?></h2>
        </div>
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
        <p><?php echo htmlspecialchars($eatery["address"])?></p>
      </div>
      <?php
          if ($eatery["website"]) {
            $website = "http://www." . htmlspecialchars($eatery["website"]);
            $websitelink = "www." . htmlspecialchars($eatery["website"]);
        ?>
        <div class="website">
          <h2>Website:</h2>
          <a href="<?php echo $website?>"><?php echo $websitelink?></a>
        </div>
      <?php }?>
      <?php if ($eatery["phone"]) { ?>
        <div class="phone">
          <h2>Phone:</h2>
          <a><?php echo htmlspecialchars($eatery["phone"])?></a>
        </div>
      <?php }?>
      <input type="checkbox" name="readmore-<?php echo htmlspecialchars($eatery["id"])?>" id="readmore-<?php echo htmlspecialchars($eatery["id"])?>">
      <input type="checkbox" name="review-action-<?php echo htmlspecialchars($eatery["id"])?>" id="review-action-<?php echo htmlspecialchars($eatery["id"])?>" <?php if ($answers["id"] == $eatery["id"]) {echo $answers["checked"];}?>>
      <div class="star-rating">
        <h2>Rating:</h2>
        <div class="rating-container">
          <div class="rating" style="--rating-percent: <?php $rating = ($eatery["star_rating"] * 100) / 5; echo htmlspecialchars($rating)?>%">
            <span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span>
          </div>
          <span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span>
        </div>
        <div class="rating-desc">
          <?php echo htmlspecialchars(round($eatery["star_rating"], 1))?> Average, out of <span><?php echo htmlspecialchars($eatery["number_reviews"]); ?></span>
          <?php if ($eatery["reviews"]) { ?>
            <label for="readmore-<?php echo htmlspecialchars($eatery["id"])?>">
              reviews<span></span>
            </label>
          <?php } else { ?>
            <span>reviews</span>
          <?php } ?>
        </div>
      </div>
      <div class="review-action">
          <label for="review-action-<?php echo htmlspecialchars($eatery["id"])?>" class="review-action">
            <h2>Write a Review</h2>
          </label>
        </div>
      <div class="reviews">
        <form id="review-editor-<?php echo htmlspecialchars($eatery["id"])?>" method="post" action="index.php#eatery-<?php echo htmlspecialchars($eatery["id"])?>">
          <h2>Write A Review!</h2>
          <div class="review-action">
            <label for="review-action-<?php echo htmlspecialchars($eatery["id"])?>" class="review-action">
              <h2>&#10006;</h2>
            </label>
          </div>
          <div class="review-name">
            <label for="review-name-<?php echo htmlspecialchars($eatery["id"])?>">Name:</label>
            <input type="text" name="review-name-<?php echo htmlspecialchars($eatery["id"])?>" id="review-name-<?php echo htmlspecialchars($eatery["id"])?>" value="<?php if ($answers["id"] == $eatery["id"]) {echo htmlspecialchars($answers["name"]);}?>">
          </div>
          <div class="review-star-rating">
            <input type="radio" name="review-star-rating-<?php echo htmlspecialchars($eatery["id"])?>" id="rating-5-<?php echo htmlspecialchars($eatery["id"])?>" value="5" <?php if ($answers["id"] == $eatery["id"] && $answers["star"] == "5") {echo 'checked=\"checked\"';}?>>
            <label for="rating-5-<?php echo htmlspecialchars($eatery["id"])?>" class="rating-5"><span>&#9733;</span></label>
            <input type="radio" name="review-star-rating-<?php echo htmlspecialchars($eatery["id"])?>" id="rating-4-<?php echo htmlspecialchars($eatery["id"])?>" value="4" <?php if ($answers["id"] == $eatery["id"] && $answers["star"] == "4") {echo 'checked=\"checked\"';}?>>
            <label for="rating-4-<?php echo htmlspecialchars($eatery["id"])?>" class="rating-4"><span>&#9733;</span></label>
            <input type="radio" name="review-star-rating-<?php echo htmlspecialchars($eatery["id"])?>" id="rating-3-<?php echo htmlspecialchars($eatery["id"])?>" value="3" <?php if ($answers["id"] == $eatery["id"] && $answers["star"] == "3") {echo 'checked=\"checked\"';}?>>
            <label for="rating-3-<?php echo htmlspecialchars($eatery["id"])?>" class="rating-3"><span>&#9733;</span></label>
            <input type="radio" name="review-star-rating-<?php echo htmlspecialchars($eatery["id"])?>" id="rating-2-<?php echo htmlspecialchars($eatery["id"])?>" value="2" <?php if ($answers["id"] == $eatery["id"] && $answers["star"] == "2") {echo 'checked=\"checked\"';}?>>
            <label for="rating-2-<?php echo htmlspecialchars($eatery["id"])?>" class="rating-2"><span>&#9733;</span></label>
            <input type="radio" name="review-star-rating-<?php echo htmlspecialchars($eatery["id"])?>" id="rating-1-<?php echo htmlspecialchars($eatery["id"])?>" value="1" <?php if ($answers["id"] == $eatery["id"] && $answers["star"] == "1") {echo 'checked=\"checked\"';}?>>
            <label for="rating-1-<?php echo htmlspecialchars($eatery["id"])?>" class="rating-1"><span>&#9733;</span></label>
            <input type="radio" name="review-star-rating-<?php echo htmlspecialchars($eatery["id"])?>" id="rating-0-<?php echo htmlspecialchars($eatery["id"])?>" value="0" <?php if ($answers["id"] == $eatery["id"] && $answers["star"] == "0") {echo 'checked=\"checked\"';}?>>
            <label for="rating-0-<?php echo htmlspecialchars($eatery["id"])?>" class="rating-0"><span></span></label>
            <label for="review-star-rating" class="title">Star Rating:</label>
          </div>
          <div class="review-comment">
            <label for="review-comment">What do you think?</label>
            <textarea name="review-comment-<?php echo htmlspecialchars($eatery["id"])?>" id="review-comment"><?php if ($answers["id"] == $eatery["id"]) {echo htmlspecialchars($answers["comment"]);}?></textarea>
          </div>
          <label for="review-submit-<?php echo htmlspecialchars($eatery["id"])?>" class="review-submit">
            Submit<span>&#8594;</span>
            <input type="submit" form="review-editor-<?php echo htmlspecialchars($eatery["id"])?>" name="review-submit" id="review-submit-<?php echo htmlspecialchars($eatery["id"])?>" value="Post-<?php echo htmlspecialchars($eatery["id"])?>">
          </label>
        </form>
        <?php
          foreach ($eatery["reviews"] as $review) {
            review($review);
          }
        ?>
      </div>
      <?php if ($eatery["reviews"]) { ?>
        <label for="readmore-<?php echo htmlspecialchars($eatery["id"])?>" class="readmore">
          <h2></h2>
        </label>
      <?php } ?>
    </div>
<?php };
?>
