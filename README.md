# Snowtricks

[![SymfonyInsight](https://insight.symfony.com/projects/c5984add-ba28-4fd6-8062-b6e3ff7abed0/big.svg)](https://insight.symfony.com/projects/c5984add-ba28-4fd6-8062-b6e3ff7abed0)

This project is a community website exercise for the PHP course at OpenClassrooms.  
It is not meant to be used in production nor is it meant to be a showcase.

This repository is primarily a way of sharing code with the tutor.

## Installation

This project uses [Composer](https://getcomposer.org) with PHP `>= 8.1`.

Configure your database and email server in a `.env.local` file at the root of the project.  
Copy a `DATABASE_URL` line from the `.env` file and modify it to fit your configuration.  
Copy a `MAILER_DSN` line from the `.env` file and modify it to fit your configuration.

Clone and install the project.

```shell
# Clone the repository
git clone https://github.com/scoopandrun/ocp6
cd ocp6

# Install the dependencies
composer install

# Create your database
# Don't forget to configure 'DATABASE_URL' in your .env.local file with your local database information
php bin/console doctrine:database:create

# Execute the migrations
php bin/console doctrine:migrations:migrate

# (Recommended) Load the fixtures to get a starting data set.
# You can update the initial users information in the User data fixture.
# This downloads pictures from the Internet, so it may take some time to complete.
php bin/console doctrine:fixtures:load

# Launch the messenger consumer (async transport for email messages)
# Don't forget to configure 'MAILER_DSN' in your .env.local file with your local email server information
php bin/console messenger:consume async
```
