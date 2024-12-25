<?php

namespace App\Controller;

use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Entity\Recipe;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Builder\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\Json;

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

    #[Route("api/recipes", name:"createRecipe", methods:['POST'])]
    public function createRecipe(SerializerInterface $serializer, Request $request, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
      //récupération des données depuis la requête
      $recipe = $serializer->deserialize($request->getContent(), Recipe::class, 'json');
      //ajoute de la date de création
      $recipe->setCreatedAt(new \DateTimeImmutable());
      //persistance en BDD
      $em->persist($recipe);
      $em->flush();

      //serialization pour retourner en réponse la ressource créer
      $jsonRecipe = $serializer->serialize($recipe, 'json');

      //génération de la route qui peut être utilisé pour récupérer des informations sur la recette
      $location = $urlGenerator->generate('detailRecipe', ['id' => $recipe->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

      //on retourne la nouvelle recipe, et on ajoute dans le headers le champs location qui contient l'url de la nouvelle recette
      return new JsonResponse($jsonRecipe, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route("api/recipe/{id}", name: "editRecipe", methods:['PUT'])]
    public function editRecipe(Request $request, SerializerInterface $serializer, Recipe $recipe, EntityManagerInterface $em): JsonResponse
    {
      $updatedRecipe = $serializer->deserialize($request->getContent(), Recipe::class, 'json', [AbstractController::OBJECT_TO_POPULATE => $recipe]);
      $updatedRecipe->setUpdatedAt(new \DateTimeImmutable());
      $content = $request->toArray();


      $em->persist($updatedRecipe);
      $em->flush();
      return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
