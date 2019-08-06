<?php

namespace App\Controller;

use App\Entity\TaskList;
use App\Repository\TaskListRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Response;


class ListController extends AbstractFOSRestController
{
	
	/**
	 * @var TaskListRepository
	 */
	private $taskListRepository;
	private $entityManager;
	
	public function __construct(TaskListRepository $taskListRepository ,EntityManagerInterface $entityManager)
	{
		$this->taskListRepository = $taskListRepository;
		$this->entityManager      = $entityManager;
	}
	
	
	public function getListsAction()
	{
		$data = $this->taskListRepository->findAll();
		return $this->view($data , Response::HTTP_CREATED );
		
	
	}
	
	public function getListAction(int $id)
	{
		$data   = $this ->taskListRepository->findOneBy(['id' => $id]);
		
		return $this->view($data , Response::HTTP_OK);
	}
	
	/**
	 * @Rest\RequestParam(name="title", description="Title of the list", nullable=false)
	 * @param ParamFetcher $paramFetcher
	 */
	public function postListsAction(ParamFetcher $paramFetcher)
	{
		$title   = $paramFetcher->get('title');
		//$background = $paramFetcher->get('background');
		//$backgrooundPath = $paramFetcher->get('backgroundPath');
		
		if($title){
			$list   = new TaskList();
			$list   -> setTitle($title);
			
			$this   ->entityManager->persist($list);
			$this   ->entityManager->flush();
			
			return $this->view($list , Response::HTTP_OK);
		}
		
		return $this->view(['title' => 'This cannot be null'] , Response::HTTP_BAD_REQUEST);
	}
	
	public function getListTasksAction(int $id)
	{
	
	}
	
	public function putListsAction()
	{
	
	}
	
	public function toggleListsAction(int $id)
	{
	
	}
    
}
