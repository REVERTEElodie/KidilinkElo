<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use Faker\Generator;
use App\Entity\Album;
use App\Entity\Photo;
use App\Entity\Classe;
use App\Entity\Comment;
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
        //Données des utilisateurs
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
        
        //Données pour les classes
        for ($i=0; $i < 10 ; $i++) { 
            $classe = new Classe();
            $classe->setName($this->faker->word());
            $classe->setAnneeScolaire($this->faker->year());
            $manager->persist($classe);
        }

        $albums = [];
        $albumsCounts =  5;
        $photoCount = 200;
        //Données pour les Albums
        for ($i=0; $i < $albumsCounts ; $i++) { 
            $album = new Album();
            $album->setTitle($this->faker->word());
            $album->setDescription($this->faker->sentence());
            $manager->persist($album);
            $albums[] = $album;
        }

        $manager->flush();

        //Données pour les Photos
        for ($i=0; $i < $photoCount ; $i++) { 
            $photo = new Photo();
            $photo->setTitle($this->faker->word());
            $photo->setDescription($this->faker->sentence());
            $photo->setUrl($this->faker->url());
            // albums de 0 à 19
            // si je divise par 10 mon $i par exemple 199 j'obtient 19.9 et que j'arrondis à l'entier inférieur j'ai 19
            // photoCount / alBumCount
            $photo->setAlbum($albums[floor($i/($photoCount/$albumsCounts))]);
            $manager->persist($photo);
        }
        //Données pour les commentaires
        for ($i=0; $i < 400; $i++) { 
            $comment = new Comment();
            $comment->setContent($this->faker->sentence());
            $manager->persist($comment);
        }

        //Données por les commentaires
        for ($i=0; $i < 400 ; $i++) { 
            $comment = new Comment();
            $comment->setContent($this->faker->sentence());
            $manager->persist($comment);

        $manager->flush();
    }

}
}