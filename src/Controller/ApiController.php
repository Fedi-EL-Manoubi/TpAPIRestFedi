<?php

namespace App\Controller;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    /**
     * @Route("/listeRegion", name="listeRegions")
     */
    public function listeRegion(SerializerInterface $serializer)
    {
        $mesRegions=file_get_contents('https://geo.api.gouv.fr/regions');
        $mesRegionsTab = $serializer->decode($mesRegions,'json');
        $mesRegionObjet = $serializer->denormalize($mesRegionsTab,'App\Entity\Region[]');
        $mesRegions = $serializer->deserialize($mesRegions, 'App\Entity\Region[]', 'json');
dump($mesRegionObjet);        return $this->render('api/region.html.twig', [
            'mesRegions'=>$mesRegionObjet
        ]);
    }

     /**
     * @Route("/listeDepsParRegion", name="listeDepsParRegions")
     */
    public function listeDepsParRegion(Request $request, SerializerInterface $serializer)
    {
        $codeRegion = $request->query->get('region');       
        $mesRegions = file_get_contents('https://geo.api.gouv.fr/regions');        
        $mesRegionsArray = $serializer->deserialize($mesRegions, 'App\Entity\Region[]', 'json');      
        $mesRegion = $mesRegionsArray;
        $mesRegion = array_values($mesRegion);
    
        if ($codeRegion == null || $codeRegion == "Toutes") {
            $mesDeps = file_get_contents('https://geo.api.gouv.fr/departements');
        } else {
            $mesDeps = file_get_contents('https://geo.api.gouv.fr/regions/'.$codeRegion.'/departements');
        }
    

        $mesDepsArray = $serializer->decode($mesDeps, 'json');
    
        return $this->render('api/listeDep.html.twig', [
            'mesRegions' => $mesRegion,
            'mesDeps' => $mesDepsArray,
        ]);
    }
    
    /**
    * @Route("/listeComParDep", name="listeComParDeps")
     */
        public function listeComParDep(Request $request, SerializerInterface $serializer, PaginatorInterface $paginator)
    {
        $codeDepartement = $request->query->get('departement');

        $mesDepartements = file_get_contents('https://geo.api.gouv.fr/departements');
        $mesDepartementsArray = $serializer->decode($mesDepartements, 'json');

        if ($codeDepartement == null || $codeDepartement == "Toutes") {
            $mesComs = file_get_contents('https://geo.api.gouv.fr/communes');
        } else {
            $mesComs = file_get_contents('https://geo.api.gouv.fr/departements/'.$codeDepartement.'/communes');
        }

        $mesComsArray = $serializer->decode($mesComs, 'json');

        $pagination = $paginator->paginate(
            $mesComsArray, 
            $request->query->getInt('page', 1), 
            15 // Nb d'éléments par page
        );

        return $this->render('api/listeCom.html.twig', [
            'mesDepartements' => $mesDepartementsArray,
            'mesComs' => $mesComsArray,
            'pagination' => $pagination
        ]);
    }


}