<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail("user@bookapi.com");
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "azertyuiop1234"));
        $manager->persist($user);

        $userAdmin = new User();
        $userAdmin->setEmail("admin@bookapi.com");
        $userAdmin->setRoles(["ROLE_ADMIN"]);
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "azertyuiop1234"));
        $manager->persist($userAdmin);

        $listAuthors = [];
        for ($i=0; $i < 20; $i++) { 
            
            $author = new Author();
            $author->setFirstName("Prénom " . $i);
            $author->setLastName("Nom " . $i);
            $manager->persist($author);
            $listAuthors[] = $author;
        }

        for ($i=0; $i < 20; $i++) { 
            
            $book = new Book();
            $book->setTitle("Titre " . $i);
            $book->setCoverText("Quatrième de couverture numéro : " . $i);
            $book->setAuthor($listAuthors[array_rand($listAuthors)]);
            $manager->persist($book);
        }

        $manager->flush();
    }
}
