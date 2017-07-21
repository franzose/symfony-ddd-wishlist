<?php

namespace Wishlist\Infrastructure\Persistence\Doctrine\Fixture;

use DateTimeImmutable;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Money\Currency;
use Money\Money;
use Wishlist\Domain\Expense;
use Wishlist\Domain\Wish;
use Wishlist\Domain\WishId;
use Wishlist\Domain\WishName;

class LoadWishesData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        $currency = new Currency('USD');

        foreach (range(0, 19) as $wishIndex) {
            $seconds = $wishIndex * 10;
            $wish = new Wish(
                WishId::next(),
                new WishName($faker->sentence(3)),
                Expense::fromCurrencyAndScalars(
                    $currency,
                    $faker->numberBetween(10000, 50000),
                    $faker->numberBetween(10, 50),
                    $faker->numberBetween(0, 50)
                ),
                new DateTimeImmutable("now - {$seconds} seconds")
            );

            $wish->publish();

            foreach (range(0, $faker->numberBetween(5, 25)) as $depositIndex) {
                $wish->deposit(new Money($faker->numberBetween(10, 50), $currency));
            }

            $manager->persist($wish);
            $manager->flush();

            $this->addReference("wish-{$wishIndex}", $wish);
        }
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1;
    }
}
