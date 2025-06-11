<?php

namespace App\Controller;

use App\Entity\CategoryActivity;
use App\Repository\SubcategoryActivityRepository;
use App\Entity\SubcategoryActivity;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\HttpFoundation\JsonResponse;


final class CategoryController extends AbstractController{
    #[Route('/category', name: 'app_category')]
    public function index(): Response
    {
        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
        ]);
    }

    //Método para que te devuelva las subcategorías, la petición AJAX llamará a este método
    #[Route('/get-subcategories/{id}', name: 'get_subcategories', methods: ['GET'])]
    public function getSubcategories(CategoryActivity $category, SubcategoryActivityRepository $subCatRepository): JsonResponse
    {
        //Primero obtienes toas las subcategorías que estén asociadas con esa categoría
        $subcategories = $subCatRepository->findBy(['categoryActivity'=>$category]);

        //preparas la respuesta json
        $response = [];

        foreach($subcategories as $subcategory){
            $response[] = [
                'id' => $subcategory->getId(),
                'name' => $subcategory->getName(),
            ];
        }

        return new JsonResponse(($response));
    }


}//cierre controlador
