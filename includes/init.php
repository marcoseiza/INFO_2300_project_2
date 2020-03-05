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

function eatery_post($eatery) {?>
  <div class="eatery">
      <div class="img">
        <div></div>
      </div>
      <div class="name">
        <h1><?php echo htmlspecialchars($eatery["name"])?></h1>
        <div class="type">
          <h2><span>- </span><?php echo ucfirst(htmlspecialchars($eatery["type"]))?></h2>
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
      <div class="star-rating">
        <h2>Rating:</h2>
        <div class="rating-container">
          <div class="rating" style="--rating-percent: <?php $rating = ($eatery["star_rating"] * 100) / 5; echo htmlspecialchars($rating)?>%">
            <span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span>
          </div>
          <span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span>
        </div>
        <span><?php echo htmlspecialchars($eatery["star_rating"])?> Average, out of <?php echo htmlspecialchars($eatery["number_reviews"])?>
          <label for="readmore-<?php echo htmlspecialchars($eatery["id"])?>">
            reviews<span></span>
          </label>
        </span>
      </div>
      <input type="checkbox" name="readmore<?php echo htmlspecialchars($eatery["id"])?>" id="readmore-<?php echo htmlspecialchars($eatery["id"])?>">
      <div class="reviews">
        <?php
          foreach ($eatery["reviews"] as $review) {
            review($review);
          }
        ?>
      </div>
      <label for="readmore-<?php echo htmlspecialchars($eatery["id"])?>" class="readmore">
        <h2></h2>
      </label>
    </div>
<?php };
?>
