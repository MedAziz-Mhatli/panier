<?php

namespace App\Controller;

use Twig\Environment;
use App\Entity\Produit;
use App\Form\ProduitFormType;
use App\Form\UpdateProduitType;
use App\Repository\ProduitRepository;
use App\Repository\WishlistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProduitController extends AbstractController
{
    #[Route('/afficherProduit', name: 'app_afficherProduit')]
    public function afficherProduit(ProduitRepository $repo)
    {
        return $this->render('produit/afficherProduit.html.twig', [
            'produit' => $repo->findAll(),
        ]);
    }

    #[Route('/deleteProduit/{id}', name: 'app_deleteProduit')]
    public function deleteProduit(ManagerRegistry $doctrine,ProduitRepository $repo,$id)
    {
        $manager = $doctrine->getManager();
        $cat=$repo->find($id);
        $manager->remove($cat);
        $manager->flush();
        return $this->redirectToRoute('app_afficherProduit');
    }

    #[Route('/ajouterProduit', name: 'app_ajouterProduit')]
    public function ajouterProduit(Environment $twig,Request $request,EntityManagerInterface $manager)
    {
        $produit=new Produit();

        $form=$this->createForm(ProduitFormType::class, $produit);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $request->files->get('produit_form')['image'];
            $filename = md5(uniqid()) . '.png';
            $file->move($this->getParameter('produit_directory'), $filename);
            $produit->setImage($filename);

            $produit->setCreationDate(new \DateTime("now"));
            $produit->setModificationDate(null);
            $produit->setIsEnabled(1);
            $manager->persist($produit);
            $manager->flush();
            return $this->redirectToRoute('app_afficherProduit');
           }

        return new Response($twig->render('produit/ajouterProduit.html.twig',[
            'formProduit' => $form->createView()
        ]));
    }

    #[Route('/updateProduit/{id}', name: 'app_updateProduit')]
    public function updateProduit($id,Produit $produit,Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UpdateProduitType::class, $produit);
        $form->handleRequest($request);
        $entityManager->getRepository(Produit::class)->find($id);

        if ($form->isSubmitted() && $form->isValid()) {
            $produit->setModificationDate(new \DateTime("now"));

            $file = $request->files->get('update_produit')['image'];
            $filename = md5(uniqid()) . '.png';
            $file->move($this->getParameter('produit_directory'), $filename);
            $produit->setImage($filename);

            $entityManager->persist($produit);
            $entityManager->flush();
            return $this->redirectToRoute('app_afficherProduit');
        }

        return $this->render('produit/modifierProduit.html.twig', [
            'formProd' => $form->createView(),
        ]);
    }

    //front
    #[Route('/afficherProduitFront/{cat}', name: 'app_afficherProduitFront')]
    public function afficherProduitFront(ProduitRepository $repo,$cat)
    {
        return $this->render('produit/afficherProduitFront.html.twig', [
            'produit' => $repo->findByCategorie($cat),
        ]);
    }


    


}
