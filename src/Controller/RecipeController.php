<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Builder\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class RecipeController extends AbstractController
{
    #[Route("/recipe", name: "app_recipe")]
    public function index(): Response
    {
        return $this->render("recipe/index.html.twig", [
            "controller_name" => "RecipeController",
        ]);
    }

    #[Route("/api/recipes", name: "listRecipe", methods: ["GET"])]
    public function listRecipe(
        RecipeRepository $recipeRepository,
        SerializerInterface $serializer
    ): JsonResponse {
        //récupération des données en BDD
        $recipeList = $recipeRepository->findAll();

        //on serialize les datas récupérés
        $jsonRecipeList = $serializer->serialize($recipeList, "json");

        //on retourne les données serializé
        //1 : donnée serialisé
        //2: code retour
        //headers
        //true: indique que les données sont serialisé
        return new JsonResponse($jsonRecipeList, Response::HTTP_OK, [], true);
    }

    #[Route("/api/recipes/{id}", name: "detailRecipe", methods: ["GET"])]
    public function detailRecipe(
        EntityManagerInterface $em,
        int $id,
        SerializerInterface $serializer
    ): JsonResponse {
        //récupération de la recipe
        $recipe = $em->getRepository(Recipe::class)->find($id);

        //si on as une recette, alors on la retourne
        if ($recipe) {
            $jsonRecipe = $serializer->serialize($recipe, "json");
            return new JsonResponse($jsonRecipe, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route("api/recipes/{id}", name: "deleteRecipe", methods: ["DELETE"])]
    public function deleteRecipe(
        Recipe $recipe,
        EntityManagerInterface $em
    ): JsonResponse {
        //on passe la référence de la recipe
        $em->remove($recipe);
        //on exécute la requête
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
