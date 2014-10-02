# Rabble and Rouser Applicant Coding Challenge

This is a simple app that allows multiple users to sign up, log in, and create new posts. I freely admit there are some 
bugs existing. Only have 8 hours to finish the project I had to cut some of my normal design ideas such as using unit 
tests to validate my work.

TODO:

* Add website to user data
* Add alert if data posted is empty and is required 
* comments should update the page without a refresh
* Look at the listing requirements in all their stupidity


## Some known issues

* Error handling is wonky, really should wrap all in try catch and add a logger.
* There are several inefficiencies with queries
* Should be using prepared statements everywhere
* Should be validating input WAY better than I am
* Shouldn't rely on in line configuration should move it all to a configuration file.


# Setup

## sqlite 

Create a sqlite db and run the init script in scripts/db

## mysql

Create a sqlite db and run the init script in scripts/db. Also you'll need to modify the connection configuration 
settings in the index.php file


# Conclusion

This isn't production ready nor would I claim it to be. With time all the issues could easily be resolved.