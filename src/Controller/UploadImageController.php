<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Image;
use App\Form\ImageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UploadImageController extends AbstractController
{

    /**
     * @Route("/upload/image/deleteImage", name="deleteImage")
     */
    public function deleteImage()
    {
        $fichier = "\public\image\\0054fc6202330c58a4bb7a1e4c4c7bc4.jpeg";
        unlink($fichier);
        return new Response('ça a marché');
    }

    /**
     * @Route("/upload/image", name="upload_image")
     */
    public function index(Request $request, EntityManagerInterface $manager)
    {

        $image = new Image();

        $annonce = new Annonce();
        $annonce -> setContent('test')
                ->setTitle('test')
                ->setDepositDate(new \DateTime())
                ->setExpirationDate(new \DateTime())
                ;

        $form = $this -> createForm(ImageType::class, $image);
       

        $form -> handleRequest($request);
        dd($request->files);

        // verifie si le formulaire est envoyé si oui, l'enregistre dans la bdd
        if($form -> isSubmitted() && $form -> isValid())
        {
            dd($form['name'] -> getData());
            $targetRepository = "\image\\";
            $file = $form['name']->getData();
            // si une image a été selectionné dans le formulaire ça lance la persistance et le flush sinon ça donne l'image par defaut et ça lance la persistance et le flush
            if(isset($file) && $file != null)
            {
                $fileName = md5(uniqid()).'.'.$file -> guessExtension();
                $file->move($this->getParameter('upload_directory'), $fileName);
                $image -> setName($fileName)
                    -> setPath($targetRepository.$image->getName())
                    -> setAnnonce($annonce);
                $annonce -> addImage($image);
                $manager -> persist($image);
                $manager -> persist($annonce);
                $manager -> flush();

            }
            else
            {
                // fait la persistance et le flush dans le cas ou l'image est nulle
                $image -> setName('default.jpg')
                        -> setPath($targetRepository.$image->getName())
                        ->setAnnonce($annonce);
                $annonce -> addImage($image);
                $manager -> persist($annonce)
                        -> persist($image);
                $manager -> flush();
            }
            return new Response('ok');

        }
        return $this->render('upload_image/index.html.twig', [
           'form' => $form -> createView()
        ]);
    }

 
}
