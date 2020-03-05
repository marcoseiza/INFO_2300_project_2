# Project 2: Design Journey

Be clear and concise in your writing. Bullets points are encouraged.

**Everything, including images, must be visible in VS Code's Markdown Preview.** If it's not visible in Markdown Preview, then we won't grade it.

## Catalog (Milestone 1)

### Describe your Catalog (Milestone 1)
> What will your collection be about? What types of attributes will you keep track of for the *things* in your catalog? 1-2 sentences.
My collection of data will be about nearby eateries in Ithaca: cafes, restaurants, bakeries, bar, etc...\
Attributes: Name, Type of Food, Price ($ $$ $$$), Location (address), Hours, Phone Number, Website Link, Type (cafe, restaurant, etc...), Reviews, Star Rating (Might not be able to implement this but we'll see)


### Target Audience(s) (Milestone 1)
> Tell us about your target audience(s).
Phillip:
- Phillip is a new commer to Ithaca. He needs to find a place that sells south-western type food becuase he is homesick.
- Price isn't too much of a concern, but he doesn't want to splurg.
- The address needs to be visible and copyable so he can put it into a gps and go there (be great if I can make a link to send him to a google maps thing)
- He wants something relatively authentic so something with pretty good reviews and ratings


### Design Patterns (Milestone 1)
> Review some existing catalog that are similar to yours. List the catalog's you reviewed here. Write a small reflection on how you might use the design patterns you identified in your review in your own catalog.

Google Search, "Eateries Near Me",
- Stacked List Blocks
    - Features by order of heirarchy
        - Name of restaurant
        - Star Rating (#of ratings)
        - Price Rating ($ $$ $$$)
        - Type of Food (default=restaurant)
        - Square image of the place
        - Address
        - Open or Closed & hours
        - Sometimes: Description of the place
- When clicked
    - More information came out in a large block to the side of the stacked list blocks
    - Website, Hours, Menu, Reservations, Ordering, Phone Number
    - Reviews when user scrolls down


Eatery App
- Title Bar
- Search Bar "Search Eateries and Menus"
- Two Lists, Open and Closed
- Open
    - Stacked List Block
        - Features by order of heirarchy
            - Img of the place
            - Title
            - Distance from the user
            - Tags, (meal plan, BRBs, Cash)
            - Open until $time
- Closed
    - Same as open
    - all list blocks are given an opaque layer on top of them
- All are clickable
    - Same preview information at the top
    - Popular Times diagram with waiting times depending on the hour
    - Menu (breakdast, lunch dinner)

What I can use
- Both have stackable blocks, so I don't think grid is a good option for me
- I didn't like when the image was too important in the heirarchy of the block
    - Title, Food Type and Star Rating should be the most important
    - Then address
    - Then image (in my case icon for the type)
    - On click, same info but add...
        - website
        - description
        - reviews with star rating


## Design & Planning (Milestone 2)

## Design Process (Milestone 2)
> Document your design process. Show us the evolution of your design from your first idea (sketch) to design you wish to implement (sketch). Show us the process you used to organize content and plan the navigation, if applicable.
> Label all images. All labels must be visible in VS Code's Markdown Preview.
> Clearly label the final design.


First Sketch:
![site-sketch](../images/Page_1.jpg)\
Final Sketch:
![site-sketch-refined](../images/Page_2.jpg)

Process:\
I first thought that the eatery card should have the star rating high in the heirarchy of the card. This didn't really work well, becuase (1) I wouldn't be able to expand to see reviews in a comfortable way and (2) it's not the most important thing a user wants to see.\
A user wants to first see the name, the type of shop, the hours and the address. The rest is extra information that could be useful in picking a place, but is not imperative.\
I therefore redesigned the first sketch to put the layout in this order of heirarchy. Name and type on top, with an icon that can help identify quicker. Hours, Address, and below them, phone and website. Finally, put the rating and the read more input that expands the card to see reviews.

## Partials (Milestone 2)
> If you have any partials, plan them here.


## Database Schema (Milestone 2)
> Describe the structure of your database. You may use words or a picture. A bulleted list is probably the simplest way to do this. Make sure you include constraints for each field.

Table: movies
- field 1: description..., constraints...
- field...

eateries {
    id: INTEGER NN PK AI U;
    type: TEXT NN; //this will be restaurant, coffee-shop, deli, etc..
    name: TEXT NN;
    hours: TEXT NN;
    address: TEXT NN;
    website: TEXT;
    phone: TEXT;
    star_rating: REAL NN;
    number_reviews: INTEGER NN;
}

reviews {
    id: INTEGER NN PK AI U;
    eatery_id: INTEGER NN; //this will be the id of the eatery
    name: TEXT; //can be anonymous
    review: TEXT NN;
    star_rating: REAL NN;
}


## Database Query Plan (Milestone 2)
> Plan your database queries. You may use natural language, pseudocode, or SQL.]

1. All records

    ```sqlite3
        SELECT * FROM eateries;
        //insert some code that makes :parent_id = "eatery's id";
        SELECT name, review, star_rating FROM reviews WHERE parent_id = :parent_id;
    ```

2. Search records
    ```sqlite3
        SELECT * FROM eateries WHERE type = :searchField AND name = :seach;
        //insert some code that makes :parent_id = "eatery's id";
        SELECT name, review, star_rating FROM reviews WHERE parent_id = :parent_id;
    ```

3. Insert record
    ```sqlite3
        //We have the id of the parent eatery = :parent_id
        //Creating a new review
        INSERT INTO reviews (parent_id, name, review, star_rating) VALUES (:parent_id, :name, :review, :star_rating)

        //Updating number of reviews in an eatery
        UPDATE eateries SET number_reviews = number_reviews + 1 WHERE id = :parent_id;
    ```



## Code Planning (Milestone 2)
> Plan any PHP code you'll need here.

pseudo:

call db and get eatery info

for every eatery in eateries{
    $review = sql query WHERE eatery_id = eatery["id"]
    star_rating = 0;
    for every review in $reviews {
        star_rating += 1
        update eatery database --> number_reviews = number_reviews + 1
    }
    if length($reviews) !== 0 {
        star_rating = star_rating / length($reviews);
    } else {
        star_rating = 5;
    }

    update eatery databse -->  star_rating = $star_rating;

    append reviews at the end of eatery array
    // eatery = array (info..., ["reviews] => array(review info...))
}

call db and get eatery info

for every eatery in eateries {
    append reviews at the end of eatery array
    // eatery = array (info..., ["reviews] => array(review info...))
}

function review_card ($review) {
    html shtuff with $review info...
}

function eatery_card ($eatery){
    bunch of html that formats the info from $eatery array...
    for every review in $eatery["reviews] {
        function review_card(review)
    }
}


# Reflection (Final Submission)
> Take this time to reflect on what you learned during this assignment. How have you improved since Project 1? What things did you have trouble with?
