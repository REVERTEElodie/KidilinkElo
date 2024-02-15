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
        $user->setFirstname('kiki');
        $user->setLastname('kikou');
        $user->setEmail('admin@kidilink.com');
        $user->setRoles(["ROLE_ADMIN"]);
        $password = "azerty";
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $password
        );
        $user->setPassword($hashedPassword);
        $manager->persist($user);

        $persons = [
            [
                'firstname' => 'Thierry',
                'role' => 'ROLE_MANAGER'
            ],
            [
                'firstname' => 'Stéphanie',
                'role' => 'ROLE_MANAGER'
            ],
            [
                'firstname' => 'Laurent',
                'role' => 'ROLE_MANAGER'
            ],
            [
                'firstname' => 'Bernard',
                'role' => 'ROLE_MANAGER'
            ],
            [
                'firstname' => 'Josiane',
                'role' => 'ROLE_USER'
            ],
            [
                'firstname' => 'Michel',
                'role' => 'ROLE_USER'
            ],
            [
                'firstname' => 'Robert',
                'role' => 'ROLE_USER'
            ],
            [
                'firstname' => 'Marcel',
                'role' => 'ROLE_USER'
            ],
            [
                'firstname' => 'Robin',
                'role' => 'ROLE_USER'
            ],
            [
                'firstname' => 'Mehdi',
                'role' => 'ROLE_USER'
            ],
            [
                'firstname' => 'Patrick',
                'role' => 'ROLE_USER'
            ],
            [
                'firstname' => 'Elodie',
                'role' => 'ROLE_USER'
            ],
        ];

        $parentsGenerated = [];
        $managersGenerated = [];
        foreach ($persons as $person) {
            $newPerson = new User();
            $newPerson->setFirstname($person['firstname']);
            if ($person['role'] === 'ROLE_MANAGER') {
                $email = strtolower($person['firstname']) . '@manager.com';
                $password = "manager";
                $newPerson->setLastname('Nom Manager');
            } else {
                $email = strtolower($person['firstname']) . '@parent.com';
                $password = "parent";
                $newPerson->setLastname('Nom Parent');
            }
            $newPerson->setEmail($email);
            $newPerson->setRoles([$person['role']]);
            $hashedPassword = $this->passwordHasher->hashPassword(
                $newPerson,
                $password
            );
            $newPerson->setPassword($hashedPassword);
            $manager->persist($newPerson);

            if ($person['role'] === 'ROLE_MANAGER') {
                $managersGenerated[] = $newPerson;
            } else {
                $parentsGenerated[] = $newPerson;
            }
        }

        $manager->flush();

        $classesName = ['Petite section', 'Moyenne section', 'Grande section', 'CP', 'CE1', 'CE2', 'CM1', 'CM2'];
        $classes = [];
        $classCount = count($classesName);
        
        //Données pour les classes
        foreach($classesName as $classeName) {
            $classe = new Classe();
            $classe->setName($classeName);
            $classe->setAnneeScolaire($this->faker->year());

            // Ajouter ou pas un manager
            $randomManagerIndex = array_rand($managersGenerated);
            $classe->setManager($managersGenerated[$randomManagerIndex]);

            // Ajouter quelques parents
            $randomNb = rand(0, 4);
            for ($j = 0; $j < $randomNb; $j++) {
                $randomParentIndex = array_rand($parentsGenerated);

                $classe->addParent($parentsGenerated[$randomParentIndex]);
            }

            $manager->persist($classe);
            $classes[] = $classe;
        }
        $manager->flush();

        $photos = [];
        $albums = [];
        $albumsCounts =  5;
        $photoCount = 200;
        //Données pour les Albums
        for ($i = 0; $i < $albumsCounts; $i++) {
            $album = new Album();
            $album->setTitle($this->faker->word());
            $album->setDescription($this->faker->sentence());
            //ici
            $album->setClasse($classes[floor($i / ($albumsCounts / $classCount))]);
            $manager->persist($album);
            $albums[] = $album;
        }

        $manager->flush();

        $photos = [];
        $photoCount = 200;
        //Données pour les Photos
        for ($i = 0; $i < $photoCount; $i++) {
            $photo = new Photo();
            $photo->setTitle($this->faker->word());
            $photo->setDescription($this->faker->sentence());
            $photo->setUrl($this->faker->url());
            // albums de 0 à 19
            // si je divise par 10 mon $i par exemple 199 j'obtient 19.9 et que j'arrondis à l'entier inférieur j'ai 19
            // photoCount / alBumCount
            $photo->setAlbum($albums[floor($i / ($photoCount / $albumsCounts))]);
            $manager->persist($photo);
            $photos[] = $photo;
        }
        $manager->flush();

        $commentCount = 30;
        //Données por les commentaires
        for ($i = 0; $i < $commentCount; $i++) {
            $comment = new Comment();
            $comment->setContent($this->faker->sentence());
            if ($i < count($photos)) {
                $comment->setPhoto($photos[$i]);
            }
            $manager->persist($comment);
        }

        $manager->flush();
    }
}
