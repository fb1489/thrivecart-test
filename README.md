# thrivecart-test
Thrivecart Technical Test

---

# Setup Instructions:
- Requirements:
    - PHP 8.4.0 or higher
    - Composer 2.8.12 or higher
- Run `composer install` to install dependencies

# Running tests:
- Run `vendor/bin/phpunit` from the base project directory

# Running Cart Calculator:
- Run `php index.php` from the base project directory
- When prompted, enter any comma-separated list of widget codes (e.g. `R01, G01, G01`)
- The script will output the total value

---

## Assumptions:
 - Only one product can be added to the cart at a time (no bulk add option)
 - Deals are once per cart (not stackable)


## Notes / Context:
 - Added utility classes like `MonetaryValue` to better handle values and to add extensibility as it might be required later to better handle large amounts, more complicated calculations, more decimal place values, etc.
 - Added a currency object even though we only need USD at the moment to make it extensible
 - I added a `WidgetFactory` as in the given example we just have 3 static widgets. In a normal scenario we'd most likely have products be fed from a database which would then possibly have the codes stored in a list on the database and not as an enum in PHP - this would change the architecture a bit, but since we didn't have a database in the prototype, I thought this was a good alternative example of doing the lookup.
    - A more simplistic implementation of this would be to have a product catalogue object with an internal array key list mapping a code to an object, which when added in the cart, the cart would simply read from that list. I decided this was less secure and testable than the implementation I went with.
 - I decided to add each extra processing of the cart (delivery fees, second half off) as injectable rules in the constructor (could also have been a method) so it behaves as listeners/middleware, meaning we can extend it further to add more later and even create carts with different rules if necessary (different rules for different countries/locations; specific users having extra deals/costs; etc.) - which we would then also add a `CartFactory` that would handle creating these with the correct rules, it wouldn't be a "manual"/concrete creation as we currently have in this prototype codebase.


## TODOs / Things to consider that were not put in due to time constraints:
 - `MonetaryValue` class:
    - should ensure values are limited to specific decimal places if the business only works with that (e.g. only 2 decimal places allowed)
        - or add more rules and tests if multiple variations are required (e.g. 2 and 3 decimal places are allowed)
    - should clarify with business in regards to how rounding values work (depends on the context answer of the point above)
    - should be tested to ensure massive values do not break the system and can either be processed or fail in a safe way
    - I would add more specific tests for rounding and calculations as I'd label these as critical objects to the application
    - I would also use probably use the php specific library BCMath (https://www.php.net/manual/en/book.bc.php) to ensure reliability of values (not used here to not overcomplicate the code for the test reviewer as it requires an added layer for float conversion as values are handled as strings)
    - One concern I would have is that if values had more than 2 decimal points, should the rounding happen per item or per checkout (round before summing or after) because that could affect the final numbers.
 - Cart:
    - I would check if currencies are a concern / future idea, and determine if we need to build validation into the cart - e.g. can a cart contain products with a mix of currencies or can the cart only contain a single currency?