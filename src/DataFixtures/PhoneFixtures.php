<?php

namespace App\DataFixtures;

use App\Entity\Phone;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class PhoneFixtures extends Fixture
{
    private $names = ['iPhone', 'Samsung'];


    public function load(ObjectManager $manager)
    {
        for($i = 1; $i <= 20; $i++) {
            $phone = new Phone();
            $phone->setName($this->names[rand(0,1)]. ' ' . rand(5, 8));
            $phone->setPrice(rand(500, 1000));
            $phone->setDescription('A wonderful phone with ' . rand(10, 50) . ' tricks');

            $manager->persist($phone);
        }

        $manager->flush();
    }
}
