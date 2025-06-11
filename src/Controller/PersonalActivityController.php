<?php

namespace App\Controller;

use App\Entity\CategoryActivity;
use App\Entity\PersonalActivities;
use App\Entity\ImagesActivity;
use App\Entity\SubcategoryActivity;
use App\Entity\User;
use App\Entity\Localities;
use App\Form\PersonalActivityType;
use Symfony\Component\Form\FormInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Repository\PersonalActivitiesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

final class PersonalActivityController extends AbstractController
{
    // -------------------------- GESTIÓN FORMULARIO CREAR Y EDITAR ACTIVIDAD --------------------------
    public function form(PersonalActivities $activities, Request $request, EntityManagerInterface $em): Response
    {
        $editing = $activities->getId() !== null;

        if($editing){
            $originalLocality = $activities->getLocality();
            $originalSubcategory = $activities->getSubcategoryActivity();
        }

        //cargar categorías y subcategorías
        $categories = $em->getRepository(CategoryActivity::class)->findAll();
       
        //Recuperar los objetos catActivity y subcat que vienen de la url
        $category = $activities->getCategoryActivity();
        $subcategory = $activities->getSubcategoryActivity();
        
        //Si hay categoría buscar subcat relacionada, si hay una subcat que no está en la lista
        //porque viene de la url, al crearla, se añade oara que no se pierda en el select!
        $subcategories = [];
        
        if ($category) {
            $subcategories = $em->getRepository(SubcategoryActivity::class)
            ->findBy(['categoryActivity' => $category]);
            
            // Asegurar que la subcategoría seleccionada esté en la lista
            if ($subcategory && !in_array($subcategory, $subcategories, true)) {
                $subcategories[] = $subcategory;
            }
        }


        //Crear el formulario
        $form = $this->createForm(PersonalActivityType::class, $activities, [
            'categories'=> $categories,
            'subcategories'=>$subcategories,
           // 'companion'=>$companion
        ]);

        //Manejar la solicitud
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$editing) {
                $user = $this->getUser();
                $activities->setUser($user);

                //Si no estás editando, marcar la actividad como completada para que en el twig aparezca en la parte de comnpletadas
                $activities->setCompleted(true);
            }

            $companion = $form->get('companion')->getData();
            if ($companion instanceof User) {
                $activities->setCompanion($companion);
            }

            // Eliminar imágenes si el checkbox de eliminar fue marcado
            if ($form->get('delete_image')->getData() === true) {
                foreach ($activities->getImagesActivity() as $existingImage) {
                    $imagePath = $this->getParameter('activity_images_path') . '/' . $existingImage->getImageUrl();
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                    $activities->removeImagesActivity($existingImage);
                    $em->remove($existingImage);
                }
            }

            // Subida de nueva imagen
            $file = $form->get('imagesActivity')->getData();
            if ($file) {
                $newImage = new ImagesActivity();
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move($this->getParameter('activity_images_path'), $fileName);

                $newImage->setImageUrl($fileName);
                $activities->addImagesActivity($newImage);
                $em->persist($newImage);
            }

            if($editing){
                if($activities->getLocality() === null && $originalLocality !== null){
                    $activities->setLocality($originalLocality);
                }

                if($activities->getSubcategoryActivity() === null && $originalSubcategory !==null){
                    $activities->setSubcategoryActivity($originalSubcategory);
                }
            }

            if (!$activities->getSubcategoryActivity() && $subcategory) {
                $activities->setSubcategoryActivity($subcategory);
            }
            
            if (!$activities->getCategoryActivity() && $category) {
                $activities->setCategoryActivity($category);
            }

            $em->persist($activities);
            $em->flush();

            $this->addFlash('success', 'Actividad guardada correctamente');

            return $this->redirectToRoute('app_user_view__profile');
        }

        return $this->render('PersonalActivity/form.html.twig', [
            'form' => $form->createView(),
            'editing' => $editing,
            'activity' => $activities,
        ]);
    }

    // --------------------------- LISTAR ACTIVIDADES DEL USUARIO ---------------------------
    #[Route('/actividades', name: 'app_activity_index', methods: ['GET'])]
    public function index(PersonalActivitiesRepository $activitiesRepository): Response
    {
        $user = $this->getUser();
        $activities = $activitiesRepository->findBy([
            'user' => $user,
        ]);

        return $this->render('user_profile/view_profile.html.twig', [
            'activity' => $activities,
           
        ]);
    }

    // --------------------------- CREAR NUEVA ACTIVIDAD ---------------------------
    #[Route('/new', name: 'app_activity_new', methods: ['GET', 'POST'])]
    public function new_activity(Request $request, EntityManagerInterface $em): Response
    {
        $activities = new PersonalActivities();

        // Añadir valores de categoría y subcategoría de la URL
        $categoryId = $request->query->get('categoryActivity');
        $subcategoryId = $request->query->get('subcategoryActivity');
    
        // Establece la categoría si se proporcionó
        if ($categoryId) {
            $category = $em->getRepository(CategoryActivity::class)->find($categoryId);
            if ($category) {
                $activities->setCategoryActivity($category);
            }
        }

        // Establece la subcategoría si se proporcionó y es válida para esa categoría
        if ($subcategoryId) {
            $subcategory = $em->getRepository(SubcategoryActivity::class)->find($subcategoryId);
            if ($subcategory && $subcategory->getCategoryActivity() === $category) {
                $activities->setSubcategoryActivity($subcategory);
            }
        }

        return $this->form($activities, $request, $em);
    }

    // --------------------------- EDITAR ACTIVIDAD ---------------------------
    #[Route('/edit/activity/{id}', name: 'edit_activity', methods: ['GET', 'POST'])]
    public function edit_activity(PersonalActivities $activities, Request $request, EntityManagerInterface $em): Response
    {
        return $this->form($activities, $request, $em);
    }

    // --------------------------- BORRAR ACTIVIDAD ---------------------------
    #[Route('/delete/activity/{id}', name: 'app_activity_delete', methods: ['POST'])]
    public function delete(Request $request, PersonalActivities $activities, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Validar que el usuario sea el dueño de la actividad
        if ($activities->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        // Verificar CSRF token
        if ($this->isCsrfTokenValid('delete' . $activities->getId(), $request->get('_token'))) {
            // Eliminar imágenes asociadas
            foreach ($activities->getImagesActivity() as $image) {
                $imagePath = $this->getParameter('activity_images_path') . '/' . $image->getImageUrl();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                $em->remove($image);
            }

            // Eliminar la actividad
            $em->remove($activities);
            $em->flush();

            $this->addFlash('success', 'Actividad eliminada correctamente.');
        }

        return $this->redirectToRoute('app_user_view__profile', [], Response::HTTP_SEE_OTHER);
    }

    /*-----------------------------------------------------Ver actividades---------------------------------------------------*/
    #[Route('/activity/view_personal_activity/{id}', name: 'app_view_activities')]
    public function viewActivities(PersonalActivities $personal_activities)
    {
        $categoryIcons = [
            'aventura' => 'fa-tree',
            'bicicross' => 'fa-bicycle',
            'carreras' => 'fa-running',
            'ciclismo' => 'fa-bicycle',
            'deportes_acuaticos' => 'fa-water',
            'deportes_aereos' => 'fa-plane',
            'deportes_urbanos' => 'fa-city',
            'equitacion' => 'fa-horse',
            'escalada' => 'fa-hiking',
            'escapadas_activas' => 'fa-campground',
            'esqui_y_nieve' => 'fa-snowflake',
            'montanismo' => 'fa-mountain',
            'natacion' => 'fa-swimmer',
            'nauticos_y_navegacion' => 'fa-ship',
            'patinaje' => 'fa-skating',
            'puenting' => 'fa-road',
            'senderismo' => 'fa-walking',
            'skate' => 'fa-skating',
            'tiro_y_precision' => 'fa-bullseye',
        ];        


       return $this->render('PersonalActivity/view_personal_activity.html.twig', [
        'personalActivity' => $personal_activities,
        'categoryIcons' => $categoryIcons,
       ]);

    }






} //fin del controlador
