<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/product/add', name: 'app_add_product')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $product = new Product();

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            

            $rawPrice = $form->get('price')->getData(); //  ԱՅՍՏԵՂ Է ՃԻՇՏԸ

            $normalizedPrice = preg_replace('/[^\d,\.]/', '', $rawPrice);
            $normalizedPrice = str_replace(',', '.', $normalizedPrice);

            $product->setPrice($normalizedPrice);


            $em->persist($product);
            $em->flush();

            // dump($rawPrice);
            //dump($normalizedPrice);
            //die;

            //if ($form->isSubmitted() && $form->isValid()) {


            $this->addFlash('success', 'Produit ajouté avec succès');

            return $this->redirectToRoute('app_product');
        }

        return $this->render('product/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
