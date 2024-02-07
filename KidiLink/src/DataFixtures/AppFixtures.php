<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use Faker\Generator;
use App\Entity\Classe;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private Generator $faker;
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {

        $this->faker = Factory::create('fr-FR');
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('admin@kidilink.com');
        $user->setRoles(["ROLE_ADMIN"]);
        $password = "azerty";
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $password
        );
        $user->setPassword($hashedPassword);
        $manager->persist($user);
        
        for ($i=0; $i < 10 ; $i++) { 
            $classe = new Classe();
            $classe->setName($this->faker->word());
            $classe->setAnneeScolaire($this->faker->year());
            $manager->persist($classe);
        }

        $manager->flush();
    }

}
