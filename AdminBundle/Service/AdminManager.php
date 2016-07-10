<?php

namespace AdminBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use CoreBundle\Entity\Actor;
use EcommerceBundle\Entity\Product;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class AdminManager
 */
class AdminManager
{
    private $entityManager;
    
    private $securityContext;
    
    private $parameters;

    private $container;


    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager, $securityContext, array $parameters, $container)
    {
        $this->entityManager = $entityManager;
        $this->securityContext = $securityContext;
        $this->parameters = $parameters['parameters'];
        $this->container = $container;
    }

    /**
     * Sort entities from the given IDs
     *
     * @param string $entityName
     * @param string $values
     */
    public function sort($entityName, $values)
    {
        $values = json_decode($values);

        for ($i=0; $i<count($values); $i++) {
            $this->entityManager
                ->getRepository($entityName)
                ->createQueryBuilder('e')
                ->update()
                ->set('e.order', $i)
                ->where('e.id = :id')
                ->setParameter('id', $values[$i]->id)
                ->getQuery()
                ->execute();
        }
    }

    public function getOpticStats(Optic $optic, $start, $end) 
    {
        $stats = $this->entityManager->getRepository('CoreBundle:Optic')
                ->getOpticStats($optic, $start, $end);
        
        return $stats;
    }
    
    public function getProductStats(Product $product, $start, $end) 
    {
        $stats = $this->entityManager->getRepository('EcommerceBundle:Product')
                ->getProductStats($product, $start, $end);
        
        return $stats;
    }
     
    
    /**
     * Sets an entity as filtrable
     *
     * @param string $entityName
     * @param int    $id
     *
     * @throws NotFoundHttpException
     * @return boolean
     */
    public function toggleFiltrable($entityName, $id)
    {
        $entity = $this->entityManager->getRepository($entityName)->find($id);

        if (!$entity) {
            throw new NotFoundHttpException();
        }

        $entity->toggleFiltrable();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity->isFiltrable();
    }
    
    public function uploadWebImage($image, $entity)
    {
        $extension = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
        $imageName = sha1(uniqid(mt_rand(), true)) . '.' . $extension;

        if ($image->move($this->getAbsolutePathWeb($entity->getId()), $imageName)) {
            return $imageName;
        }
        else {
            return null;
        }
    }
    
    public function uploadProfileImage($image, $entity)
    {
        $extension = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
        $name = sha1(uniqid(mt_rand(), true));
        $imageName = $name . '.' . $extension;

        if ($image->move($this->getAbsolutePathProfile($entity->getId()), $imageName)) {
            $absPathImage = $this->getAbsolutePathProfile($entity->getId()).$imageName;
            
            $thumPath = $this->getWebPath().$this->parameters['upload_directory'].DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'profile'.DIRECTORY_SEPARATOR.$entity->getId().DIRECTORY_SEPARATOR.'thumbnail';
            if(!is_dir($thumPath)) {
                $fs = new Filesystem();
                $fs->mkdir($thumPath, 0777);
                $fs->chown($thumPath, 'www-data', true);
                $fs->chgrp($thumPath, 'www-data', true);
                $fs->chmod($thumPath, 0777, 0000, true);
            }
            
            $this->resizeImage($absPathImage, $name.'_260', 260, 123, $this->getAbsolutePathProfile($entity->getId()));
            $this->resizeImage($absPathImage, $name.'_142', 142, 88, $this->getAbsolutePathProfile($entity->getId()));
            return $imageName;
        }
        else {
            return null;
        }
    }
    
    public function getAbsolutePathProfile($id) {
        return $this->getWebPath() .  $this->parameters['upload_directory'] . DIRECTORY_SEPARATOR . 'images'. DIRECTORY_SEPARATOR . 'profile'.  DIRECTORY_SEPARATOR . $id .DIRECTORY_SEPARATOR;
    }
    
    public function getAbsolutePathWeb($id) {
        return $this->getWebPath() .  $this->parameters['upload_directory'] . DIRECTORY_SEPARATOR . 'images'. DIRECTORY_SEPARATOR . 'web'.  DIRECTORY_SEPARATOR . $id .DIRECTORY_SEPARATOR;
    }
    
    public function getAbsolutePathPost($id) {
        return $this->getWebPath() .  $this->parameters['upload_directory'] . DIRECTORY_SEPARATOR . 'images'. DIRECTORY_SEPARATOR . 'post'.  DIRECTORY_SEPARATOR . $id .DIRECTORY_SEPARATOR;
    }

    public function getWebPath() {
        return __DIR__ . '/../../../../../web/';
    }
    
     /**
    * Returns the image path of user actor
    *
    */
    public function getProfileImage($actor=null)
    {

        if (is_null($actor)) {
                $actor = $this->container->get('security.token_storage')->getToken()->getUser();
        }

        if ($actor instanceof Actor && $actor->getImage() instanceof Image) {
            $profileImage = '/uploads/images/profile/'.$actor->getId().'/'.$actor->getImage()->getPath();
        } else {
            $profileImage = $this->getDefaultImageProfile();
        }
 
        return  $profileImage;
    }
    
    public function getDefaultImageProfile()
    {
        return '/bundles/admin/img/default_profile.png';
    }
    
    public function resizeImage($pathSource, $name, $newImageWidth, $newImageHeight, $dstPath=null) 
    {
        $fileName = explode('/', $pathSource);
        $extension = pathinfo(end($fileName), PATHINFO_EXTENSION);

        if($extension=='jpg') {
            $source = @imagecreatefromjpeg($pathSource);
        }elseif($extension=='gif') {
            $source = @imagecreatefromgif($pathSource);
        }elseif($extension=='png') {
            $source = @imagecreatefrompng($pathSource);
        }

        //imagen vertical u horizontal
        $width  = imagesx($source);
        $height = imagesy($source);    
        
        if($newImageWidth==null){
          $ratio = $newImageHeight / $height;
          $newImageWidth = round($width * $ratio);
        }

        if($newImageHeight==null){
          $ratio = $newImageWidth / $width;
          $newImageHeight = round($height * $ratio);
        }

        $source_ratio=$width/$height;
        $new_ratio=$newImageWidth/$newImageHeight;

        //imagen horizontal ajustar al alto   
        if($new_ratio<$source_ratio){
          $ratio = $newImageHeight / $height;
          $width_aux = round($width * $ratio);
          $height_aux = $newImageHeight;
        }else{//imagen vertical ajustar al ancho
          $ratio = $newImageWidth / $width;
          $height_aux = round($height * $ratio);
          $width_aux = $newImageWidth;
        }  
        
        $newImage = imagecreatetruecolor($width_aux,$height_aux);       
        imagecopyresampled($newImage,$source,0,0,0,0,$width_aux,$height_aux,$width,$height);
        //imagedestroy($source);

        //recortar al centro
        if($width_aux==$newImageWidth && $height_aux==$newImageHeight){
          $newImage2=$newImage;
        }else{
          
          $centreX = ceil($width_aux / 2);
          $centreY = ceil($height_aux / 2);

          $cropWidth  = $newImageWidth;
          $cropHeight = $newImageHeight;
          $cropWidthHalf  = ceil($cropWidth / 2); // could hard-code this but I'm keeping it flexible
          $cropHeightHalf = ceil($cropHeight / 2);

          $x1 = max(0, $centreX - $cropWidthHalf);
          $y1 = max(0, $centreY - $cropHeightHalf);

          $x2 = min($width, $centreX + $cropWidthHalf);
          $y2 = min($height, $centreY + $cropHeightHalf);

          $newImage2 = imagecreatetruecolor($cropWidth,$cropHeight);
          //echo 'recorta '.$cropWidth.' '.$cropHeight.' '.$x1.' '.$y1.' '.$x2.' '.$y2.' '.$newImageWidth.' '.$newImageHeight;
          imagecopy($newImage2, $newImage, 0, 0, $x1, $y1, $newImageWidth, $newImageHeight); 

        }
        //save image
        if(!is_null($dstPath)) {
            if($extension=='jpg') {
                imagejpeg($newImage2, $dstPath.'thumbnail/'.$name.'.'.$extension,90);
            }elseif($extension=='gif') {
                imagegif($newImage2, $dstPath.'thumbnail/'.$name.'.'.$extension,90);
            }elseif($extension=='png') {
                $quality = round(abs((90 - 100) / 11.111111));
                imagepng($newImage2, $dstPath.'thumbnail/'.$name.'.'.$extension,$quality);
            }
        }else {
            if($extension=='jpg') {
                imagejpeg($newImage2, $this->options['upload_dir'].'thumbnail/'.$name.'.'.$extension,90);
            }elseif($extension=='gif') {
                imagegif($newImage2, $this->options['upload_dir'].'thumbnail/'.$name.'.'.$extension,90);
            }elseif($extension=='png') {
                imagepng($newImage2, $this->options['upload_dir'].'thumbnail/'.$name.'.'.$extension,90);
            } 
        }
        return $newImage2;
       
    }
    
    public function checkRemoteFile($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        // don't download content
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if(curl_exec($ch)!==FALSE)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
}