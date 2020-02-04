<?php

namespace App\Controller;

use DOMDocument;
use App\Entity\Annonce;
use App\Entity\CompteurAccueil;
use App\Entity\Image;

use App\Entity\Rubrique;
use App\Form\AnnonceType;
use App\Form\RubriqueType;
use Doctrine\ORM\Mapping\OrderBy;
use App\Repository\AnnonceRepository;
use App\Repository\CompteurAccueilRepository;
use App\Repository\ImageRepository;

use App\Repository\RubriqueRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DeathSprintController extends AbstractController
{
    /**
     * @Route("/deathsprint", name="death_sprint")
     */
    public function index()
    {
        return $this->render('death_sprint/index.html.twig', [
            'controller_name' => 'DeathSprintController',
        ]);
    }

    /**
     * @Route("/", name="home")
     */
    public function home(CompteurAccueilRepository $compteurAcceuil, EntityManagerInterface $manager, Request $request)
    {
        // Compteur de la page Accueil
        //  Initialisation de la date du jour
        $today = new DateTime();
        $today->setTime('00', '00', '00');

        //Si la date de visite n'est pas inscrite dans la BDD alors
        if ($compteurAcceuil->findBy(['date_visit' => $today]) == false) {
            $compteurAcceuil = new CompteurAccueil();
            $compteurAcceuil->setDateVisit($today);
            $compteurAcceuil->setVisit(1);
            $manager->persist($compteurAcceuil);
            $manager->flush();
        } else {
            //Recuperation de l'objet annonce de la bdd
            $objectAnonceToday = $compteurAcceuil->findOneBy(['date_visit' => $today]);
            $visit = $objectAnonceToday->getVisit() + 1;
            $objectAnonceToday->setVisit($visit);
            $manager->persist($objectAnonceToday);
            $manager->flush();
        }
        return $this->render('death_sprint/home.html.twig');
    }

    // ----------------------------------------------- RUBRIQUES ------------------------------------------------------------

    /**
     * @Route("/deathsprint/rubrique", name="show_rubrique")
     */
    public function showRubrique(RubriqueRepository $repository)
    {
        $rubriques = $repository->findAll();

        return $this->render('death_sprint/show_rubrique.html.twig', [
            'rubriques' => $rubriques,
        ]);
    }

    /**
     * @Route("/deathsprint/rubrique/{id}", name="annonce_rubrique")
     *
     * @param mixed $id
     */
    public function annonceByRubrique(RubriqueRepository $rubriqueRepository, AnnonceRepository $annonceRepository, $id)
    {

        $rubrique = $rubriqueRepository->find($id);

        $annonce = $annonceRepository
        //fonction crée dans AnnonceRepository
        ->findAllByRub($id);

        return $this->render('death_sprint/annoncesByRubrique.html.twig', [
            'rubrique' => $rubrique,
            'annonces' => $annonce,
        ]);
    }

    /**
     * @Route("/deathsprint/create_rubrique", name="create_rubrique")
     * @Route("/deathsprint/edit/{id}", name="modification_rubrique")
     */
    public function addRubrique(Rubrique $rubrique = null, Request $request, EntityManagerInterface $manager)
    {
        if (!$rubrique) {
            $rubrique = new Rubrique();
        }
        // création du formulaire et ajout du libelle
        $form = $this->createForm(RubriqueType::class, $rubrique);

        // condition de validation du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($rubrique);

            $manager->flush();

            //redirection vers une visualisation de l'annonce crée
            return $this->redirectToRoute('show_rubrique', [
                'id' => $rubrique->getId()]);
        }

        //vue affiché quand le formulaire n'est pas soumis
        return $this->render('death_sprint/form_rubrique.html.twig', [
            'form' => $form->createView(),
            'editMode' => null !== $rubrique->getId(),
        ]);
    }

    /**
     *@Route("/deathsprint/delete/{id}", name="delete_rubrique")
     */
    public function delete(Rubrique $rubrique)
    {
        $del = $this->getDoctrine()->getManager();

        $del->remove($rubrique);

        $del->flush();

        return $this->redirectToRoute('show_rubrique');
    }

    // ----------------------------------------------- ANNONCES ------------------------------------------------------------

    /**
     * @Route("/create", name="create")
     * @Route ("/annonces/{id}/edit", name="annonce_edit")
     *
     */

    public function annoncesForm(Annonce $annonces = null, Request $request, EntityManagerInterface $manager, RubriqueRepository $rubriqueRepository, ImageRepository $imageRepository, $id = null)
    {
        // Si non connecter redirection
        if (empty($this->getUser())) {

            return $this->redirectToRoute('security_login');
        }
        // Si création d'annonce
        if (!$annonces) {
            $annonces = new Annonce();

        }
        //Form annonce
        $form = $this->createForm(AnnonceType::class, $annonces);
        $form->handleRequest($request);
        $rub = $rubriqueRepository->findAll();

        //Si form est valid
        $imageSelected = $imageRepository->findAll();
        
        if ($form->isSubmitted() && $form->isValid()) {
            //recupere la rubrique selectionner
            $rubrique = $rubriqueRepository->find($request->get('rubrique_id'));
            $annonces->setDepositDate(new \DateTime());
            $annonces->setUser($this->getUser());
            $annonces->setRubrique($rubrique);
            // verifie si le formulaire est envoyé si oui, l'enregistre dans la bdd
            if ($form->isSubmitted() && $form->isValid()) {
                $file = $form['name']->getData();
                // si une image a été selectionné dans le formulaire ça lance la persistance et le flush sinon ça donne l'image par defaut et ça lance la persistance et le flush
                if (isset($file) && $file != null) {
                    foreach ($file as $element) {
                        // creation d'une noubelle image recupération du file envoyer en post deplacement dans le dossier image apres creation d'un nomUnique
                        $image = new Image();
                        $targetRepository = "image\\";
                        $fileName = md5(uniqid()) . '.' . $element->guessExtension();
                        $element->move($this->getParameter('upload_directory'), $fileName);
                        $image->setName($fileName)
                            ->setPath($targetRepository . $image->getName())
                            ->setAnnonce($annonces);
                        $annonces->addImage($image);
                        $manager->persist($image);
                    }
                    $manager->persist($annonces);
                    $manager->flush();
                } else {
                    $image = new Image();
                    $targetRepository = "image\\";
                    // fait la persistance et le flush dans le cas ou l'image est nulle
                    $image -> setName('default.jpg')
                            -> setPath($targetRepository.$image->getName())
                            ->setAnnonce($annonces);
                    $annonces -> addImage($image);
                    dump($annonces);
                    $manager -> persist($image);
                    $manager-> persist($annonces);
                    $manager -> flush();
                }
            }
            return $this->redirectToRoute('annonces_show', ['id' => $annonces->getId()]); //lors de la validation redirige vers la page de l'annonce une fois le form valider
        }
        return $this->render('annonce/create.html.twig', [
            'annonce_form' => $form->createView(),
            'editMode' => null !== $annonces->getId(), // Si update de l'annonce recupere est envoi les donnée de l'annonce
            'rubriques' => $rub,
            'annonce_update' => $annonces,
            'image_edit' => null !== $imageSelected,
            'images' => $imageSelected
        ]);
    }

    /**
     * @Route("/annonces", name="annonces")
     */
    public function listAnnonce(AnnonceRepository $annonces, ImageRepository $image)
    {
        $annonce = $annonces->findAll();
        $image = $image->findAll();
        return $this->render('annonce/index.html.twig', [
            'annonces' => $annonce, 'image' => $image,
        ]);
    }

    /**
     * @Route("/annonces/{id}", name="annonces_show")
     *
     * @param mixed $id
     */
    public function show(Annonce $annonce, ImageRepository $image, EntityManagerInterface $manager)
    {
        //Incrementation du compteur de visite ( non proteger par utilisateur unique ) //TODO REFACTORING
        $visite = $annonce->getVisit();
        $incrementVisit = $visite + 1;
        $manager->persist($annonce->setVisit($incrementVisit));
        $manager->flush($annonce);

        $image = $image->findBy(['annonce' => $annonce->getId()], ['id' => 'ASC']);
        return $this->render('annonce/show.html.twig', [
            'annonce' => $annonce, 'image' => $image,
        ]);

    }

    /**
     * @Route("/annonces/{id}/delete", name="annonce_delete")
     *
     * @param mixed $id
     */
    public function deleteAnnonce(Annonce $annonce, EntityManagerInterface $manager, $id)
    {
        $images = $this->getDoctrine()->getRepository(Image::class);
        $image = $images->findBy(['annonce' => $id], ['id' => 'ASC']);
        $manager->remove($annonce);
        $manager->flush();
        if (isset($image)) {
            foreach ($image as $element) {
                if ($element->getName() != 'default.jpg') {
                    unlink($element->getPath());
                }
            }
        }
        return $this->render('death_sprint/home.html.twig'); //TODO modifier le chemin lors du delete pour le remettre dans "mon compte"
    }

 // ----------------------------------------------- UTILISATEURS ------------------------------------------------------------

    /**
     * @Route("/compte" ,name="mon_compte")
     * @Route("/deathsprint/edit/{id}", name="modification_rubrique")
     * @Route ("/annonces/{id}/edit", name="annonce_edit")
     * @Route("/deathsprint/rubrique/{id}", name="annonce_rubrique")
     * @Route("/annonces/{id}/delete", name="annonce_delete")
     */
    public function monCompte(UserRepository $repository, AnnonceRepository $annonceRepository, CompteurAccueilRepository $compteurAcceuil,
        RubriqueRepository $rubriqueRepository, Rubrique $rubrique = null, Request $request, EntityManagerInterface $manager,$id = null)
    {

        // Récupération de l'utilisateur courant
        $id = $this->getUser();
        $annonce = $annonceRepository
        //fonction crée dans AnnonceRepository
        ->findAllById($id);
        // Compteur visite de la page Accueil
        //total
        $cnx = $manager->getConnection();
        $sql = 'SELECT SUM(visit) FROM compteur_accueil';
        $stmt = $cnx->prepare($sql);
        $stmt->execute();
        $visitTotal = $stmt->fetchColumn();
        //aujourdhui
        //conversion de la date en string pour la comparaison dans la bdd
        $today = new DateTime();
        $today->setTime('00', '00', '00');
        $date = date_format($today, 'Y-m-d H:i:s');
        $cnx = $manager->getConnection();
        $sql = "SELECT visit FROM compteur_accueil WHERE date_visit =  '{$date}'";
        $stmt = $cnx->prepare($sql);
        $stmt->execute();
        $visitToday = $stmt->fetchColumn();
        //User total
        $cnx = $manager->getConnection();
        $sql = 'SELECT COUNT(*) FROM user';
        $stmt = $cnx->prepare($sql);
        $stmt->execute();
        $userTotal = $stmt->fetchColumn();
        //Nombre d'annonce en ligne
        $cnx = $manager->getConnection();
        $sql = 'SELECT COUNT(*) FROM annonce';
        $stmt = $cnx->prepare($sql);
        $stmt->execute();
        $annonceTotal = $stmt->fetchColumn();
        //nombre annonce périmé
        $cnx = $manager->getConnection();
        $sql = 'SELECT COUNT(*) FROM annonce WHERE expiration_date<NOW()';
        $stmt = $cnx->prepare($sql);
        $stmt->execute();
        $annonceExp = $stmt->fetchColumn();

        // création du formulaire et ajout du libelle
        $form = $this->createForm(RubriqueType::class, $rubrique);

        // condition de validation du formulaire
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($rubrique);
 
            $manager->flush();
        }

        $anns = $annonceRepository->findAllByRub($id);

        $rubriques = $rubriqueRepository->findAll();

        // Récupération de l'utilisateur courant
        $id_ann = $this->getUser();

        $annonce = $annonceRepository
                    //fonction crée dans AnnonceRepository
                    ->findAllById($id_ann);

        return $this->render('death_sprint/mon_compte.html.twig', [
            'annonces' => $annonce,
            'visiteSite' => $visitTotal,
            'userTotal' => $userTotal,
            'annonceTotal' => $annonceTotal,
            'visiteToday' => $visitToday,
            'annonceExpirer' => $annonceExp,
            'rubriques'=>$rubriques,
            'form'=>$form->createView(),
            'rubrique'=>$rubrique,
            'anns'=>$anns

        ]);
    }

    public function search()
    {
        $xmlDoc = new DOMDocument();
        $xmlDoc->load("links.xml");

        $x = $xmlDoc->getElementsByTagName('link');

        //get the q parameter from URL
        $q = $_GET["q"];

        //lookup all links from the xml file if length of q>0
        if (strlen($q) > 0) {
            $hint = "";
            for ($i = 0; $i < ($x->length); $i++) {
                $y = $x->item($i)->getElementsByTagName('title');
                $z = $x->item($i)->getElementsByTagName('url');
                if ($y->item(0)->nodeType == 1) {
                    //find a link matching the search text
                    if (stristr($y->item(0)->childNodes->item(0)->nodeValue, $q)) {
                        if ($hint == "") {
                            $hint = "<a href='" .
                                $z->item(0)->childNodes->item(0)->nodeValue .
                                "' target='_blank'>" .
                                $y->item(0)->childNodes->item(0)->nodeValue . "</a>";
                        } else {
                            $hint = $hint . "<br /><a href='" .
                                $z->item(0)->childNodes->item(0)->nodeValue .
                                "' target='_blank'>" .
                                $y->item(0)->childNodes->item(0)->nodeValue . "</a>";
                        }
                    }
                }
            }
        }

        // Set output to "no suggestion" if no hint was found
        // or to the correct values
        if ($hint == "") {
            $response = "no suggestion";
        } else {
            $response = $hint;
        }

        //output the response
        echo $response;
    }
    /**
     * @Route("/deletePerime" ,name="deletePerime")
     */
    public function deletePerime(EntityManagerInterface $manager, Request $request)
    {
        $cnx = $manager->getConnection();
        $sql = 'DELETE FROM annonce WHERE expiration_date<NOW()';
        $stmt = $cnx->prepare($sql);
        $stmt->execute();

        return $this->redirectToRoute("mon_compte");
    }

    // public function monCompte( RubriqueRepository $rubriqueRepository, AnnonceRepository $annonceRepository,Rubrique $rubrique = null, Request $request, EntityManagerInterface $manager,$id = null){

    //     // création du formulaire et ajout du libelle
    //     $form = $this->createForm(RubriqueType::class, $rubrique);

    //     // condition de validation du formulaire
    //     $form->handleRequest($request);

    //     if($form->isSubmitted() && $form->isValid()) {
    //         $manager->persist($rubrique);
 
    //         $manager->flush();
    //     }

    //     $anns = $annonceRepository->findAllByRub($id);

    //     $rubriques = $rubriqueRepository->findAll();

    //     // Récupération de l'utilisateur courant
    //     $id_ann = $this->getUser();

    //     $annonce = $annonceRepository
    //                 //fonction crée dans AnnonceRepository
    //                 ->findAllById($id_ann);
                    
    //     return $this->render('death_sprint/mon_compte.html.twig',[
    //         'annonces'=>$annonce,
    //         'rubriques'=>$rubriques,
    //         'form'=>$form->createView(),
    //         'rubrique'=>$rubrique,
    //         'anns'=>$anns
    //     ]);
    // }
}
