# Snowtricks

This project is a community website exercise for the PHP course at OpenClassrooms.  
It is not meant to be used in production nor is it meant to be a showcase.

This repository is primarily a way of sharing code with the tutor.

## Installation

This project uses [Composer](https://getcomposer.org) with PHP `>= 8.1`.

Configure your database in a `.env.local` file at the root of the project.
Copy a `DATABASE_URL` line from the `.env` file a modify it to fit your configuration.

Clone and install the project.

```shell
# Clone the repository
git clone https://github.com/scoopandrun/ocp6
cd ocp6

# Install the dependencies
composer install

# Create your database
php bin/console doctrine:database:create

# Execute the migrations
php bin/console doctrine:migrations:migrate

# (Optional) Load the fixtures to get a starting data set. See below.
php bin/console doctrine:fixtures:load
```

You can update the initial users information in the User data fixture.
