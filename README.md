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

* Error handling is wonky, really should wrap all exception cases in try catch and add a logger.
* There are several inefficiencies with queries
* Should be using prepared statements everywhere
* Should be validating input WAY better than I am
* Shouldn't rely on in line configuration should move it all to a configuration file.


# Setup

Install vagrant and then while in the directory of this project execute `vagrant up` this will spin up the machine after
a wait. You'll need to set up your host file to access it which should be on the IP address 192.168.53.18.

Once the machine is running you'll ineed to install dependencies so execute `vagrant ssh` and then change to the `/var/www/` directory. While there you'll need to execute `composer install` and let it churn. Once it's done you should be ready to go.