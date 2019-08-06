<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\TaskList;
use App\Repository\TaskListRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class ListController extends AbstractFOSRestController
{
	
	/**
	 * @var TaskListRepository
	 */
	private $taskListRepository;
	private $entityManager;
	private $taskRepository;
	
	public function __construct(TaskListRepository $taskListRepository ,EntityManagerInterface $entityManager , TaskRepository $taskRepository)
	{
		$this->taskListRepository = $taskListRepository;
		$this->entityManager      = $entityManager;
		$this->taskRepository     = $taskRepository;
	}
	
	
	/**
	 * @return View
	 */
	public function getListsAction()
	{
		$data = $this->taskListRepository->findAll();
		return $this->view($data , Response::HTTP_CREATED );
		
	
	}
	
	/**
	 * @param TaskList $list
	 * @return View
	 */
	public function getListAction(TaskList $list)
	{
		return $this->view($list , Response::HTTP_OK);
	}
	
	/**
	 * @Rest\RequestParam(name="title", description="Title of the list", nullable=false)
	 * @param ParamFetcher $paramFetcher
	 * @return View
	 */
	public function postListsAction(ParamFetcher $paramFetcher)
	{
		$title   = $paramFetcher->get('title');
		
		if($title){
			$list   = new TaskList();
			$list   -> setTitle($title);
			
			$this   ->entityManager->persist($list);
			$this   ->entityManager->flush();
			
			return $this->view($list , Response::HTTP_OK);
		}
		
		return $this->view(['title' => 'This cannot be null'] , Response::HTTP_BAD_REQUEST);
	}
	
	/**
	 * @Rest\RequestParam(name="title", description="Title of the new task", nullable=false)
	 * @param ParamFetcher $paramFetcher
	 * @param TaskList $list
	 * @return View
	 */
	public function postListTaskAction(ParamFetcher $paramFetcher, TaskList $list)
	{
		
		if ($list){
			$title = $paramFetcher->get('title');
			
			$task = new Task();
			$task -> setTitle($title);
			$task -> setTaskList($list);
			
			$list ->addTask($task);
			
			$this->entityManager->persist($task);
			$this->entityManager->flush();
			
			return $this->view($task, Response::HTTP_OK);
		}
		
		return $this->view(['message' => 'something went wrong'], Response::HTTP_INTERNAL_SERVER_ERROR);
	}
	
	public function getListTasksAction(int $id)
	{
		$list = $this->taskListRepository->findOneBy(['id' => $id]);
		return $this->view($list->getTasks(), Response::HTTP_OK);
		
	}
	
	/**
	 * @Rest\FileParam(name="image", description="The background of the list", nullable=false, image=true)
	 * @param Request $request
	 * @param ParamFetcher $paramFetcher
	 * @param TaskList $list
	 * @return View
	 */
	public function backgroundListsAction(Request $request, ParamFetcher $paramFetcher, TaskList $list)
	{
		$currentBackground  = $list->getBackground();
		
		if (!is_null($currentBackground)){
			$filesSystem    = new Filesystem();
			$filesSystem    ->remove(
				$this->getUploadsDir().$currentBackground
			);
		}
		
		/** @var UploadedFile $file */
		$file = $paramFetcher->get('image');
		
		if ($file){
			$filename = md5(uniqid()).'.'. $file->guessClientExtension();
			
			$file ->move(
				$this -> getUploadsDir(),
				$filename
			);
			
			$list->setBackground($filename);
			$list->setBackgroundPath('/uploads/'.$filename);
			
			$this->entityManager ->persist($list);
			$this->entityManager ->flush();
			
			$data = $request->getUriForPath(
				$list->getBackgroundPath()
			);
			
			return $this->view($data, Response::HTTP_OK);
		}
		
		return $this->view(['message' => 'something went wrong'], Response::HTTP_BAD_REQUEST);
	}
	
	/**
	 * @param TaskList $list
	 * @return View
	 */
	public function deleteListAction(TaskList $list)
	{
		$this -> entityManager->remove($list);
		$this -> entityManager->flush();
		
		return $this->view(null, Response::HTTP_NO_CONTENT);
	}
	
	/**
	 * @Rest\RequestParam(name="title",description="The new title for the list", nullable=false)
	 * @param ParamFetcher $paramFetcher
	 * @param TaskList $list
	 * @return View
	 */
	public function patchListTitleAction(ParamFetcher $paramFetcher, TaskList $list)
	{
		$errors = [];
		$title  = $paramFetcher->get('title');
		
		if (trim ($title) !== '') {
			if($list){
				$list -> setTitle($title);
				
				$this->entityManager->persist($list);
				$this->entityManager->flush();
				
				return $this->view(null, Response::HTTP_OK);
			}
			$errors[] =[
				'title' =>'This value cannot be empty'
			];
		}
		
		$errors[] =[
			'list' => 'List not found'
		];
		
		return $this->view($errors,Response::HTTP_NO_CONTENT);
		
	}
	
	private function getUploadsDir()
	{
		return $this->getParameter('uploads_dir');
	}
    
}
