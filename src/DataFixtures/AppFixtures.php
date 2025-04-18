<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\Author;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $userPasswordHasher;
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        // Création d'un user "normal"
        $user = new User();
        $user->setEmail('user@bookapi.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword(
            $this->userPasswordHasher->hashPassword($user, 'password')
        );
        $manager->persist($user);

        // Création d'un user "admin"
        $admin = new User();
        $admin->setEmail('admin@bookapi.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword(
            $this->userPasswordHasher->hashPassword($admin, 'password')
        );
        $manager->persist($admin);

        // Création d'un auteur
        for ($i=0; $i < 10; $i++) { 
            $author = new Author();
            $author->setFirstName('Prénom : '.$i);
            $author->setLastName('Nom : '.$i);
            $manager->persist($author);

            $listAuthors[] = $author;
        }

        // Création d'un livre
        for ($i = 0; $i < 20; $i++) {
            $book = new Book();
            $book->setTitle('Titre '.$i);
            $book->setCoverText('Quatrième de couverture numéro :  '.$i);
            $book->setAuthor($listAuthors[array_rand($listAuthors)]);

            $manager->persist($book);
        }
        $manager->flush();
    }
}
