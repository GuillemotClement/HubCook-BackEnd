<?php

namespace App\DataFixtures;

use App\Entity\Recipe;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
      //instanciation de la lib Faker avec le paramètre en FR
      $faker = Factory::create('fr-FR');

      //on créer une boucle for pour générer 20 recetes différentes
      for($i = 0; $i < 20; $i++){
        //création d'une instance de l'entité
        $recipe = new Recipe();
        //on ajoute des données à l'instance
        $recipe->setName($faker->words(3, true))
                ->setDescrib($faker->paragraph(3, true))
                ->setImage('https://picsum.photos/id/'.($i +1 ). '/640/480')
                ->setCreatedAt(new \DateTimeImmutable());

        //on viens préparer la requête
        $manager->persist($recipe);
      }

        //on envoie les fake data
        $manager->flush();
    }
}
