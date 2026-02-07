<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class CategoryController extends AbstractController
{
    // LIST (index)
    #[Route('/category', name: 'app_category')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    // ADD (new)
    #[Route('/category/add', name: 'app_category_add')]
    public function add(
        Request $request,
        CategoryRepository $categoryRepository
    ): Response {
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('image')->getData();

            if ($file) {
                $newFilename = uniqid() . '.' . $file->guessExtension();

                $file->move(
                    $this->getParameter('kernel.project_dir') . '/public/uploads',
                    $newFilename
                );

                $category->setImage($newFilename);
            }

            $category->setDateAdd(new \DateTimeImmutable());

            $categoryRepository->save($category, true);

            $this->addFlash('success', 'Catégorie ajoutée avec succès');

            return $this->redirectToRoute('app_category');
        }

        return $this->render('category/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // EDIT

    #[Route('/category/edit/{id}', name: 'app_category_edit')]
    public function edit(
        Request $request,
        Category $category,
        CategoryRepository $categoryRepository
    ): Response {
        $oldImage = $category->getImage();

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('image')->getData();

            if ($file) {
                //  Նոր անուն
                $newFilename = uniqid() . '.' . $file->guessExtension();

                // Upload նոր ֆայլը
                $file->move(
                    $this->getParameter('kernel.project_dir') . '/public/uploads',
                    $newFilename
                );

                // Ջնջում ենք հին նկարը
                if ($oldImage) {
                    $oldImagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $oldImage;

                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                // Պահում ենք նոր նկարը DB-ում
                $category->setImage($newFilename);
            }

            $categoryRepository->save($category, true);

            $this->addFlash('success', 'Catégorie modifiée avec succès');

            return $this->redirectToRoute('app_category');
        }

        return $this->render('category/edit.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
        ]);
    }




    // Delete

    #[Route('/category/{id}/delete', name: 'app_category_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Category $category,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {

            //  Ջնջում ենք նկարը helper-ով
            //$this->deleteImage($category->getImage());

            // Ջնջում ենք category-ն DB-ից
            $em->remove($category);
            $em->flush();

            $this->addFlash('success', 'Catégorie supprimée avec succès');
        }

        return $this->redirectToRoute('app_category');
    }
}
