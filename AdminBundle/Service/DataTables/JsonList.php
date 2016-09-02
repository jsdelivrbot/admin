<?php

namespace AdminBundle\Service\DataTables;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Class JsonList
 *
 * Returns a list in JSON format.
 */
class JsonList
{
    /** @var integer */
    protected $offset;

    /** @var integer */
    protected $limit;

    /** @var integer */
    protected $sortColumn;

    /** @var string */
    protected $sortDirection;

    /** @var string */
    protected $search;

    /** @var integer */
    protected $echo;

    /** @var ObjectRepository */
    protected $repository;
    
    /** @var Category entity */
    protected $category=null;
    
    /** @var EntityId entity */
    protected $entityId=null;
    
    /** @var EntityId entity */
    protected $agreementId=null;
    
    /** @var EntityId entity */
    protected $advertId=null;
    
    /** @var string  */
    protected $locale;
    
    /**
     * Constructor
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->offset = intval($request->get('iDisplayStart'));
        $this->limit = intval($request->get('iDisplayLength'));
        $this->sortColumn = intval($request->get('iSortCol_0'));
        $this->sortDirection = $request->get('sSortDir_0');
        $this->search = $request->get('sSearch');
        $this->echo = intval($request->get('sEcho'));

        return $this;
    }

    /**
     * Set the repository
     *
     * @param ObjectRepository $repository
     */
    public function setRepository(ObjectRepository $repository)
    {
        $this->repository = $repository;
    }
    
    /**
     * Set the category entity 
     *
     * @param Category $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }
    
     /**
     * Set the entityId entity 
     *
     * @param Category $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }
    
     /**
     * Set the advertId entity 
     *
     * @param Agreement $advertId
     */
    public function setAgreementId($advertId)
    {
        $this->advertId = $advertId;
    }
    
     /**
     * Set the advertId entity 
     *
     * @param Agreement $advertId
     */
    public function setAdvertId($advertId)
    {
        $this->advertId = $advertId;
    }
    
    /**
     * Set the locale
     *
     * @param ObjectRepository $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }
    
    /**
     * Get the list
     *
     * @return array
     */
    public function get()
    {
        $totalEntities = $this->repository->countTotal();

       
        if(!is_null($this->category)){
            $entities = $this->repository->findAllForDataTablesByCategory($this->search, $this->sortColumn, $this->sortDirection, $this->category);
        }elseif(!is_null($this->entityId)){
            $entities = $this->repository->findAllForDataTables($this->search, $this->sortColumn, $this->sortDirection, $this->entityId, $this->locale);
        }elseif(!is_null($this->agreementId)){
            $entities = $this->repository->findByAgreementForDataTables($this->search, $this->sortColumn, $this->sortDirection, $this->agreementId);
        }elseif(!is_null($this->advertId)){
            $entities = $this->repository->findByAdvertForDataTables($this->search, $this->sortColumn, $this->sortDirection, $this->advertId);
        }else{
            $entities = $this->repository->findAllForDataTables($this->search, $this->sortColumn, $this->sortDirection, null, $this->locale);
        }
         
        $totalFilteredEntities = count($entities->getScalarResult());

        // paginate
        $entities->setFirstResult($this->offset)
            ->setMaxResults($this->limit);

        $data = $entities->getResult();


        return array(
            'iTotalRecords'         => $totalEntities,
            'iTotalDisplayRecords'  => $totalFilteredEntities,
            'sEcho'                 => $this->echo,
            'aaData'                => $data
        );
    }
    
}