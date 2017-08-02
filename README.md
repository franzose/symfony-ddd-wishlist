[![](https://img.shields.io/packagist/dt/franzose/wishlist.svg)](https://packagist.org/packages/franzose/wishlist)
[![](https://travis-ci.org/franzose/symfony-ddd-wishlist.svg?branch=master)](https://travis-ci.org/franzose/symfony-ddd-wishlist)
[![](https://scrutinizer-ci.com/g/franzose/symfony-ddd-wishlist/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/franzose/symfony-ddd-wishlist?branch=master)

[На русском](https://github.com/franzose/symfony-ddd-wishlist/blob/master/README_RUS.md)

Wishlist
========

*I'm still working on the project, so some things can be unimplemented yet.*

This repository serves as an implementation of DDD, domain driven design, with usage of Symfony 3, PostgreSQL, and Redis as a backend and Vue.js/Sass as a frontend. The project is heavily inspired by [DDD Cargo Sample in PHP](https://github.com/codeliner/php-ddd-cargo-sample).

The basis for the project is a fairly simple domain, a wish list. Each wish can have its own price, daily fee and a fund which is implemented as a list of deposits to the wish. Wish can be fulfilled and is fulfilled as soon as its fund has enough money. Mistaken deposits can be removed or transfered to another wish. Any wish can have surplus funds, so they can also be transfered to other wishes.

## Installation
Clone the repository and run the following commands to install all the dependencies and build frontend scripts and styles:
```bash
cd /path/to/webroot
git clone https://github.com/franzose/symfony-ddd-wishlist.git
cd symfony-ddd-wishlist
composer self-update
composer install
npm install
./node_modules/.bin/encore dev
```

### PostgreSQL, Redis, and PHP dev server
To simplify backend setup, the project uses a couple of Docker images (so you need to install Docker too) that you'll find in `docker-compose.yml.dist` file. Run the following commands to start PostgreSQL and Redis, and also fill the database with some data:

```bash
cp ./app/config/parameters.yml.dist ./app/config/parameters.yml
cp ./app/config/parameters_permanent.yml.dist ./app/config/parameters_permanent.yml
cp ./docker-compose.yml.dist ./docker-compose.yml
docker-compose up -d
php bin/console doctrine:fixtures:load --fixtures=/path/to/src/Infrastructure/Persistence/Doctrine/Fixture/LoadWishesData.php
php bin/console server:start
```

## Project structure
TODO: write about project structure

## Support
If you have any problems using the application, please open a Github issue. The same applies to any questions or feature requests.

## Contributions
Any contribution is appreciated. This application serves as an example implementation of the domain driven design. I'd be very glad of any kind of shares of this repository being it a tweet, a post, a link, or whatever.

## Tests
The application is covered by unit and functional tests. Functional tests use SQLite database. Before running tests, please copy PHPUnit's configuration file:

```bash
cp ./phpunit.xml.dist ./phpunit.xml
```

Then use the following command to run tests:

```bash
./vendor/bin/phpunit
```
