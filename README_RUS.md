[![](https://img.shields.io/packagist/dt/franzose/wishlist.svg)](https://packagist.org/packages/franzose/wishlist)
[![](https://travis-ci.org/franzose/symfony-ddd-wishlist.svg?branch=master)](https://travis-ci.org/franzose/symfony-ddd-wishlist)
[![](https://scrutinizer-ci.com/g/franzose/symfony-ddd-wishlist/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/franzose/symfony-ddd-wishlist?branch=master)

[In English](https://github.com/franzose/symfony-ddd-wishlist/blob/master/README.md)

Wishlist
========

*Я всё еще работаю над проектом, поэтому некоторые вещи могут оставаться нереализованными.*

Этот репозиторий посвящен реализации предметно-ориентированного проектирования (DDD) с использованием Symfony 3, PostgreSQL и Redis для серверной части, а также Vue.js/SASS для фронтенда.

За основу проекта взята довольно простая предметная область — вишлист. Это список желаний, в который можно добавлять свои желания, а также их исполнять. Каждое желание имеет свою стоимость, размер ежедневного денежного вклада и копилку, выраженную набором вкладов в это желание. Чтобы исполнить желание, необходимо вложить в него достаточное количество денег. Ошибочные вклады можно удалять, либо перенаправлять на другие желания. У желаний могут быть излишки вкладов, которые также можно перенаправлять на другие желания.

## Установка
Склонируйте репозиторий и выполните следующие команды, чтобы установить все зависимости и собрать скрипты со стилями для фронтенда:
```bash
cd /path/to/webroot
git clone https://github.com/franzose/symfony-ddd-wishlist.git
cd symfony-ddd-wishlist
composer self-update
composer install
npm install
./node_modules/.bin/encore dev
```

### PostgreSQL, Redis и dev-сервер PHP
Для упрощения разворачивания базы данных и кеша в проекте используются образы Docker (так что его тоже придётся установить), указанные в файле `docker-compose.yml.dist`. Выполните следующие команды, чтобы запустить PostgreSQL и Redis, а также заполнить базу данных начальными данными:

```bash
cp ./app/config/parameters.yml.dist ./app/config/parameters.yml
cp ./app/config/parameters_permanent.yml.dist ./app/config/parameters_permanent.yml
cp ./docker-compose.yml.dist ./docker-compose.yml
docker-compose up -d
php bin/console doctrine:fixtures:load --fixtures=/path/to/src/Infrastructure/Persistence/Doctrine/Fixture/LoadWishesData.php
php bin/console server:start
```

## Структура проекта
TODO: написать про структуру проекта

## Поддержка
Если у вас возникли какие-либо проблемы в процессе использования данного приложения, пожалуйста напишите об этом в отдельной задаче. То же касается вопросов по реализации функциональности или запросов на добавление новых возможностей.

## Собственный вклад
Любой вклад ценен. Данное приложение служит одним из примеров реализации предметно-ориентированного проектирования. Хорошим или плохим, это уже не мне решать. Поэтому я был бы очень рад распространению информации об этом репозитории в широкие массы (<s>зараспространите среди жильцов вашего ЖЭКа, как вы это любите</s>).

## Тесты
Приложение покрыто юнит- и функциональными тестами. Для функциональных тестов используется база данных SQLite. Перед запуском тестов скопируйте конфигурационный файл-образец PHPUnit:

```bash
cp ./phpunit.xml.dist ./phpunit.xml
```

Затем, чтобы запустить тесты, используйте следующую команду:

```bash
./vendor/bin/phpunit
```
